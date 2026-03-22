<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust reverse proxy headers (Cloudflare Tunnel, etc.) so generated URLs keep HTTPS scheme.
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Vercel sometimes truncates long stack traces. Emit a short error summary
        // so the root cause (e.g. SQLSTATE, missing table, bad env) is visible.
        $exceptions->report(function (\Throwable $e): void {
            $summary = sprintf(
                '[ExceptionSummary] %s: %s',
                $e::class,
                $e->getMessage()
            );

            error_log($summary);

            if ($e instanceof QueryException) {
                error_log('[QueryExceptionSQL] '.$e->getSql());
            }
        });
    })->create();
