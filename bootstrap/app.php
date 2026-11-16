<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // The mobile apps call /self/*, /parent/*, and /driver/* without the
        // /api prefix (for example POST /self/login). Keep /api/* as well.
        then: function () {
            Route::middleware('api')
                ->name('mobile.')
                ->group(function () {
                    require base_path('routes/driver.php');
                    require base_path('routes/parent.php');
                });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'super.admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'panel.admin' => \App\Http\Middleware\EnsurePanelAdmin::class,
        ]);
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
            'payments/jazzcash/callback',
            'payments/easypaisa/callback',
            'api/*',
            'self/*',
            'parent/*',
            'driver/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
