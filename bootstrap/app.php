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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            // 🔥 [ចំណុចកែប្រែសំខាន់]៖ ប្រាប់ Laravel ឱ្យប្រើ File របស់អ្នកជំនួស guest ដើម
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);

        // បន្ថែម Middleware របស់អ្នកនៅត្រង់នេះ ដើម្បីកំណត់ភាសា
        $middleware->web(append: [
            \App\Http\Middleware\LocalizationMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();