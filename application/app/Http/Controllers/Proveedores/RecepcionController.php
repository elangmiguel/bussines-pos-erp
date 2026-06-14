<?php

namespace App\Http\Controllers\Proveedores;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedores\RecepcionRequest;
use App\Models\DetalleRecepcion;
use App\Models\OrdenCompra;
use App\Models\RecepcionMercancia;
use App\Services\InventarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class RecepcionController extends Controller
{
    public function __construct(
        private readonly InventarioService $inventarioService
    ) {}

    public function store(RecepcionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            /** @var OrdenCompra $orden */
            $orden = OrdenCompra::with('detalles')->findOrFail($data['orden_id']);

            if (! in_array($orden->estado, ['enviada', 'recibida_parcial'])) {
                abort(422, 'Solo se pueden registrar recepciones para órdenes enviadas o parcialmente recibidas.');
            }

            $recepcion = RecepcionMercancia::create([
                'orden_id'      => $orden->id,
                'user_id'       => auth()->id(),
                'fecha'         => $data['fecha'],
                'observaciones' => $data['observaciones'] ?? null,
                'estado'        => 'parcial',
            ]);

            $tieneNovedad    = false;
            $totalEsperado   = 0;
            $totalRecibido   = 0;

            foreach ($data['items'] as $item) {
                $detalle = $orden->detalles->firstWhere('id', $item['detalle_id']);

                if (! $detalle) {
                    continue;
                }

                $cantidadRecibida = (float) $item['cantidad_recibida'];
                $novedad          = $item['novedad'] ?? null;

                if ($novedad) {
                    $tieneNovedad = true;
                }

                DetalleRecepcion::create([
                    'recepcion_id'     => $recepcion->id,
                    'producto_id'      => $detalle->producto_id,
                    'cantidad_esperada' => (float) $detalle->cantidad,
                    'cantidad_recibida' => $cantidadRecibida,
                    'precio_unitario'  => (float) $detalle->precio_unitario,
                    'novedad'          => $novedad,
                ]);

                if ($cantidadRecibida > 0) {
                    $this->inventarioService->incrementarStock(
                        $detalle->producto_id,
                        $cantidadRecibida,
                        (float) $detalle->precio_unitario,
                        'recepcion',
                        $recepcion->id,
                        auth()->id()
                    );
                }

                $totalEsperado += (float) $detalle->cantidad;
                $totalRecibido += $cantidadRecibida;
            }

            // Determine recepcion estado
            if ($tieneNovedad) {
                $estadoRecepcion = 'con_novedad';
            } elseif ($totalRecibido >= $totalEsperado) {
                $estadoRecepcion = 'completa';
            } else {
                $estadoRecepcion = 'parcial';
            }

            $recepcion->update(['estado' => $estadoRecepcion]);

            // Determine orden estado based on all recepciones
            $orden->load('detalles');
            $totalOrdenEsperado = $orden->detalles->sum(fn($d) => (float) $d->cantidad);

            $totalOrdenRecibido = DetalleRecepcion::whereIn(
                'recepcion_id',
                $orden->recepciones()->pluck('id')
            )->sum('cantidad_recibida');

            if ($totalOrdenRecibido >= $totalOrdenEsperado) {
                $orden->update(['estado' => 'recibida']);
            } else {
                $orden->update(['estado' => 'recibida_parcial']);
            }
        });

        return back()->with('success', 'Recepción registrada exitosamente.');
    }
}
