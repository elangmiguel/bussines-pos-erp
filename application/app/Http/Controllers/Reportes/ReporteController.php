<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Services\ReporteService;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ReporteController extends Controller
{
    public function __construct(private ReporteService $reporteService) {}

    /**
     * Página inicial del módulo: selector de reportes.
     */
    public function index(): Response
    {
        return Inertia::render('Reportes/Index');
    }

    /**
     * Reporte de ventas por período con top productos.
     */
    public function ventas(Request $request): Response
    {
        $desde      = Carbon::parse($request->input('desde', Carbon::now()->startOfMonth()->toDateString()));
        $hasta      = Carbon::parse($request->input('hasta', Carbon::now()->endOfMonth()->toDateString()));
        $agrupacion = $request->input('agrupacion', 'dia');

        if (! in_array($agrupacion, ['dia', 'semana', 'mes'], true)) {
            $agrupacion = 'dia';
        }

        $ventasPeriodo = $this->reporteService->ventasPorPeriodoLegacy($agrupacion, $desde, $hasta);
        $topProductos  = $this->reporteService->topProductos($desde, $hasta, 10);

        $totalVentas   = round($ventasPeriodo->sum('total_ventas'), 2);
        $countFacturas = $ventasPeriodo->sum('count_facturas');
        $promedioVenta = $countFacturas > 0 ? round($totalVentas / $countFacturas, 2) : 0.0;

        return Inertia::render('Reportes/Ventas', [
            'ventas_periodo' => $ventasPeriodo,
            'top_productos'  => $topProductos,
            'total_ventas'   => $totalVentas,
            'count_facturas' => $countFacturas,
            'promedio_venta' => $promedioVenta,
            'filtros'        => [
                'desde'      => $desde->toDateString(),
                'hasta'      => $hasta->toDateString(),
                'agrupacion' => $agrupacion,
            ],
        ]);
    }

    /**
     * Exportar CSV de ventas por período (ruta dedicada).
     */
    public function exportarVentas(Request $request): HttpResponse
    {
        $desde    = Carbon::parse($request->input('desde', Carbon::now()->startOfMonth()->toDateString()));
        $hasta    = Carbon::parse($request->input('hasta', Carbon::now()->endOfMonth()->toDateString()));
        $csv      = $this->buildCsvVentas($desde, $hasta);
        $bom      = "\xEF\xBB\xBF";
        $filename = "ventas_{$desde->format('Ymd')}_{$hasta->format('Ymd')}.csv";

        return response($bom . $csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Reporte de inventario con kardex opcional por producto.
     */
    public function inventario(Request $request): Response
    {
        $productoId = $request->input('producto_id');
        $desde      = Carbon::parse($request->input('desde', Carbon::now()->startOfMonth()->toDateString()));
        $hasta      = Carbon::parse($request->input('hasta', Carbon::now()->endOfMonth()->toDateString()));

        $productos = Producto::with(['categoria', 'tarifaIva'])
            ->orderBy('nombre')
            ->get()
            ->map(fn (Producto $p) => [
                'id'            => $p->id,
                'codigo'        => $p->codigo,
                'nombre'        => $p->nombre,
                'stock_actual'  => (float) $p->stock_actual,
                'stock_minimo'  => (float) $p->stock_minimo,
                'stock_maximo'  => (float) $p->stock_maximo,
                'precio_compra' => (float) $p->precio_compra,
                'precio_venta'  => (float) $p->precio_venta,
                'categoria'     => $p->categoria?->nombre,
                'tarifa_iva'    => $p->tarifaIva?->porcentaje,
                'activo'        => $p->activo,
            ]);

        $kardex = null;

        if ($productoId) {
            $kardex = $this->reporteService->kardex((int) $productoId, $desde, $hasta);
        }

        return Inertia::render('Reportes/Inventario', [
            'productos' => $productos,
            'kardex'    => $kardex,
            'filtros'   => [
                'producto_id' => $productoId ? (int) $productoId : null,
                'desde'       => $desde->toDateString(),
                'hasta'       => $hasta->toDateString(),
            ],
        ]);
    }

    /**
     * Reporte de cartera por antigüedad.
     */
    public function cartera(Request $request): Response
    {
        $antiguedad = $this->reporteService->antiguedadCartera();

        $totalCartera = array_sum(array_column($antiguedad['buckets'], 'total'));
        $totalVencida = round(
            ($antiguedad['buckets']['31-60']['total'] ?? 0)
            + ($antiguedad['buckets']['61-90']['total'] ?? 0)
            + ($antiguedad['buckets']['>90']['total'] ?? 0),
            2
        );

        return Inertia::render('Reportes/Cartera', [
            'antiguedad'    => $antiguedad,
            'total_cartera' => round($totalCartera, 2),
            'total_vencida' => $totalVencida,
        ]);
    }

    /**
     * Reporte de rentabilidad por producto.
     */
    public function rentabilidad(Request $request): Response
    {
        $desde = Carbon::parse($request->input('desde', Carbon::now()->startOfMonth()->toDateString()));
        $hasta = Carbon::parse($request->input('hasta', Carbon::now()->endOfMonth()->toDateString()));

        $productosRentabilidad = $this->reporteService->rentabilidadProductos($desde, $hasta);

        $totalIngresos  = round($productosRentabilidad->sum('ingresos_total'), 2);
        $totalCostos    = round($productosRentabilidad->sum('costo_total'), 2);
        $utilidadBruta  = round($totalIngresos - $totalCostos, 2);
        $margenPromedio = $totalIngresos > 0
            ? round(($utilidadBruta / $totalIngresos) * 100, 2)
            : 0.0;

        return Inertia::render('Reportes/Rentabilidad', [
            'productos_rentabilidad' => $productosRentabilidad,
            'total_ingresos'         => $totalIngresos,
            'total_costos'           => $totalCostos,
            'utilidad_bruta'         => $utilidadBruta,
            'margen_promedio'        => $margenPromedio,
            'filtros'                => [
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
            ],
        ]);
    }

    /**
     * Libro de ventas DIAN.
     */
    public function libroVentas(Request $request): Response
    {
        $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ]);

        $desde = Carbon::parse($request->input('desde'));
        $hasta = Carbon::parse($request->input('hasta'));

        $filas = $this->reporteService->libroVentas($desde, $hasta);

        $totales = [
            'base_0'    => round($filas->sum('base_iva_0'), 2),
            'base_5'    => round($filas->sum('base_iva_5'), 2),
            'iva_5'     => round($filas->sum('iva_5'), 2),
            'base_19'   => round($filas->sum('base_iva_19'), 2),
            'iva_19'    => round($filas->sum('iva_19'), 2),
            'inc'       => round($filas->sum('inc'), 2),
            'descuento' => round($filas->sum('descuento'), 2),
            'total'     => round($filas->sum('total'), 2),
        ];

        return Inertia::render('Reportes/DIAN/LibroVentas', [
            'filas'   => $filas,
            'desde'   => $desde->toDateString(),
            'hasta'   => $hasta->toDateString(),
            'totales' => $totales,
            'filtros' => [
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
            ],
        ]);
    }

    /**
     * Exportar libro de ventas en CSV (ruta dedicada).
     */
    public function exportarLibroVentas(Request $request): HttpResponse
    {
        $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ]);

        $desde    = Carbon::parse($request->input('desde'));
        $hasta    = Carbon::parse($request->input('hasta'));
        $csv      = $this->buildCsvLibroVentas($desde, $hasta);
        $bom      = "\xEF\xBB\xBF";
        $filename = "libro_ventas_{$desde->format('Ymd')}_{$hasta->format('Ymd')}.csv";

        return response($bom . $csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Libro de compras DIAN.
     */
    public function libroCompras(Request $request): Response
    {
        $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ]);

        $desde = Carbon::parse($request->input('desde'));
        $hasta = Carbon::parse($request->input('hasta'));

        $filas = $this->reporteService->libroCompras($desde, $hasta);

        $totales = [
            'total_compras'   => round($filas->sum('subtotal'), 2),
            'iva_descontable' => round($filas->sum('iva_descontable'), 2),
            'total'           => round($filas->sum('total'), 2),
        ];

        return Inertia::render('Reportes/DIAN/LibroCompras', [
            'filas'   => $filas,
            'desde'   => $desde->toDateString(),
            'hasta'   => $hasta->toDateString(),
            'totales' => $totales,
            'filtros' => [
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
            ],
        ]);
    }

    /**
     * Resumen de IVA para el período.
     */
    public function resumenIva(Request $request): Response
    {
        $desdeStr = $request->input('desde', Carbon::now()->startOfMonth()->toDateString());
        $hastaStr = $request->input('hasta', Carbon::now()->endOfMonth()->toDateString());

        $desde = Carbon::parse($desdeStr);
        $hasta = Carbon::parse($hastaStr);

        $resumen = $this->buildResumenIvaFromRange($desde, $hasta);

        return Inertia::render('Reportes/DIAN/ResumenIva', [
            'resumen' => $resumen,
            'filtros' => [
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
            ],
        ]);
    }

    /**
     * Exportar reporte en formato CSV compatible con Excel (UTF-8 BOM).
     * Punto de entrada genérico: tipo=libro_ventas|libro_compras|ventas|kardex
     */
    public function exportarExcel(Request $request): HttpResponse
    {
        $tipo  = $request->input('tipo', 'ventas');
        $desde = Carbon::parse($request->input('desde', Carbon::now()->startOfMonth()->toDateString()));
        $hasta = Carbon::parse($request->input('hasta', Carbon::now()->endOfMonth()->toDateString()));

        $bom      = "\xEF\xBB\xBF";
        $filename = "{$tipo}_{$desde->format('Ymd')}_{$hasta->format('Ymd')}.csv";

        $csv = match ($tipo) {
            'libro_ventas'  => $this->buildCsvLibroVentas($desde, $hasta),
            'libro_compras' => $this->buildCsvLibroCompras($desde, $hasta),
            'kardex'        => $this->buildCsvKardex($request, $desde, $hasta),
            default         => $this->buildCsvVentas($desde, $hasta),
        };

        return response($bom . $csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    /**
     * Build the IVA summary for an arbitrary date range.
     *
     * @return array{iva_generado:float,iva_descontable:float,saldo:float,detalle_generado:array,detalle_descontable:array}
     */
    private function buildResumenIvaFromRange(Carbon $desde, Carbon $hasta): array
    {
        $filas = $this->reporteService->libroVentas($desde, $hasta);

        $base5   = round($filas->sum('base_iva_5'), 2);
        $iva5    = round($filas->sum('iva_5'), 2);
        $base19  = round($filas->sum('base_iva_19'), 2);
        $iva19   = round($filas->sum('iva_19'), 2);
        $inc     = round($filas->sum('inc'), 2);

        $ivaGenerado = round($iva5 + $iva19, 2);

        $compras        = $this->reporteService->libroCompras($desde, $hasta);
        $totalCompras   = round($compras->sum('subtotal'), 2);
        $ivaDescontable = round($compras->sum('iva_descontable'), 2);

        return [
            'iva_generado'        => $ivaGenerado,
            'iva_descontable'     => $ivaDescontable,
            'saldo'               => round($ivaGenerado - $ivaDescontable, 2),
            'detalle_generado'    => [
                'base_5'  => $base5,
                'iva_5'   => $iva5,
                'base_19' => $base19,
                'iva_19'  => $iva19,
                'inc'     => $inc,
            ],
            'detalle_descontable' => [
                'total_compras' => $totalCompras,
                'iva_compras'   => $ivaDescontable,
            ],
        ];
    }

    private function buildCsvLibroVentas(Carbon $desde, Carbon $hasta): string
    {
        $filas = $this->reporteService->libroVentas($desde, $hasta);

        $headers = [
            'Fecha', '# Factura', 'Tipo ID', 'Identificación', 'Cliente',
            'Base Excluida', 'Base 5%', 'IVA 5%', 'Base 19%', 'IVA 19%',
            'INC', 'Descuento', 'Total',
        ];

        $rows = $filas->map(fn ($f) => [
            $f['fecha'],
            $f['numero_completo'],
            $f['tipo_id_cliente'],
            $f['identificacion_cliente'],
            $f['nombre_cliente'],
            number_format($f['base_iva_0'], 2, '.', ''),
            number_format($f['base_iva_5'], 2, '.', ''),
            number_format($f['iva_5'], 2, '.', ''),
            number_format($f['base_iva_19'], 2, '.', ''),
            number_format($f['iva_19'], 2, '.', ''),
            number_format($f['inc'], 2, '.', ''),
            number_format($f['descuento'], 2, '.', ''),
            number_format($f['total'], 2, '.', ''),
        ])->all();

        return $this->arrayToCsv($headers, $rows);
    }

    private function buildCsvLibroCompras(Carbon $desde, Carbon $hasta): string
    {
        $filas = $this->reporteService->libroCompras($desde, $hasta);

        $headers = [
            'Fecha', 'Tipo', '# Documento', 'NIT Proveedor', 'Proveedor',
            'Subtotal', 'IVA Descontable', 'Total',
        ];

        $rows = $filas->map(fn ($f) => [
            $f['fecha'],
            $f['tipo'] === 'gasto' ? 'Gasto' : 'Orden de Compra',
            $f['doc_numero'],
            $f['nit_proveedor'],
            $f['nombre_proveedor'],
            number_format($f['subtotal'], 2, '.', ''),
            number_format($f['iva_descontable'], 2, '.', ''),
            number_format($f['total'], 2, '.', ''),
        ])->all();

        return $this->arrayToCsv($headers, $rows);
    }

    private function buildCsvVentas(Carbon $desde, Carbon $hasta): string
    {
        $filas = $this->reporteService->ventasPorPeriodoLegacy('dia', $desde, $hasta);

        $headers = ['Período', 'Total Ventas (COP)', '# Facturas'];

        $rows = $filas->map(fn ($f) => [
            $f['periodo'],
            number_format($f['total_ventas'], 2, '.', ''),
            $f['count_facturas'],
        ])->all();

        return $this->arrayToCsv($headers, $rows);
    }

    private function buildCsvKardex(Request $request, Carbon $desde, Carbon $hasta): string
    {
        $productoId = (int) $request->input('producto_id', 0);

        if ($productoId === 0) {
            return $this->arrayToCsv(['Error'], [['Se requiere producto_id para exportar kardex']]);
        }

        $filas = $this->reporteService->kardex($productoId, $desde, $hasta);

        $headers = [
            'Fecha', 'Tipo', 'Cantidad', 'Stock Anterior', 'Stock Nuevo',
            'Costo Unitario', 'Referencia', 'Usuario',
        ];

        $rows = $filas->map(fn ($f) => [
            $f['fecha'],
            $f['tipo'],
            number_format($f['cantidad'], 3, '.', ''),
            number_format($f['stock_anterior'], 3, '.', ''),
            number_format($f['stock_nuevo'], 3, '.', ''),
            number_format($f['costo_unitario'], 2, '.', ''),
            $f['referencia_tipo'] ? "{$f['referencia_tipo']}:{$f['referencia_id']}" : ($f['motivo'] ?? ''),
            $f['usuario'],
        ])->all();

        return $this->arrayToCsv($headers, $rows);
    }

    /**
     * Converts headers + rows arrays into a CSV string.
     *
     * @param  string[]  $headers
     * @param  array[]   $rows
     */
    private function arrayToCsv(array $headers, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, $headers, ',', '"', '\\');

        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"', '\\');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
