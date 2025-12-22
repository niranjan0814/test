<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        $middleware->append(\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);
        $middleware->append(HandleCors::class);
        $middleware->alias([
            'is_super_admin' => \App\Http\Middleware\EnsureUserIsSuperAdmin::class,
            'is_admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'admin_only' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'is_field_officer' => \App\Http\Middleware\EnsureUserIsFieldOfficer::class,
            'is_manager' => \App\Http\Middleware\EnsureUserIsManager::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'statusCode' => 4010,
                    'message' => 'Unauthorized access'
                ], 401);
            }
        });
    })->create();
