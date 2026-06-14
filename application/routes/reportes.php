<?php

use App\Http\Controllers\Reportes\ReporteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [ReporteController::class, 'index'])->name('index');
    Route::get('/ventas', [ReporteController::class, 'ventas'])->name('ventas');
    Route::get('/ventas/exportar', [ReporteController::class, 'exportarVentas'])->name('ventas.exportar');
    Route::get('/dian/libro-ventas', [ReporteController::class, 'libroVentas'])->name('dian.libro-ventas');
    Route::get('/dian/libro-ventas/exportar', [ReporteController::class, 'exportarLibroVentas'])->name('dian.libro-ventas.exportar');
    Route::get('/dian/libro-compras', [ReporteController::class, 'libroCompras'])->name('dian.libro-compras');
    Route::get('/dian/resumen-iva', [ReporteController::class, 'resumenIva'])->name('dian.resumen-iva');
    Route::get('/inventario', [ReporteController::class, 'inventario'])->name('inventario');
    Route::get('/cartera', [ReporteController::class, 'cartera'])->name('cartera');
    Route::get('/rentabilidad', [ReporteController::class, 'rentabilidad'])->name('rentabilidad');
    Route::get('/exportar', [ReporteController::class, 'exportarExcel'])->name('exportar');
});
