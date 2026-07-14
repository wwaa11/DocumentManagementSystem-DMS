<?php

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
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\PreventBackHistory::class);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'role-manager' => \App\Http\Middleware\EnsureUserCanManageRoles::class,
            'it' => \App\Http\Middleware\CheckIT::class,
            'user' => \App\Http\Middleware\CheckUser::class,
            'purchase' => \App\Http\Middleware\CheckPurchase::class,
            'media' => \App\Http\Middleware\CheckMedia::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
