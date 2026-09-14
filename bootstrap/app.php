<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        \App\Providers\AppServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/bookings',
            'api/bookings/*',
            'internal/maintenance/*',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\PreventBackButtonCache::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            $message = 'The upload is too large for the current server limit. Please upload only relevant documents, keep each file under 10 MB, and try fewer files at once if needed.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return redirect()
                ->back()
                ->withInput($request->except(array_keys($request->files->all())))
                ->withErrors(['files' => $message]);
        });

        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your session has expired. Please log in again.'], 419);
            }

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Your session has expired. Please log in again.']);
        });

        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request) {
            if (Auth::check() || $request->expectsJson()) {
                return null;
            }

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Please log in again to continue.']);
        });
    })->create();
