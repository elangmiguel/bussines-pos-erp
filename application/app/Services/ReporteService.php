<?php

namespace App\Services;

use App\Models\CuentaPorCobrar;
use App\Models\DetalleFactura;
use App\Models\Factura;
use App\Models\Gasto;
use App\Models\MovimientoInventario;
use App\Models\OrdenCompra;
use App\Models\Producto;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    /**
     * Ventas por período: summary + daily breakdown.
     * Compatible with SQLite (uses strftime) and PostgreSQL (uses DATE()).
     *
     * @return array{total: float, count: int, promedio: float, por_dia: array}
     */
    public function ventasPorPeriodo(string $desde, string $hasta): array
    {
        $desdeCarbon = Carbon::parse($desde)->startOfDay();
        $hastaCarbon = Carbon::parse($hasta)->endOfDay();

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $dateExpr = DB::raw("strftime('%Y-%m-%d', fecha) as fecha_dia");
        } else {
            $dateExpr = DB::raw('DATE(fecha) as fecha_dia');
        }

        $rows = Factura::select(
            $dateExpr,
            DB::raw('SUM(total) as total_dia'),
            DB::raw('COUNT(*) as count_dia')
        )
            ->where('estado', 'emitida')
            ->whereDate('fecha', '>=', $desdeCarbon->toDateString())
            ->whereDate('fecha', '<=', $hastaCarbon->toDateString())
            ->groupBy('fecha_dia')
            ->orderBy('fecha_dia')
            ->get();

        $porDia = $rows->map(fn ($row) => [
            'fecha' => $row->fecha_dia,
            'total' => round((float) $row->total_dia, 2),
            'count' => (int) $row->count_dia,
        ])->values()->all();

        $total  = round(array_sum(array_column($porDia, 'total')), 2);
        $count  = (int) array_sum(array_column($porDia, 'count'));
        $promedio = $count > 0 ? round($total / $count, 2) : 0.0;

        return [
            'total'   => $total,
            'count'   => $count,
            'promedio' => $promedio,
            'por_dia' => $porDia,
        ];
    }

    /**
     * Libro de ventas para declaración de IVA.
     * Returns one row per factura emitida in the date range.
     *
     * @return array{facturas: array, totales: array}
     */
    public function libroVentas(Carbon $desde, Carbon $hasta): Collection
    {
        return Factura::with([
            'cliente.persona',
            'cliente.empresa',
        ])
            ->where('estado', 'emitida')
            ->whereDate('fecha', '>=', $desde->toDateString())
            ->whereDate('fecha', '<=', $hasta->toDateString())
            ->orderBy('fecha')
            ->orderBy('numero_completo')
            ->get()
            ->map(function (Factura $factura): array {
                $cliente = $factura->cliente;

                if ($cliente === null) {
                    $tipoId         = '—';
                    $identificacion = '—';
                    $nombre         = 'Consumidor final';
                } elseif ($cliente->tipo === 'natural' && $cliente->persona) {
                    $tipoId         = $cliente->persona->tipo_identificacion;
                    $identificacion = $cliente->persona->numero_identificacion;
                    $nombre         = $cliente->persona->nombre_completo;
                } else {
                    $tipoId         = 'NIT';
                    $identificacion = $cliente->empresa?->nit ?? '—';
                    $nombre         = $cliente->empresa?->razon_social ?? '—';
                }

                return [
                    'fecha'                  => $factura->fecha?->toDateString(),
                    'numero_completo'        => $factura->numero_completo,
                    'tipo_id_cliente'        => $tipoId,
                    'identificacion_cliente' => $identificacion,
                    'nombre_cliente'         => $nombre,
                    'base_iva_0'             => (float) $factura->base_iva_0,
                    'base_iva_5'             => (float) $factura->base_iva_5,
                    'iva_5'                  => (float) $factura->iva_5,
                    'base_iva_19'            => (float) $factura->base_iva_19,
                    'iva_19'                 => (float) $factura->iva_19,
                    'inc'                    => (float) $factura->inc,
                    'descuento'              => (float) $factura->descuento_global,
                    'total'                  => (float) $factura->total,
                ];
            });
    }

    /**
     * Libro de compras para declaración de IVA.
     * Merges gastos and ordenes_compra (recibidas) in the date range.
     */
    public function libroCompras(Carbon $desde, Carbon $hasta): Collection
    {
        $gastos = Gasto::with(['proveedor.persona', 'proveedor.empresa'])
            ->whereDate('fecha', '>=', $desde->toDateString())
            ->whereDate('fecha', '<=', $hasta->toDateString())
            ->get()
            ->map(function (Gasto $gasto): array {
                $proveedor = $gasto->proveedor;
                $nombre    = '—';
                $nit       = '—';

                if ($proveedor) {
                    $nombre = $proveedor->nombre;
                    $nit    = $proveedor->tipo === 'natural'
                        ? ($proveedor->persona?->numero_identificacion ?? '—')
                        : ($proveedor->empresa?->nit ?? '—');
                }

                return [
                    'fecha'            => $gasto->fecha?->toDateString(),
                    'tipo'             => 'gasto',
                    'doc_numero'       => $gasto->numero_documento ?? "G-{$gasto->id}",
                    'nombre_proveedor' => $nombre,
                    'nit_proveedor'    => $nit,
                    'subtotal'         => (float) $gasto->monto,
                    'iva_descontable'  => (float) $gasto->iva,
                    'total'            => (float) $gasto->monto + (float) $gasto->iva,
                ];
            });

        $ordenes = OrdenCompra::with(['proveedor.persona', 'proveedor.empresa'])
            ->whereIn('estado', ['recibida', 'parcial'])
            ->whereDate('fecha', '>=', $desde->toDateString())
            ->whereDate('fecha', '<=', $hasta->toDateString())
            ->get()
            ->map(function (OrdenCompra $orden): array {
                $proveedor = $orden->proveedor;
                $nombre    = '—';
                $nit       = '—';

                if ($proveedor) {
                    $nombre = $proveedor->nombre;
                    $nit    = $proveedor->tipo === 'natural'
                        ? ($proveedor->persona?->numero_identificacion ?? '—')
                        : ($proveedor->empresa?->nit ?? '—');
                }

                return [
                    'fecha'            => $orden->fecha?->toDateString(),
                    'tipo'             => 'orden_compra',
                    'doc_numero'       => "OC-{$orden->id}",
                    'nombre_proveedor' => $nombre,
                    'nit_proveedor'    => $nit,
                    'subtotal'         => (float) $orden->subtotal,
                    'iva_descontable'  => (float) $orden->iva,
                    'total'            => (float) $orden->total,
                ];
            });

        return $gastos->concat($ordenes)->sortBy('fecha')->values();
    }

    /**
     * Resumen de IVA para un mes/año dados.
     *
     * @return array{ventas_iva: array, compras_iva: array, neto: float}
     */
    public function resumenIva(string $mes, string $anio): array
    {
        $desde = Carbon::createFromDate((int) $anio, (int) $mes, 1)->startOfMonth();
        $hasta = $desde->copy()->endOfMonth();

        $facturas = Factura::where('estado', 'emitida')
            ->whereDate('fecha', '>=', $desde->toDateString())
            ->whereDate('fecha', '<=', $hasta->toDateString())
            ->get();

        $base5   = $facturas->sum(fn ($f) => (float) $f->base_iva_5);
        $iva5    = $facturas->sum(fn ($f) => (float) $f->iva_5);
        $base19  = $facturas->sum(fn ($f) => (float) $f->base_iva_19);
        $iva19   = $facturas->sum(fn ($f) => (float) $f->iva_19);
        $incSum  = $facturas->sum(fn ($f) => (float) $f->inc);

        $ivaGenerado = round($iva5 + $iva19, 2);

        $compras        = $this->libroCompras($desde, $hasta);
        $ivaDescontable = round($compras->sum('iva_descontable'), 2);

        return [
            'ventas_iva' => [
                'base_5'  => round($base5, 2),
                'iva_5'   => round($iva5, 2),
                'base_19' => round($base19, 2),
                'iva_19'  => round($iva19, 2),
                'inc'     => round($incSum, 2),
                'total'   => $ivaGenerado,
            ],
            'compras_iva' => [
                'total_compras' => round($compras->sum('subtotal'), 2),
                'iva_compras'   => $ivaDescontable,
            ],
            'neto' => round($ivaGenerado - $ivaDescontable, 2),
        ];
    }

    /**
     * Inventario valorizado: all active products grouped by category.
     *
     * @return array{categorias: array, total_valorizado: float}
     */
    public function inventarioValorizado(): array
    {
        $productos = Producto::with('categoria')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $grouped = $productos->groupBy(fn ($p) => $p->categoria?->nombre ?? 'Sin categoría');

        $categorias     = [];
        $totalGlobal    = 0.0;

        foreach ($grouped as $categoriaNombre => $items) {
            $subtotal = 0.0;
            $lista    = [];

            foreach ($items as $p) {
                $valorTotal = round((float) $p->stock_actual * (float) $p->precio_compra, 2);
                $subtotal  += $valorTotal;

                $lista[] = [
                    'id'            => $p->id,
                    'codigo'        => $p->codigo,
                    'nombre'        => $p->nombre,
                    'stock_actual'  => (float) $p->stock_actual,
                    'precio_compra' => (float) $p->precio_compra,
                    'valor_total'   => $valorTotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $totalGlobal += $subtotal;

            $categorias[] = [
                'nombre'    => $categoriaNombre,
                'productos' => $lista,
                'subtotal'  => $subtotal,
            ];
        }

        return [
            'categorias'      => $categorias,
            'total_valorizado' => round($totalGlobal, 2),
        ];
    }

    /**
     * Ventas agrupadas por período (dia, semana, mes) — legacy, used by old controller methods.
     */
    public function ventasPorPeriodoLegacy(string $agrupacion, Carbon $desde, Carbon $hasta): Collection
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $select = match ($agrupacion) {
                'semana' => DB::raw("strftime('%Y-%W', fecha) as periodo"),
                'mes'    => DB::raw("strftime('%Y-%m', fecha) as periodo"),
                default  => DB::raw("strftime('%Y-%m-%d', fecha) as periodo"),
            };
        } else {
            $select = match ($agrupacion) {
                'semana' => DB::raw('YEARWEEK(fecha, 1) as periodo'),
                'mes'    => DB::raw("DATE_FORMAT(fecha, '%Y-%m') as periodo"),
                default  => DB::raw('DATE(fecha) as periodo'),
            };
        }

        return Factura::select(
            $select,
            DB::raw('SUM(total) as total_ventas'),
            DB::raw('COUNT(*) as count_facturas')
        )
            ->where('estado', 'emitida')
            ->whereDate('fecha', '>=', $desde->toDateString())
            ->whereDate('fecha', '<=', $hasta->toDateString())
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get()
            ->map(fn ($row) => [
                'periodo'        => $row->periodo,
                'total_ventas'   => round((float) $row->total_ventas, 2),
                'count_facturas' => (int) $row->count_facturas,
            ]);
    }

    /**
     * Top productos más vendidos por total en COP.
     */
    public function topProductos(Carbon $desde, Carbon $hasta, int $limit = 10): Collection
    {
        return DetalleFactura::select(
            'detalles_factura.producto_id',
            DB::raw('SUM(detalles_factura.cantidad) as total_cantidad'),
            DB::raw('SUM(detalles_factura.total) as total_ventas')
        )
            ->join('facturas', 'facturas.id', '=', 'detalles_factura.factura_id')
            ->join('productos', 'productos.id', '=', 'detalles_factura.producto_id')
            ->where('facturas.estado', 'emitida')
            ->whereDate('facturas.fecha', '>=', $desde->toDateString())
            ->whereDate('facturas.fecha', '<=', $hasta->toDateString())
            ->groupBy('detalles_factura.producto_id')
            ->orderByDesc('total_ventas')
            ->limit($limit)
            ->with('producto:id,nombre,codigo')
            ->get()
            ->map(fn ($row) => [
                'producto_id'   => $row->producto_id,
                'nombre'        => $row->producto?->nombre ?? '—',
                'codigo'        => $row->producto?->codigo ?? '—',
                'total_cantidad' => round((float) $row->total_cantidad, 3),
                'total_ventas'  => round((float) $row->total_ventas, 2),
            ]);
    }

    /**
     * Kardex de un producto en el período dado.
     */
    public function kardex(int $productoId, Carbon $desde, Carbon $hasta): Collection
    {
        return MovimientoInventario::with(['user.persona'])
            ->where('producto_id', $productoId)
            ->where(function ($q) use ($desde, $hasta) {
                $q->whereDate('created_at', '>=', $desde->toDateString())
                  ->whereDate('created_at', '<=', $hasta->toDateString());
            })
            ->orderBy('created_at')
            ->get()
            ->map(function (MovimientoInventario $mov): array {
                $usuario = $mov->user?->persona
                    ? $mov->user->persona->nombre_completo
                    : ($mov->user?->name ?? 'Sistema');

                return [
                    'id'             => $mov->id,
                    'fecha'          => $mov->created_at?->toDateTimeString(),
                    'tipo'           => $mov->tipo,
                    'cantidad'       => (float) $mov->cantidad,
                    'stock_anterior' => (float) $mov->stock_anterior,
                    'stock_nuevo'    => (float) $mov->stock_nuevo,
                    'costo_unitario' => (float) $mov->costo_unitario,
                    'referencia_tipo' => $mov->referencia_tipo,
                    'referencia_id'  => $mov->referencia_id,
                    'motivo'         => $mov->motivo,
                    'usuario'        => $usuario,
                ];
            });
    }

    /**
     * Antigüedad de cartera por rangos de días vencidos.
     */
    public function antiguedadCartera(): array
    {
        $cuentas = CuentaPorCobrar::with(['cliente.persona', 'cliente.empresa', 'factura'])
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->get();

        $today = Carbon::today();

        $buckets = [
            '0-30'  => ['count' => 0, 'total' => 0.0],
            '31-60' => ['count' => 0, 'total' => 0.0],
            '61-90' => ['count' => 0, 'total' => 0.0],
            '>90'   => ['count' => 0, 'total' => 0.0],
        ];

        $lista = $cuentas->map(function (CuentaPorCobrar $cuenta) use ($today, &$buckets): array {
            $vencimiento  = Carbon::parse($cuenta->fecha_vencimiento);
            $diasVencidos = max(0, $today->diffInDays($vencimiento, false) * -1);
            $saldo        = round($cuenta->monto_total - $cuenta->monto_pagado, 2);

            $bucketKey = match (true) {
                $diasVencidos <= 30 => '0-30',
                $diasVencidos <= 60 => '31-60',
                $diasVencidos <= 90 => '61-90',
                default             => '>90',
            };

            $buckets[$bucketKey]['count']++;
            $buckets[$bucketKey]['total'] = round($buckets[$bucketKey]['total'] + $saldo, 2);

            $cliente = $cuenta->cliente;

            return [
                'id'                => $cuenta->id,
                'cliente_id'        => $cuenta->cliente_id,
                'nombre_cliente'    => $cliente?->nombre ?? '—',
                'factura_numero'    => $cuenta->factura?->numero_completo ?? "CXC-{$cuenta->id}",
                'monto_total'       => (float) $cuenta->monto_total,
                'monto_pagado'      => (float) $cuenta->monto_pagado,
                'saldo'             => $saldo,
                'fecha_vencimiento' => $cuenta->fecha_vencimiento?->toDateString(),
                'dias_vencidos'     => $diasVencidos,
                'estado'            => $cuenta->estado,
                'bucket'            => $bucketKey,
            ];
        })->sortByDesc('dias_vencidos')->values()->all();

        return [
            'buckets' => $buckets,
            'lista'   => $lista,
        ];
    }

    /**
     * Rentabilidad por producto (margen bruto en el período).
     */
    public function rentabilidadProductos(Carbon $desde, Carbon $hasta): Collection
    {
        return DetalleFactura::select(
            'detalles_factura.producto_id',
            DB::raw('SUM(detalles_factura.cantidad) as cantidad_vendida'),
            DB::raw('SUM(detalles_factura.total) as ingresos_total')
        )
            ->join('facturas', 'facturas.id', '=', 'detalles_factura.factura_id')
            ->join('productos', 'productos.id', '=', 'detalles_factura.producto_id')
            ->where('facturas.estado', 'emitida')
            ->whereDate('facturas.fecha', '>=', $desde->toDateString())
            ->whereDate('facturas.fecha', '<=', $hasta->toDateString())
            ->groupBy('detalles_factura.producto_id')
            ->orderByDesc('ingresos_total')
            ->get()
            ->map(function ($row): array {
                $producto      = Producto::find($row->producto_id);
                $cantidad      = (float) $row->cantidad_vendida;
                $ingresos      = round((float) $row->ingresos_total, 2);
                $costoUnitario = $producto ? (float) $producto->precio_compra : 0.0;
                $costoTotal    = round($cantidad * $costoUnitario, 2);
                $utilidad      = round($ingresos - $costoTotal, 2);
                $margen        = $ingresos > 0 ? round(($utilidad / $ingresos) * 100, 2) : 0.0;

                return [
                    'producto_id'      => $row->producto_id,
                    'nombre'           => $producto?->nombre ?? '—',
                    'codigo'           => $producto?->codigo ?? '—',
                    'cantidad_vendida' => $cantidad,
                    'costo_total'      => $costoTotal,
                    'ingresos_total'   => $ingresos,
                    'utilidad_bruta'   => $utilidad,
                    'margen_pct'       => $margen,
                ];
            });
    }
}
