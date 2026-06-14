<?php

use App\Http\Controllers\Clientes\CarteraController;
use App\Http\Controllers\Clientes\ClienteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('clientes', ClienteController::class);

    Route::prefix('cartera')->name('cartera.')->group(function () {
        Route::get('/', [CarteraController::class, 'index'])->name('index');
        Route::get('/antiguedad', [CarteraController::class, 'antiguedad'])->name('antiguedad');
        Route::get('/cliente/{cliente}', [CarteraController::class, 'show'])->name('show');
        Route::post('/abonar', [CarteraController::class, 'abonar'])->name('abonar');
    });
});
