<?php

namespace App\Actions\Facturas;

use App\Models\CuentaPorCobrar;
use App\Models\Factura;
use App\Models\Fondo;
use App\Models\Producto;
use App\Models\ResolucionDian;
use App\Services\DianService;
use App\Services\FondoService;
use App\Services\InventarioService;
use Illuminate\Support\Facades\DB;

class CrearFactura
{
    public function __construct(
        private readonly InventarioService $inventario,
        private readonly FondoService $fondos,
        private readonly DianService $dian,
    ) {}

    public function execute(array $data, int $userId): Factura
    {
        return DB::transaction(function () use ($data, $userId) {
            // 1. Lock and get active resolution, increment numero_actual atomically
            $resolucion = ResolucionDian::where('activo', true)->lockForUpdate()->firstOrFail();

            if (! $resolucion->estaVigente()) {
                throw new \Exception('No hay resolución DIAN vigente para facturar.');
            }

            $resolucion->increment('numero_actual');
            $resolucion->refresh();

            $numero        = $resolucion->numero_actual;
            $prefijo       = $resolucion->prefijo;
            $numeroCompleto = $prefijo ? "{$prefijo}{$numero}" : (string) $numero;

            // 2. Create Factura with zeroed totals (updated after line items)
            $factura = Factura::create([
                'numero'           => $numero,
                'prefijo'          => $prefijo,
                'numero_completo'  => $numeroCompleto,
                'resolucion_id'    => $resolucion->id,
                'turno_caja_id'    => $data['turno_caja_id'],
                'cliente_id'       => $data['cliente_id'],
                'user_id'          => $userId,
                'fecha'            => now(),
                'fecha_vencimiento' => $data['tipo_pago'] === 'credito'
                    ? now()->addDays($data['plazo_dias'] ?? 30)->toDateString()
                    : null,
                'tipo_pago'        => $data['tipo_pago'],
                'subtotal'         => 0,
                'descuento_global' => $data['descuento_global'] ?? 0,
                'base_iva_0'       => 0,
                'base_iva_5'       => 0,
                'base_iva_19'      => 0,
                'iva_5'            => 0,
                'iva_19'           => 0,
                'inc'              => 0,
                'total'            => 0,
                'estado'           => 'emitida',
                'estado_dian'      => 'pendiente',
                'observaciones'    => $data['observaciones'] ?? null,
            ]);

            // 3. Create line items and accumulate tax breakdown
            $subtotal = 0;
            $base0    = 0;
            $base5    = 0;
            $base19   = 0;
            $iva5     = 0;
            $iva19    = 0;
            $incTotal = 0;

            foreach ($data['items'] as $item) {
                $producto = Producto::with('tarifaIva')->findOrFail($item['producto_id']);
                $tarifa   = $producto->tarifaIva;

                $lineBase = round(
                    $item['cantidad'] * $item['precio_unitario'] * (1 - ($item['descuento_pct'] ?? 0) / 100),
                    2
                );
                $lineIva = 0;

                if ($tarifa->tipo === 'iva' && $tarifa->porcentaje == 5) {
                    $base5   += $lineBase;
                    $lineIva  = round($lineBase * 0.05, 2);
                    $iva5    += $lineIva;
                } elseif ($tarifa->tipo === 'iva' && $tarifa->porcentaje == 19) {
                    $base19  += $lineBase;
                    $lineIva  = round($lineBase * 0.19, 2);
                    $iva19   += $lineIva;
                } elseif ($tarifa->tipo === 'inc') {
                    $lineIva  = round($lineBase * ($tarifa->porcentaje / 100), 2);
                    $incTotal += $lineIva;
                } else {
                    // excluido / exento
                    $base0 += $lineBase;
                }

                $subtotal += $lineBase;

                $factura->detalles()->create([
                    'producto_id'     => $item['producto_id'],
                    'descripcion'     => $producto->nombre,
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento_pct'   => $item['descuento_pct'] ?? 0,
                    'tarifa_iva_id'   => $tarifa->id,
                    'subtotal'        => $lineBase,
                    'iva'             => $lineIva,
                    'total'           => $lineBase + $lineIva,
                ]);

                // 4. Decrement stock
                $this->inventario->descontarStock(
                    $item['producto_id'],
                    $item['cantidad'],
                    'factura',
                    $factura->id,
                    $userId
                );
            }

            $descuento = $data['descuento_global'] ?? 0;
            $total     = $subtotal + $iva5 + $iva19 + $incTotal - $descuento;

            $factura->update([
                'subtotal'    => $subtotal,
                'base_iva_0'  => $base0,
                'base_iva_5'  => $base5,
                'base_iva_19' => $base19,
                'iva_5'       => $iva5,
                'iva_19'      => $iva19,
                'inc'         => $incTotal,
                'total'       => $total,
            ]);

            // 5. Register payments for contado sales
            if ($data['tipo_pago'] === 'contado') {
                foreach ($data['pagos'] as $pago) {
                    $factura->pagos()->create([
                        'medio_pago_id' => $pago['medio_pago_id'],
                        'monto'         => $pago['monto'],
                        'referencia'    => $pago['referencia'] ?? null,
                    ]);

                    $fondo = Fondo::where('medio_pago_id', $pago['medio_pago_id'])
                        ->where('activo', true)
                        ->first();

                    if ($fondo) {
                        $this->fondos->registrarIngreso(
                            $fondo->id,
                            $pago['monto'],
                            "Venta {$numeroCompleto}",
                            'factura',
                            $factura->id,
                            $userId
                        );
                    }
                }
            }

            // 6. Create cuenta por cobrar for credito sales
            if ($data['tipo_pago'] === 'credito') {
                CuentaPorCobrar::create([
                    'cliente_id'       => $data['cliente_id'],
                    'factura_id'       => $factura->id,
                    'monto_total'      => $total,
                    'monto_pagado'     => 0,
                    'fecha_vencimiento' => $factura->fecha_vencimiento,
                    'estado'           => 'pendiente',
                ]);
            }

            // 7. Generate CUFE and DIAN data
            $cufe = $this->dian->generarCufe($factura);
            $qr   = $this->dian->generarQrData($factura->fresh());
            $xml  = $this->dian->generarXml($factura->fresh());

            $factura->update([
                'cufe'     => $cufe,
                'qr_data'  => $qr,
                'xml_dian' => $xml,
            ]);

            return $factura->fresh(['cliente', 'detalles.producto', 'pagos.medioPago']);
        });
    }
}
