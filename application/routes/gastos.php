<?php

use App\Http\Controllers\Gastos\GastoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('gastos')->name('gastos.')->group(function () {
    Route::get('/', [GastoController::class, 'index'])->name('index');
    Route::get('/create', [GastoController::class, 'create'])->name('create');
    Route::post('/', [GastoController::class, 'store'])->name('store');
    Route::get('/{gasto}/edit', [GastoController::class, 'edit'])->name('edit');
    Route::put('/{gasto}', [GastoController::class, 'update'])->name('update');
    Route::delete('/{gasto}', [GastoController::class, 'destroy'])->name('destroy');
});
