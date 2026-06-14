<?php

use App\Http\Controllers\Caja\CajaController;
use App\Http\Controllers\Caja\FondoController;
use App\Http\Controllers\Caja\TurnoCajaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('caja')->name('caja.')->group(function () {
    // Cajas
    Route::get('/', [CajaController::class, 'index'])->name('index');
    Route::post('/', [CajaController::class, 'store'])->name('store');
    Route::put('/{caja}', [CajaController::class, 'update'])->name('update');
    Route::delete('/{caja}', [CajaController::class, 'destroy'])->name('destroy');

    // Turnos
    Route::get('/turnos', [TurnoCajaController::class, 'index'])->name('turnos.index');
    Route::get('/turnos/abrir', [TurnoCajaController::class, 'abrirForm'])->name('turnos.abrir.form');
    Route::post('/turnos/abrir', [TurnoCajaController::class, 'abrir'])->name('turnos.abrir');
    Route::get('/turnos/{turno}', [TurnoCajaController::class, 'show'])->name('turnos.show');
    Route::patch('/turnos/{turno}/cerrar', [TurnoCajaController::class, 'cerrar'])->name('turnos.cerrar');

    // Fondos
    Route::get('/fondos', [FondoController::class, 'index'])->name('fondos.index');
    Route::post('/fondos', [FondoController::class, 'store'])->name('fondos.store');
    Route::put('/fondos/{fondo}', [FondoController::class, 'update'])->name('fondos.update');
    Route::post('/fondos/{fondo}/movimiento', [FondoController::class, 'movimiento'])->name('fondos.movimiento');
    Route::post('/fondos/transferencia', [FondoController::class, 'transferencia'])->name('fondos.transferencia');
});
