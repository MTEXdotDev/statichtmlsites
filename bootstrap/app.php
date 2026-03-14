<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleAppearance::class,
        ]);

        $middleware->alias([
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);

        // Exclude page-serving routes from CSRF so subdomain requests work
        $middleware->validateCsrfTokens(except: [
            // CSRF is only needed for state-mutating requests from the browser,
            // but we serve pages publicly so no exclusions needed here.
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Return JSON for API-like file manager requests
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('*/files*') || $request->expectsJson();
        });
    })
    ->create();
