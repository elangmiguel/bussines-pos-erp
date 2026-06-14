<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Factura;
use App\Services\InventarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class FacturaController extends Controller
{
    public function __construct(private InventarioService $inventarioService) {}

    public function index(Request $request): Response
    {
        $query = Factura::with([
            'cliente.persona',
            'cliente.empresa',
            'user',
        ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_completo', 'like', "%{$search}%")
                    ->orWhereHas('cliente.persona', fn ($sq) => $sq->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%"))
                    ->orWhereHas('cliente.empresa', fn ($sq) => $sq->where('razon_social', 'like', "%{$search}%"));
            });
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        if ($estadoDian = $request->input('estado_dian')) {
            $query->where('estado_dian', $estadoDian);
        }

        if ($tipoPago = $request->input('tipo_pago')) {
            $query->where('tipo_pago', $tipoPago);
        }

        if ($desde = $request->input('fecha_desde')) {
            $query->whereDate('fecha', '>=', $desde);
        }

        if ($hasta = $request->input('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $hasta);
        }

        $facturas = $query->orderByDesc('fecha')->orderByDesc('id')->paginate(20)->withQueryString()->toInertia();

        $hoy    = now()->toDateString();
        $mesIni = now()->startOfMonth()->toDateString();
        $mesFin = now()->endOfMonth()->toDateString();

        $totalDia = Factura::whereDate('fecha', $hoy)->where('estado', '!=', 'anulada')->sum('total');
        $countDia = Factura::whereDate('fecha', $hoy)->where('estado', '!=', 'anulada')->count();

        $totalMes = Factura::whereDate('fecha', '>=', $mesIni)
            ->whereDate('fecha', '<=', $mesFin)
            ->where('estado', '!=', 'anulada')
            ->sum('total');
        $countMes = Factura::whereDate('fecha', '>=', $mesIni)
            ->whereDate('fecha', '<=', $mesFin)
            ->where('estado', '!=', 'anulada')
            ->count();

        return Inertia::render('Facturacion/Index', [
            'facturas' => $facturas,
            'filtros'  => [
                'search'      => $request->input('search', ''),
                'estado'      => $request->input('estado', ''),
                'estado_dian' => $request->input('estado_dian', ''),
                'tipo_pago'   => $request->input('tipo_pago', ''),
                'fecha_desde' => $request->input('fecha_desde', ''),
                'fecha_hasta' => $request->input('fecha_hasta', ''),
            ],
            'stats' => [
                'total_dia'  => (float) $totalDia,
                'count_dia'  => (int) $countDia,
                'total_mes'  => (float) $totalMes,
                'count_mes'  => (int) $countMes,
            ],
        ]);
    }

    public function show(Factura $factura): Response
    {
        $factura->load([
            'resolucion',
            'cliente.persona',
            'cliente.empresa',
            'user',
            'detalles.producto',
            'detalles.tarifaIva',
            'pagos.medioPago',
            'notasCredito.user',
            'notasDebito.user',
            'cuentaPorCobrar',
        ]);

        $configuracion = Configuracion::with('empresa')->first();

        return Inertia::render('Facturacion/Show', [
            'factura'       => $factura,
            'configuracion' => $configuracion,
        ]);
    }

    public function anular(Factura $factura): RedirectResponse
    {
        if ($factura->estado !== 'emitida') {
            return back()->with('error', 'Solo se pueden anular facturas en estado emitida.');
        }

        if ($factura->notasCredito()->exists()) {
            return back()->with('error', 'No se puede anular una factura que ya tiene notas crédito.');
        }

        if ($factura->tipo_pago === 'credito') {
            $cpc = $factura->cuentaPorCobrar;
            if ($cpc && $cpc->abonos()->exists()) {
                return back()->with('error', 'No se puede anular una factura con abonos registrados.');
            }
        }

        $factura->load('detalles.producto');

        \Illuminate\Support\Facades\DB::transaction(function () use ($factura) {
            $factura->estado = 'anulada';
            $factura->save();

            foreach ($factura->detalles as $detalle) {
                if ($detalle->producto_id) {
                    $this->inventarioService->incrementarStock(
                        $detalle->producto_id,
                        (float) $detalle->cantidad,
                        (float) ($detalle->producto->precio_compra ?? 0),
                        'anulacion_factura',
                        $factura->id,
                        auth()->id()
                    );
                }
            }

            if ($factura->tipo_pago === 'credito' && $factura->cuentaPorCobrar) {
                $factura->cuentaPorCobrar->estado = 'pagada';
                $factura->cuentaPorCobrar->save();
            }
        });

        return back()->with('success', "Factura {$factura->numero_completo} anulada correctamente.");
    }

    public function descargarPdf(Factura $factura): mixed
    {
        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return back()->with('error', 'PDF no disponible. Instale barryvdh/laravel-dompdf');
        }

        $factura->load([
            'resolucion',
            'cliente.persona',
            'cliente.empresa',
            'detalles.producto',
            'detalles.tarifaIva',
            'pagos.medioPago',
        ]);

        $configuracion = Configuracion::with('empresa')->first();

        /** @var \Barryvdh\DomPDF\PDF $pdf */
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.factura', [
            'factura'       => $factura,
            'configuracion' => $configuracion,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("{$factura->numero_completo}.pdf");
    }

    public function descargarXml(Factura $factura): HttpResponse
    {
        return response($factura->xml_dian ?? '', 200, [
            'Content-Type'        => 'text/xml',
            'Content-Disposition' => "attachment; filename=\"{$factura->numero_completo}.xml\"",
        ]);
    }
}
