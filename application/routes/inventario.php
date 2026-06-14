<?php

use App\Http\Controllers\Inventario\AjusteController;
use App\Http\Controllers\Inventario\CategoriaController;
use App\Http\Controllers\Inventario\ProductoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('inventario')->name('inventario.')->group(function () {
    Route::resource('productos', ProductoController::class);
    Route::resource('categorias', CategoriaController::class);
    Route::post('ajustes', [AjusteController::class, 'store'])->name('ajustes.store');
});
