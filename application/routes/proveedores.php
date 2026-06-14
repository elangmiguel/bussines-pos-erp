<?php

use App\Http\Controllers\Proveedores\OrdenCompraController;
use App\Http\Controllers\Proveedores\ProveedorController;
use App\Http\Controllers\Proveedores\RecepcionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('proveedores', ProveedorController::class);
    Route::prefix('compras')->name('compras.')->group(function () {
        Route::resource('ordenes', OrdenCompraController::class);
        Route::patch('ordenes/{orden}/estado', [OrdenCompraController::class, 'updateEstado'])->name('ordenes.estado');
        Route::post('recepciones', [RecepcionController::class, 'store'])->name('recepciones.store');
    });
});
