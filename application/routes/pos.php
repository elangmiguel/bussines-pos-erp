<?php

use App\Http\Controllers\POS\VentaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [VentaController::class, 'index'])->name('index');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('/buscar-producto', [VentaController::class, 'buscarProducto'])->name('buscar-producto');
    Route::get('/buscar-cliente', [VentaController::class, 'buscarCliente'])->name('buscar-cliente');
});
