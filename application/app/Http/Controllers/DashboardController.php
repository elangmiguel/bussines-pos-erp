<?php

namespace App\Http\Controllers;

use App\Models\CuentaPorCobrar;
use App\Models\DetalleFactura;
use App\Models\Factura;
use App\Models\Gasto;
use App\Models\Producto;
use App\Models\TurnoCaja;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        // Ventas hoy
        $ventasHoy = Factura::where('estado', 'emitida')
            ->whereDate('fecha', $hoy)
            ->selectRaw('COALESCE(SUM(total), 0) as total, COUNT(*) as count')
            ->first();

        // Ventas este mes
        $ventasMes = Factura::where('estado', 'emitida')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->selectRaw('COALESCE(SUM(total), 0) as total, COUNT(*) as count')
            ->first();

        // Ventas últimos 7 días (incluyendo hoy)
        $diasSemana = collect();
        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

        $ventasPorDia = Factura::where('estado', 'emitida')
            ->whereBetween('fecha', [Carbon::today()->subDays(6)->startOfDay(), Carbon::today()->endOfDay()])
            ->selectRaw('DATE(fecha) as dia, COALESCE(SUM(total), 0) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::today()->subDays($i);
            $fechaStr = $fecha->toDateString();
            $nombreDia = $dias[$fecha->dayOfWeek];
            $diasSemana->push([
                'dia' => $nombreDia,
                'total' => (float) ($ventasPorDia[$fechaStr] ?? 0),
                'fecha' => $fechaStr,
            ]);
        }

        // Cartera total (pendiente + parcial)
        $carteraTotal = CuentaPorCobrar::whereIn('estado', ['pendiente', 'parcial'])
            ->selectRaw('COALESCE(SUM(monto_total - monto_pagado), 0) as saldo')
            ->value('saldo');

        // Cartera vencida
        $carteraVencida = CuentaPorCobrar::whereIn('estado', ['pendiente', 'parcial'])
            ->where('fecha_vencimiento', '<', $hoy->toDateString())
            ->selectRaw('COALESCE(SUM(monto_total - monto_pagado), 0) as saldo')
            ->value('saldo');

        // Productos bajo stock
        $productosBajoStock = Producto::where('activo', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->count();

        // Top 5 productos del mes
        $topProductosMes = DetalleFactura::join('facturas', 'detalles_factura.factura_id', '=', 'facturas.id')
            ->join('productos', 'detalles_factura.producto_id', '=', 'productos.id')
            ->where('facturas.estado', 'emitida')
            ->whereBetween('facturas.fecha', [$inicioMes, $finMes])
            ->selectRaw('
                productos.nombre,
                productos.codigo,
                COALESCE(SUM(detalles_factura.total), 0) as total_vendido,
                COALESCE(SUM(detalles_factura.cantidad), 0) as cantidad
            ')
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'nombre' => $item->nombre,
                'codigo' => $item->codigo,
                'total_vendido' => (float) $item->total_vendido,
                'cantidad' => (float) $item->cantidad,
            ]);

        // Turno activo
        $turnoActivo = TurnoCaja::with(['caja', 'cajero.colaborador.user'])
            ->where('estado', 'abierto')
            ->latest()
            ->first();

        // Últimas 5 facturas emitidas
        $ultimasFacturas = Factura::with(['cliente.persona', 'cliente.empresa'])
            ->where('estado', 'emitida')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($f) => [
                'id' => $f->id,
                'numero_completo' => $f->numero_completo,
                'fecha' => $f->fecha,
                'total' => (float) $f->total,
                'estado' => $f->estado,
                'cliente_nombre' => $f->cliente?->nombre ?? 'Consumidor final',
            ]);

        // Gastos del mes
        $gastosMes = Gasto::whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->sum('monto');

        return Inertia::render('Dashboard', [
            'ventas_hoy' => [
                'total' => (float) ($ventasHoy->total ?? 0),
                'count' => (int) ($ventasHoy->count ?? 0),
            ],
            'ventas_mes' => [
                'total' => (float) ($ventasMes->total ?? 0),
                'count' => (int) ($ventasMes->count ?? 0),
            ],
            'ventas_semana' => $diasSemana->values()->toArray(),
            'cartera_total' => (float) $carteraTotal,
            'cartera_vencida' => (float) $carteraVencida,
            'productos_bajo_stock' => $productosBajoStock,
            'top_productos_mes' => $topProductosMes->values()->toArray(),
            'turno_activo' => $turnoActivo ? [
                'id' => $turnoActivo->id,
                'caja_id' => $turnoActivo->caja_id,
                'apertura' => $turnoActivo->apertura,
                'estado' => $turnoActivo->estado,
                'caja' => $turnoActivo->caja ? ['id' => $turnoActivo->caja->id, 'nombre' => $turnoActivo->caja->nombre] : null,
                'cajero_nombre' => $turnoActivo->cajero?->colaborador?->user?->display_name
                    ?? $turnoActivo->cajero?->colaborador?->user?->name
                    ?? 'Sin cajero',
            ] : null,
            'ultimas_facturas' => $ultimasFacturas->values()->toArray(),
            'gastos_mes' => (float) $gastosMes,
        ]);
    }
}
