<?php

use App\Http\Controllers\Facturacion\FacturaController;
use App\Http\Controllers\Facturacion\NotaCreditoController;
use App\Http\Controllers\Facturacion\NotaDebitoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('facturacion')->name('facturacion.')->group(function () {
    Route::get('/', [FacturaController::class, 'index'])->name('index');
    Route::get('/{factura}', [FacturaController::class, 'show'])->name('show');
    Route::patch('/{factura}/anular', [FacturaController::class, 'anular'])->name('anular');
    Route::get('/{factura}/pdf', [FacturaController::class, 'descargarPdf'])->name('pdf');
    Route::get('/{factura}/xml', [FacturaController::class, 'descargarXml'])->name('xml');

    Route::get('/{factura}/nota-credito/create', [NotaCreditoController::class, 'create'])->name('notas-credito.create');
    Route::post('/{factura}/nota-credito', [NotaCreditoController::class, 'store'])->name('notas-credito.store');
    Route::get('/{factura}/nota-debito/create', [NotaDebitoController::class, 'create'])->name('notas-debito.create');
    Route::post('/{factura}/nota-debito', [NotaDebitoController::class, 'store'])->name('notas-debito.store');
});
