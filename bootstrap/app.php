<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global web middleware - hanya untuk middleware yang diperlukan di semua routes
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Daftarkan middleware sebagai alias untuk digunakan di routes
        $middleware->alias([
            'check.shift' => \App\Http\Middleware\CheckActiveShift::class,
            'check.permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        // Atau jika ingin menggunakan group middleware
        $middleware->group('authenticated', [
            'auth',
            'verified',
        ]);

        $middleware->group('shift_required', [
            'auth',
            'verified',
            'check.shift',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withCommands([
        App\Console\Commands\UpdatePromotionStatus::class,
    ])
    ->create();
