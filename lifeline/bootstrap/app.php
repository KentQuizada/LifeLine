<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => \App\Http\Middleware\EnsureUserHasRole::class]);
        // /api/v1 always answers in JSON, also from middleware that would otherwise redirect (DEC-015).
        $middleware->api(prepend: [\App\Http\Middleware\ForceJsonResponse::class]);
        // Logs out every other session once the password changes (e.g. after a password reset).
        $middleware->web(append: [\Illuminate\Session\Middleware\AuthenticateSession::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Every /api/v1 error is JSON: {"message": "..."}, plus "errors" for validation (422).
        // Web routes keep Laravel's default: JSON only when the client asks for it.
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());
    })->create();
