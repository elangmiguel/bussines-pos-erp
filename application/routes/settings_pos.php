<?php

use App\Http\Controllers\Settings\EmpresaController;
use App\Http\Controllers\Settings\ResolucionDianController;
use App\Http\Controllers\Settings\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/empresa', [EmpresaController::class, 'show'])->name('empresa');
    Route::put('/empresa', [EmpresaController::class, 'update'])->name('empresa.update');

    Route::resource('usuarios', UsuarioController::class)->except(['show']);

    Route::get('/dian', [ResolucionDianController::class, 'index'])->name('dian.index');
    Route::post('/dian', [ResolucionDianController::class, 'store'])->name('dian.store');
    Route::patch('/dian/{resolucion}/activar', [ResolucionDianController::class, 'activar'])->name('dian.activar');
});
