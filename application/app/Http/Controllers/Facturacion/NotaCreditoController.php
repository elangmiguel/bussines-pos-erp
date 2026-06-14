<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Factura;
use App\Models\NotaCredito;
use App\Services\InventarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class NotaCreditoController extends Controller
{
    public function __construct(private InventarioService $inventarioService) {}

    public function create(Factura $factura): Response
    {
        $factura->load([
            'cliente.persona',
            'cliente.empresa',
            'detalles.producto',
            'detalles.tarifaIva',
        ]);

        return Inertia::render('Facturacion/NotaCredito/Form', [
            'factura' => $factura,
            'motivos' => [
                ['value' => 'devolucion', 'label' => 'Devolución de mercancía'],
                ['value' => 'descuento',  'label' => 'Descuento comercial'],
                ['value' => 'anulacion',  'label' => 'Anulación de factura'],
                ['value' => 'error',      'label' => 'Error en facturación'],
            ],
        ]);
    }

    public function store(Request $request, Factura $factura): RedirectResponse
    {
        $validated = $request->validate([
            'motivo'             => ['required', 'in:devolucion,descuento,anulacion,error'],
            'descripcion'        => ['required', 'string', 'min:5'],
            'items'              => ['required_if:motivo,devolucion', 'array'],
            'items.*.detalle_id' => ['required_if:motivo,devolucion', 'exists:detalles_factura,id'],
            'items.*.cantidad'   => ['required_if:motivo,devolucion', 'numeric', 'min:0.001'],
        ]);

        DB::transaction(function () use ($validated, $factura) {
            $configuracion = Configuracion::first();
            $prefijo       = $configuracion?->prefijo_nota_credito ?? 'NC';
            $numero        = NotaCredito::count() + 1;
            $numeroCompleto = $prefijo . str_pad((string) $numero, 5, '0', STR_PAD_LEFT);

            $subtotal = 0.0;
            $iva      = 0.0;

            if ($validated['motivo'] === 'devolucion' && ! empty($validated['items'])) {
                $detallesMap = $factura->load('detalles.tarifaIva')->detalles->keyBy('id');

                foreach ($validated['items'] as $item) {
                    $detalle = $detallesMap->get($item['detalle_id']);
                    if (! $detalle) {
                        continue;
                    }

                    $cantidadDevuelta = min((float) $item['cantidad'], (float) $detalle->cantidad);
                    $proporcion       = $cantidadDevuelta / (float) $detalle->cantidad;
                    $subtotal        += round((float) $detalle->subtotal * $proporcion, 2);
                    $iva             += round((float) $detalle->iva * $proporcion, 2);
                }
            } else {
                $subtotal = (float) $factura->subtotal;
                $iva      = (float) ($factura->iva_5 + $factura->iva_19);
            }

            $total = $subtotal + $iva;

            $nc = NotaCredito::create([
                'numero'          => $numero,
                'numero_completo' => $numeroCompleto,
                'factura_id'      => $factura->id,
                'user_id'         => auth()->id(),
                'fecha'           => now(),
                'motivo'          => $validated['motivo'],
                'descripcion'     => $validated['descripcion'],
                'subtotal'        => $subtotal,
                'iva'             => $iva,
                'total'           => $total,
                'estado'          => 'emitida',
                'estado_dian'     => 'pendiente',
            ]);

            if ($validated['motivo'] === 'devolucion' && ! empty($validated['items'])) {
                $detallesMap = $factura->load('detalles.producto')->detalles->keyBy('id');

                foreach ($validated['items'] as $item) {
                    $detalle = $detallesMap->get($item['detalle_id']);
                    if (! $detalle || ! $detalle->producto_id) {
                        continue;
                    }

                    $cantidadDevuelta = min((float) $item['cantidad'], (float) $detalle->cantidad);
                    $this->inventarioService->incrementarStock(
                        $detalle->producto_id,
                        $cantidadDevuelta,
                        (float) ($detalle->producto->precio_compra ?? 0),
                        'devolucion_nota_credito',
                        $nc->id,
                        auth()->id()
                    );
                }
            }
        });

        return redirect()
            ->route('facturacion.show', $factura)
            ->with('success', 'Nota crédito emitida correctamente.');
    }
}
