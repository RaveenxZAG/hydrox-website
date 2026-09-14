<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class InternalMaintenanceController extends Controller
{
    /**
     * Display status and confirmation form for SQLite migration.
     * Never mutates any state.
     */
    public function statusForm(Request $request): Response|JsonResponse
    {
        // Feature flag guard
        if (! (bool) config('app.sqlite_migration_enabled', env('SQLITE_MIGRATION_ENABLED', false))) {
            abort(404);
        }

        $activeDriver = config('database.default');
        $sqlitePath = config('database.connections.sqlite.database') ?: env('SQLITE_DB_DATABASE');
        $activeSqliteExists = !empty($sqlitePath) && File::exists($sqlitePath);
        $activeSqliteSize = $activeSqliteExists ? File::size($sqlitePath) : 0;

        $targetDir = !empty($sqlitePath) ? dirname($sqlitePath) : '';
        $isLocked = !empty($targetDir) && File::exists($targetDir . '/.sqlite_migration.lock');
        $isDown = app()->isDownForMaintenance();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'ready',
                'active_driver' => $activeDriver,
                'target_sqlite_path' => $sqlitePath,
                'target_sqlite_exists' => $activeSqliteExists,
                'target_sqlite_bytes' => $activeSqliteSize,
                'is_locked' => $isLocked,
                'is_down_for_maintenance' => $isDown,
                'message' => 'GET is read-only. Submit a POST request with maintenance token to execute migration.',
            ]);
        }

        $csrfToken = csrf_token();
        $downBanner = $isDown
            ? '<div style="background:#fee2e2;border:1px solid #ef4444;color:#991b1b;padding:12px;border-radius:6px;margin-bottom:16px;">
                <strong>Notice:</strong> The application is currently in maintenance mode (503).
                <form method="POST" action="/internal/maintenance/up" style="margin-top:8px;">
                    <input type="hidden" name="_token" value="' . $csrfToken . '">
                    <input type="password" name="token" required placeholder="Enter token to restore site" style="padding:6px;font-size:13px;border-radius:4px;border:1px solid #cbd5e1;margin-right:8px;">
                    <button type="submit" style="background:#dc2626;color:#fff;border:none;padding:6px 12px;border-radius:4px;cursor:pointer;">Bring Site Back Up</button>
                </form>
               </div>'
            : '';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hydrox SQLite Migration Maintenance</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 640px; margin: 40px auto; padding: 20px; color: #1e293b; line-height: 1.5; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { font-size: 20px; margin-top: 0; color: #0f172a; }
        .field { margin-bottom: 16px; }
        label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 6px; }
        input[type="password"], input[type="number"] { width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        .checkbox-label { display: flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer; }
        .status-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 20px; font-size: 13px; }
        .btn { background: #0284c7; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn:hover { background: #0369a1; }
        .notice { font-size: 12px; color: #64748b; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Hydrox Public Website - SQLite Cutover</h1>
        {$downBanner}
        <div class="status-box">
            <strong>Active Database Driver:</strong> {$activeDriver}<br>
            <strong>Target SQLite Path:</strong> {$sqlitePath}<br>
            <strong>Target SQLite File Exists:</strong> {$activeSqliteExists} ({$activeSqliteSize} bytes)<br>
            <strong>Migration Lock Active:</strong> {$isLocked}<br>
            <strong>Site In Maintenance Mode:</strong> {$isDown}
        </div>
        <form method="POST" action="/internal/maintenance/migrate-sqlite">
            <input type="hidden" name="_token" value="{$csrfToken}">
            <div class="field">
                <label for="token">Maintenance Secret Token</label>
                <input type="password" id="token" name="token" required autocomplete="off" placeholder="Enter INTERNAL_MAINTENANCE_TOKEN">
            </div>
            <div class="field">
                <label class="checkbox-label">
                    <input type="checkbox" name="dry_run" value="1" checked>
                    <strong>Run in Dry-Run Mode first (safely test connection and count rows without importing)</strong>
                </label>
            </div>
            <div class="field">
                <label for="chunk">Chunk Size</label>
                <input type="number" id="chunk" name="chunk" value="500" min="50" max="2000">
            </div>
            <button type="submit" class="btn">Execute Migration</button>
            <p class="notice">Note: Live import temporarily places the website in write-free maintenance mode to ensure zero lost submissions, then automatically restores it upon completion. Staging database is verified before atomic promotion.</p>
        </form>
    </div>
</body>
</html>
HTML;

        return response($html, 200)->header('Content-Type', 'text/html');
    }

    /**
     * Execute SQLite data migration via POST only.
     */
    public function migrateSqlite(Request $request): JsonResponse
    {
        // 1. Feature flag guard
        if (! (bool) config('app.sqlite_migration_enabled', env('SQLITE_MIGRATION_ENABLED', false))) {
            abort(404);
        }

        // 2. Reject query string token to prevent URL/log credential leakage
        if ($request->query('token') !== null) {
            Log::warning('Rejected maintenance request with token passed in query string.', [
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Tokens passed in query string URLs are strictly prohibited. Pass token in POST body or X-Maintenance-Token header.',
            ], 400);
        }

        // 3. Authenticate token using hash_equals
        $configuredToken = (string) config('app.internal_maintenance_token', env('INTERNAL_MAINTENANCE_TOKEN', ''));
        if (empty($configuredToken)) {
            Log::warning('Internal maintenance requested but INTERNAL_MAINTENANCE_TOKEN is not configured.');
            return response()->json([
                'status' => 'error',
                'message' => 'Maintenance endpoint is not configured.',
            ], 403);
        }

        $providedToken = (string) ($request->header('X-Maintenance-Token') ?? $request->post('token') ?? '');
        if (empty($providedToken) || ! hash_equals($configuredToken, $providedToken)) {
            Log::warning('Unauthorized access attempt to internal maintenance endpoint.', [
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 401);
        }

        // 4. Check import lock
        $targetPath = config('database.connections.sqlite.database') ?: env('SQLITE_DB_DATABASE');
        if (!empty($targetPath)) {
            $lockFile = dirname($targetPath) . '/.sqlite_migration.lock';
            if (File::exists($lockFile)) {
                $lockAge = time() - File::lastModified($lockFile);
                if ($lockAge < 1800) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Another migration is currently in progress. Please wait for it to complete.',
                    ], 409);
                }
            }
        }

        // 5. Execution parameters
        $dryRun = $request->boolean('dry_run', false);
        $chunk = max(50, min(2000, (int) $request->input('chunk', 500)));

        $startTime = microtime(true);
        Log::info('Triggering SQLite migration via maintenance endpoint.', [
            'dry_run' => $dryRun,
            'chunk' => $chunk,
            'ip' => $request->ip(),
        ]);

        // 6. Automated write-free maintenance window during non-dry-run live import
        $enteredMaintenance = false;
        if (! $dryRun) {
            Log::info('Placing application into temporary maintenance mode to block concurrent writes during cutover...');
            try {
                Artisan::call('down', [
                    '--render' => 'errors::503',
                ]);
                $enteredMaintenance = true;
            } catch (\Throwable $downEx) {
                Log::warning('Failed to enter maintenance mode via down command: ' . $downEx->getMessage());
            }
        }

        try {
            $params = [
                '--force' => true,
                '--chunk' => $chunk,
            ];
            if ($dryRun) {
                $params['--dry-run'] = true;
            }

            $exitCode = Artisan::call('hydrox:migrate-to-sqlite', $params);
            $output = Artisan::output();
            $durationSeconds = round(microtime(true) - $startTime, 2);

            if ($exitCode !== 0) {
                Log::error('SQLite migration failed via maintenance endpoint.', [
                    'exit_code' => $exitCode,
                    'output' => $output,
                ]);

                // Return redacted safe error response
                return response()->json([
                    'status' => 'failed',
                    'exit_code' => $exitCode,
                    'dry_run' => $dryRun,
                    'duration_seconds' => $durationSeconds,
                    'message' => 'Migration failed. Active database and MySQL source were left untouched. Check Laravel log files for details.',
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'exit_code' => 0,
                'dry_run' => $dryRun,
                'duration_seconds' => $durationSeconds,
                'message' => $dryRun ? 'Dry run completed successfully.' : 'Migration and atomic promotion completed successfully.',
                'output' => $output,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Exception during SQLite migration via maintenance endpoint: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            // Redacted safe error response
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during execution. Databases were left untouched. Check Laravel log files for details.',
                'duration_seconds' => round(microtime(true) - $startTime, 2),
            ], 500);
        } finally {
            // Restore application from maintenance mode
            if ($enteredMaintenance) {
                try {
                    Artisan::call('up');
                    Log::info('Application brought out of maintenance mode after migration.');
                } catch (\Throwable $upEx) {
                    Log::emergency('Failed to bring application out of maintenance mode: ' . $upEx->getMessage());
                }
            }
        }
    }

    /**
     * Emergency recovery endpoint to bring application out of maintenance mode.
     */
    public function bringUp(Request $request): JsonResponse
    {
        // 1. Feature flag guard
        if (! (bool) config('app.sqlite_migration_enabled', env('SQLITE_MIGRATION_ENABLED', false))) {
            abort(404);
        }

        // 2. Reject query string token
        if ($request->query('token') !== null) {
            return response()->json(['status' => 'error', 'message' => 'Tokens in query string prohibited.'], 400);
        }

        // 3. Authenticate
        $configuredToken = (string) config('app.internal_maintenance_token', env('INTERNAL_MAINTENANCE_TOKEN', ''));
        $providedToken = (string) ($request->header('X-Maintenance-Token') ?? $request->post('token') ?? '');
        if (empty($configuredToken) || empty($providedToken) || ! hash_equals($configuredToken, $providedToken)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        try {
            Artisan::call('up');
            Log::info('Application brought out of maintenance mode via emergency recovery endpoint.');
            return response()->json([
                'status' => 'success',
                'message' => 'Application brought out of maintenance mode successfully.',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to bring application up: ' . $e->getMessage(),
            ], 500);
        }
    }
}
