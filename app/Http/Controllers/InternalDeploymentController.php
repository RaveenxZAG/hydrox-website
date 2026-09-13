<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

/**
 * TEMPORARY DEPLOYMENT CONTROLLER
 * -------------------------------------------------------------
 * This controller provides a browser-based endpoint to run:
 *     php artisan hydrox:deploy
 * for environments without SSH / Terminal access (e.g. HostPapa cPanel).
 *
 * IMPORTANT:
 * - This endpoint is strictly temporary.
 * - Protected by DEPLOYMENT_TOKEN in .env.
 * - Safely DELETE this file and its route once deployment is complete.
 * -------------------------------------------------------------
 */
class InternalDeploymentController extends Controller
{
    /**
     * Display the deployment form.
     */
    public function show(Request $request): Response
    {
        $hasTokenConfigured = ! empty($this->getSecretToken());

        $html = $this->renderFormHtml($hasTokenConfigured);

        return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    /**
     * Execute the deployment command.
     */
    public function deploy(Request $request): Response
    {
        $secretToken = $this->getSecretToken();

        if (empty($secretToken)) {
            return response(
                $this->renderResultHtml(
                    success: false,
                    title: 'Deployment Configuration Error',
                    message: 'DEPLOYMENT_TOKEN is not configured on this server. Please set DEPLOYMENT_TOKEN in the server .env file before running deployment.',
                    output: '',
                    exitCode: 500
                ),
                500,
                ['Content-Type' => 'text/html; charset=utf-8']
            );
        }

        // Token can be supplied via POST field 'token' or 'X-Deployment-Token' header
        $suppliedToken = $request->input('token') ?? $request->header('X-Deployment-Token');

        if (empty($suppliedToken) || ! is_string($suppliedToken) || ! hash_equals($secretToken, $suppliedToken)) {
            return response(
                $this->renderResultHtml(
                    success: false,
                    title: 'Unauthorized Access',
                    message: 'Invalid or missing deployment token. Please verify your DEPLOYMENT_TOKEN.',
                    output: '',
                    exitCode: 403
                ),
                403,
                ['Content-Type' => 'text/html; charset=utf-8']
            );
        }

        // Prevent concurrent deployment runs using an atomic file lock
        $lockFile = storage_path('framework/deployment.lock');
        $lockHandle = @fopen($lockFile, 'c+');

        if (! $lockHandle || ! @flock($lockHandle, LOCK_EX | LOCK_NB)) {
            if ($lockHandle) {
                @fclose($lockHandle);
            }

            return response(
                $this->renderResultHtml(
                    success: false,
                    title: 'Deployment In Progress',
                    message: 'Another deployment process is currently executing. Please wait a moment and check back.',
                    output: '',
                    exitCode: 409
                ),
                409,
                ['Content-Type' => 'text/html; charset=utf-8']
            );
        }

        $startTime = microtime(true);
        $exitCode = 1;
        $output = '';

        try {
            // Increase execution time limit for migrations/seeding/optimization
            @set_time_limit(300);

            // Execute the deployment command
            $exitCode = Artisan::call('hydrox:deploy');
            $output = Artisan::output();
        } catch (\Throwable $e) {
            $output .= "\nException: " . $e->getMessage() . "\n" . $e->getTraceAsString();
            $exitCode = 1;
        } finally {
            @flock($lockHandle, LOCK_UN);
            @fclose($lockHandle);
        }

        $duration = round(microtime(true) - $startTime, 2);
        $sanitizedOutput = $this->sanitizeOutput($output);
        $isSuccess = ($exitCode === 0);

        return response(
            $this->renderResultHtml(
                success: $isSuccess,
                title: $isSuccess ? 'Deployment Successful' : 'Deployment Failed',
                message: "Executed 'php artisan hydrox:deploy' in {$duration}s with exit code {$exitCode}.",
                output: $sanitizedOutput,
                exitCode: $exitCode
            ),
            $isSuccess ? 200 : 500,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    /**
     * Retrieve the configured deployment token.
     */
    protected function getSecretToken(): ?string
    {
        $token = config('app.deployment_token') ?: env('DEPLOYMENT_TOKEN');

        return ! empty($token) && is_string($token) ? trim($token) : null;
    }

    /**
     * Sanitize output to prevent leaking credentials or sensitive environment secrets.
     */
    protected function sanitizeOutput(string $output): string
    {
        $sensitiveValues = [
            env('DEPLOYMENT_TOKEN'),
            config('app.deployment_token'),
            env('DB_PASSWORD'),
            env('APP_KEY'),
            env('HYDROX_ADMIN_PASSWORD'),
            env('MICROSOFT_GRAPH_CLIENT_SECRET'),
            env('TELEGRAM_BOT_TOKEN'),
            env('HYDROX_BOOKING_TOKEN'),
        ];

        foreach ($sensitiveValues as $val) {
            if (! empty($val) && is_string($val) && strlen(trim($val)) >= 4) {
                $output = str_replace(trim($val), '[REDACTED]', $output);
            }
        }

        return $output;
    }

    /**
     * Render the HTML form for GET requests.
     */
    protected function renderFormHtml(bool $hasTokenConfigured): string
    {
        $statusNotice = $hasTokenConfigured
            ? '<div class="alert alert-info"><strong>Server Ready:</strong> <code>DEPLOYMENT_TOKEN</code> is detected in <code>.env</code>. Enter it below to trigger deployment.</div>'
            : '<div class="alert alert-warning"><strong>Attention:</strong> <code>DEPLOYMENT_TOKEN</code> is not yet configured in your server <code>.env</code> file. Add <code>DEPLOYMENT_TOKEN=your_random_secret</code> to run deployment.</div>';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hydrox Website &mdash; Internal Deployment</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #0f172a;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            max-width: 520px;
            width: 100%;
            padding: 32px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
        }
        h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 8px; color: #38bdf8; display: flex; align-items: center; gap: 8px; }
        .tag { font-size: 0.75rem; background: #ea580c; color: #fff; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase; font-weight: 600; }
        p { color: #94a3b8; font-size: 0.95rem; line-height: 1.5; margin-bottom: 20px; }
        .alert { padding: 12px 16px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px; line-height: 1.4; }
        .alert-info { background: rgba(56, 189, 248, 0.15); border: 1px solid #0284c7; color: #bae6fd; }
        .alert-warning { background: rgba(245, 158, 11, 0.15); border: 1px solid #d97706; color: #fde68a; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.85em; color: #f1f5f9; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #cbd5e1; margin-bottom: 8px; }
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            background: #0f172a;
            border: 1px solid #475569;
            border-radius: 8px;
            color: #f8fafc;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s;
        }
        input[type="password"]:focus { border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2); }
        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: #0284c7;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.2s;
        }
        .btn:hover { background: #0369a1; }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .note { margin-top: 16px; font-size: 0.8rem; color: #64748b; text-align: center; line-height: 1.4; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Hydrox Deployment <span class="tag">Temporary</span></h1>
        <p>Trigger <code>php artisan hydrox:deploy</code> directly from the browser on HostPapa cPanel.</p>
        {$statusNotice}
        <form action="/internal/deploy" method="POST" id="deployForm">
            <div class="form-group">
                <label for="token">Deployment Token</label>
                <input type="password" id="token" name="token" placeholder="Paste DEPLOYMENT_TOKEN from .env" required autocomplete="off">
            </div>
            <button type="submit" class="btn" id="submitBtn">Run Deployment</button>
        </form>
        <p class="note">Executing this will run pending migrations, database seeders, storage linking, and production cache optimization.</p>
    </div>
    <script>
        document.getElementById('deployForm').addEventListener('submit', function() {
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.textContent = 'Deploying... please wait (up to 30s)';
        });
    </script>
</body>
</html>
HTML;
    }

    /**
     * Render the result HTML for POST execution.
     */
    protected function renderResultHtml(bool $success, string $title, string $message, string $output, int $exitCode): string
    {
        $badgeClass = $success ? 'badge-success' : 'badge-failed';
        $badgeText = $success ? "SUCCESS &bull; Exit Code {$exitCode}" : "FAILED &bull; Exit Code {$exitCode}";
        $escapedOutput = htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
        $escapedMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        $outputSection = '';
        if (! empty(trim($output))) {
            $outputSection = <<<HTML
            <div class="output-header">Console Output:</div>
            <pre><code>{$escapedOutput}</code></pre>
HTML;
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hydrox Deployment &mdash; Result</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #0f172a;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            max-width: 760px;
            width: 100%;
            padding: 32px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
        }
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: gap; }
        h1 { font-size: 1.4rem; font-weight: 700; color: #f8fafc; }
        .badge { padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-success { background: #166534; color: #86efac; border: 1px solid #22c55e; }
        .badge-failed { background: #991b1b; color: #fca5a5; border: 1px solid #ef4444; }
        p { color: #94a3b8; font-size: 0.95rem; line-height: 1.5; margin-bottom: 20px; }
        .output-header { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 8px; }
        pre {
            background: #090d16;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 16px;
            overflow-x: auto;
            max-height: 480px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.85rem;
            line-height: 1.6;
            color: #cbd5e1;
            white-space: pre-wrap;
            word-break: break-word;
            margin-bottom: 24px;
        }
        .actions { display: flex; gap: 12px; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-primary { background: #0284c7; color: #ffffff; border: none; }
        .btn-primary:hover { background: #0369a1; }
        .btn-secondary { background: #334155; color: #e2e8f0; border: 1px solid #475569; }
        .btn-secondary:hover { background: #475569; }
        .footer-note { margin-top: 24px; font-size: 0.8rem; color: #64748b; border-top: 1px solid #334155; padding-top: 16px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>{$escapedTitle}</h1>
            <span class="badge {$badgeClass}">{$badgeText}</span>
        </div>
        <p>{$escapedMessage}</p>
        {$outputSection}
        <div class="actions">
            <a href="/internal/deploy" class="btn btn-primary">Run Again</a>
            <a href="/" class="btn btn-secondary">Go to Homepage</a>
        </div>
        <p class="footer-note"><strong>Security Reminder:</strong> Once migrations and setup are complete, please remove or disable this temporary endpoint and delete <code>DEPLOYMENT_TOKEN</code>.</p>
    </div>
</body>
</html>
HTML;
    }
}
