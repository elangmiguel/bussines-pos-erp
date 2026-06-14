<?php

namespace App\Services;

use App\Events\StockBajoDetectado;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class InventarioService
{
    /**
     * Descontar stock de un producto (salida, venta, devolución, traslado).
     */
    public function descontarStock(
        int $productoId,
        float $cantidad,
        string $referenciaType,
        int $referenciaId,
        int $userId
    ): void {
        DB::transaction(function () use ($productoId, $cantidad, $referenciaType, $referenciaId, $userId) {
            /** @var Producto $producto */
            $producto = Producto::lockForUpdate()->findOrFail($productoId);

            $stockAnterior = (float) $producto->stock_actual;

            if ($cantidad > $stockAnterior) {
                throw new \Exception(
                    "Stock insuficiente para \"{$producto->nombre}\". "
                    . "Disponible: {$stockAnterior}, solicitado: {$cantidad}."
                );
            }

            $stockNuevo = $stockAnterior - $cantidad;

            $producto->stock_actual = $stockNuevo;
            $producto->save();

            MovimientoInventario::create([
                'producto_id'     => $productoId,
                'tipo'            => 'salida_venta',
                'cantidad'        => $cantidad,
                'stock_anterior'  => $stockAnterior,
                'stock_nuevo'     => $stockNuevo,
                'costo_unitario'  => $producto->precio_compra,
                'referencia_tipo' => $referenciaType,
                'referencia_id'   => $referenciaId,
                'motivo'          => null,
                'user_id'         => $userId,
            ]);

            // Broadcast low-stock alert after transaction commits
            StockBajoDetectado::dispatchIfBajoStock($producto->fresh());
        });
    }

    /**
     * Incrementar stock de un producto (compra, recepción, devolución de venta).
     */
    public function incrementarStock(
        int $productoId,
        float $cantidad,
        float $costoUnitario,
        string $referenciaType,
        int $referenciaId,
        int $userId
    ): void {
        DB::transaction(function () use ($productoId, $cantidad, $costoUnitario, $referenciaType, $referenciaId, $userId) {
            /** @var Producto $producto */
            $producto = Producto::lockForUpdate()->findOrFail($productoId);

            $stockAnterior = (float) $producto->stock_actual;
            $stockNuevo    = $stockAnterior + $cantidad;

            $producto->stock_actual = $stockNuevo;
            $producto->save();

            MovimientoInventario::create([
                'producto_id'     => $productoId,
                'tipo'            => 'entrada_compra',
                'cantidad'        => $cantidad,
                'stock_anterior'  => $stockAnterior,
                'stock_nuevo'     => $stockNuevo,
                'costo_unitario'  => $costoUnitario,
                'referencia_tipo' => $referenciaType,
                'referencia_id'   => $referenciaId,
                'motivo'          => null,
                'user_id'         => $userId,
            ]);
        });
    }

    /**
     * Ajustar stock de un producto a una cantidad específica (ajuste manual).
     */
    public function ajustarStock(
        int $productoId,
        float $nuevaCantidad,
        string $motivo,
        int $userId
    ): void {
        DB::transaction(function () use ($productoId, $nuevaCantidad, $motivo, $userId) {
            /** @var Producto $producto */
            $producto = Producto::lockForUpdate()->findOrFail($productoId);

            $stockAnterior = (float) $producto->stock_actual;
            $diferencia    = $nuevaCantidad - $stockAnterior;
            $tipo          = $diferencia >= 0 ? 'ajuste_positivo' : 'ajuste_negativo';

            $producto->stock_actual = $nuevaCantidad;
            $producto->save();

            MovimientoInventario::create([
                'producto_id'     => $productoId,
                'tipo'            => $tipo,
                'cantidad'        => abs($diferencia),
                'stock_anterior'  => $stockAnterior,
                'stock_nuevo'     => $nuevaCantidad,
                'costo_unitario'  => $producto->precio_compra,
                'referencia_tipo' => null,
                'referencia_id'   => null,
                'motivo'          => $motivo,
                'user_id'         => $userId,
            ]);
        });
    }
}
