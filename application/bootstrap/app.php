<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function (): void {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/inventario.php'));

            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/clientes.php'));

            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/proveedores.php'));

            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/caja.php'));

            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/settings_pos.php'));

            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/pos.php'));

            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/facturacion.php'));

            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/reportes.php'));

            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/gastos.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
