<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class InternalMaintenanceController extends Controller
{
    /**
     * Trigger SQLite data migration from source database.
     */
    public function migrateSqlite(Request $request): JsonResponse
    {
        // 1. Feature flag guard
        $enabled = (bool) config('app.sqlite_migration_enabled', env('SQLITE_MIGRATION_ENABLED', false));
        if (! $enabled) {
            abort(404);
        }

        // 2. Token guard
        $configuredToken = (string) config('app.internal_maintenance_token', env('INTERNAL_MAINTENANCE_TOKEN', ''));
        if (empty($configuredToken)) {
            Log::warning('Internal maintenance requested but INTERNAL_MAINTENANCE_TOKEN is not configured.');
            return response()->json([
                'status' => 'error',
                'message' => 'Maintenance endpoint is not configured.',
            ], 403);
        }

        $providedToken = (string) ($request->header('X-Maintenance-Token') ?? $request->query('token') ?? $request->input('token', ''));
        if (empty($providedToken) || ! hash_equals($configuredToken, $providedToken)) {
            Log::warning('Unauthorized access attempt to internal maintenance endpoint.', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 401);
        }

        // 3. Execution
        $dryRun = $request->boolean('dry_run', false);
        $chunk = max(50, min(2000, (int) $request->input('chunk', 500)));

        $startTime = microtime(true);
        Log::info('Triggering SQLite migration via internal maintenance endpoint.', [
            'dry_run' => $dryRun,
            'chunk' => $chunk,
            'ip' => $request->ip(),
        ]);

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

            return response()->json([
                'status' => $exitCode === 0 ? 'success' : 'failed',
                'exit_code' => $exitCode,
                'dry_run' => $dryRun,
                'duration_seconds' => $durationSeconds,
                'output' => $output,
            ], $exitCode === 0 ? 200 : 500);
        } catch (\Throwable $e) {
            Log::error('SQLite migration failed via maintenance endpoint: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'duration_seconds' => round(microtime(true) - $startTime, 2),
            ], 500);
        }
    }
}
