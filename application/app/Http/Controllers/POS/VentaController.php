<?php

namespace App\Http\Controllers\POS;

use App\Actions\Facturas\CrearFactura;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Fondo;
use App\Models\MedioPago;
use App\Models\Producto;
use App\Models\ResolucionDian;
use App\Models\TurnoCaja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class VentaController extends Controller
{
    /**
     * POS main screen.
     */
    public function index(): Response
    {
        $turnoActivo = TurnoCaja::abierto()
            ->with(['caja', 'cajero'])
            ->first();

        $mediosPago = MedioPago::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'tipo']);

        $fondos = Fondo::where('activo', true)
            ->with('medioPago:id,nombre,tipo')
            ->get(['id', 'nombre', 'tipo', 'saldo_actual', 'medio_pago_id']);

        $resolucion = ResolucionDian::where('activo', true)->first();
        $resolucionActiva = $resolucion !== null && $resolucion->estaVigente();

        $clienteConsumidorFinal = Cliente::where('tipo_cliente', 'regular')
            ->with(['persona', 'empresa'])
            ->whereHas('persona', function ($q) {
                $q->where('nombres', 'like', '%consumidor%')
                  ->orWhere('numero_identificacion', '222222222222');
            })
            ->first();

        return Inertia::render('POS/Index', [
            'turno_activo'            => $turnoActivo,
            'medios_pago'             => $mediosPago,
            'fondos'                  => $fondos,
            'resolucion_activa'       => $resolucionActiva,
            'resolucion'              => $resolucion ? [
                'id'               => $resolucion->id,
                'prefijo'          => $resolucion->prefijo,
                'numero_actual'    => $resolucion->numero_actual,
                'rango_hasta'      => $resolucion->rango_hasta,
                'fecha_fin'        => $resolucion->fecha_fin,
            ] : null,
            'cliente_consumidor_final' => $clienteConsumidorFinal,
        ]);
    }

    /**
     * Search products by code, barcode, or name.
     */
    public function buscarProducto(Request $request): JsonResponse
    {
        $q = $request->input('q', '');

        $productos = Producto::with('tarifaIva:id,nombre,tipo,porcentaje')
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->where(function ($query) use ($q) {
                $query->where('codigo', 'like', "%{$q}%")
                      ->orWhere('codigo_barras', 'like', "%{$q}%")
                      ->orWhere('nombre', 'like', "%{$q}%");
            })
            ->orderByRaw("CASE WHEN codigo = ? THEN 0 WHEN codigo_barras = ? THEN 1 ELSE 2 END", [$q, $q])
            ->orderBy('nombre')
            ->limit(10)
            ->get([
                'id',
                'codigo',
                'codigo_barras',
                'nombre',
                'precio_venta',
                'stock_actual',
                'tarifa_iva_id',
            ]);

        return response()->json($productos->map(function ($p) {
            return [
                'id'              => $p->id,
                'codigo'          => $p->codigo,
                'codigo_barras'   => $p->codigo_barras,
                'nombre'          => $p->nombre,
                'precio_venta'    => (float) $p->precio_venta,
                'stock_actual'    => (float) $p->stock_actual,
                'tarifa_iva'      => $p->tarifaIva ? [
                    'id'          => $p->tarifaIva->id,
                    'nombre'      => $p->tarifaIva->nombre,
                    'tipo'        => $p->tarifaIva->tipo,
                    'porcentaje'  => (float) $p->tarifaIva->porcentaje,
                ] : null,
            ];
        }));
    }

    /**
     * Store a new sale (invoice).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'turno_caja_id'            => ['required', 'exists:turnos_caja,id'],
            'cliente_id'               => ['required', 'exists:clientes,id'],
            'tipo_pago'                => ['required', 'in:contado,credito'],
            'descuento_global'         => ['nullable', 'numeric', 'min:0'],
            'plazo_dias'               => ['required_if:tipo_pago,credito', 'nullable', 'integer', 'min:1'],
            'observaciones'            => ['nullable', 'string', 'max:500'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.producto_id'      => ['required', 'exists:productos,id'],
            'items.*.cantidad'         => ['required', 'numeric', 'min:0.001'],
            'items.*.precio_unitario'  => ['required', 'numeric', 'min:0'],
            'items.*.descuento_pct'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pagos'                    => ['required_if:tipo_pago,contado', 'nullable', 'array'],
            'pagos.*.medio_pago_id'    => ['required', 'exists:medios_pago,id'],
            'pagos.*.monto'            => ['required', 'numeric', 'min:0.01'],
            'pagos.*.referencia'       => ['nullable', 'string', 'max:255'],
        ]);

        $validated['descuento_global'] = $validated['descuento_global'] ?? 0;
        $validated['pagos']            = $validated['pagos'] ?? [];

        try {
            $factura = app(CrearFactura::class)->execute($validated, Auth::id());

            return response()->json([
                'success'         => true,
                'factura_id'      => $factura->id,
                'numero_completo' => $factura->numero_completo,
                'total'           => (float) $factura->total,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Search clients by name or identification number.
     */
    public function buscarCliente(Request $request): JsonResponse
    {
        $q = $request->input('q', '');

        $clientes = Cliente::with(['persona:id,nombres,apellidos,numero_identificacion,nombre_completo', 'empresa:id,razon_social,nit'])
            ->where('activo', true)
            ->where(function ($query) use ($q) {
                $query->whereHas('persona', function ($p) use ($q) {
                    $p->where('nombres', 'like', "%{$q}%")
                      ->orWhere('apellidos', 'like', "%{$q}%")
                      ->orWhere('numero_identificacion', 'like', "%{$q}%");
                })->orWhereHas('empresa', function ($e) use ($q) {
                    $e->where('razon_social', 'like', "%{$q}%")
                      ->orWhere('nit', 'like', "%{$q}%");
                });
            })
            ->orderBy('tipo_cliente')
            ->limit(8)
            ->get();

        return response()->json($clientes->map(function ($c) {
            return [
                'id'             => $c->id,
                'nombre'         => $c->nombre,
                'identificacion' => $c->identificacion,
                'tipo'           => $c->tipo,
                'tipo_cliente'   => $c->tipo_cliente,
                'credito_activo' => $c->credito_activo,
                'limite_credito' => (float) $c->limite_credito,
                'plazo_dias'     => $c->plazo_dias,
            ];
        }));
    }
}
