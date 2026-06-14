<?php

namespace App\Http\Controllers\Proveedores;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedores\OrdenCompraRequest;
use App\Models\DetalleOrdenCompra;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrdenCompraController extends Controller
{
    public function index(Request $request): Response
    {
        $query = OrdenCompra::with(['proveedor.persona', 'proveedor.empresa'])
            ->withCount('detalles')
            ->orderBy('fecha', 'desc');

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('proveedor', function ($q) use ($search) {
                $q->whereHas('persona', function ($p) use ($search) {
                    $p->where('nombres', 'like', "%{$search}%")
                      ->orWhere('apellidos', 'like', "%{$search}%");
                })->orWhereHas('empresa', function ($e) use ($search) {
                    $e->where('razon_social', 'like', "%{$search}%")
                      ->orWhere('nit', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha', '<=', $request->input('fecha_hasta'));
        }

        $ordenes = $query->paginate(20)->withQueryString()->toInertia();

        return Inertia::render('Compras/OrdenCompra/Index', [
            'ordenes' => $ordenes,
            'filtros' => $request->only('estado', 'search', 'fecha_desde', 'fecha_hasta'),
        ]);
    }

    public function create(Request $request): Response
    {
        $proveedores = Proveedor::activo()
            ->with(['persona', 'empresa'])
            ->orderBy('id')
            ->get()
            ->map(fn(Proveedor $p) => [
                'id'     => $p->id,
                'nombre' => $p->nombre,
                'tipo'   => $p->tipo,
            ]);

        $productos = Producto::activo()
            ->with(['unidadMedida', 'tarifaIva'])
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Compras/OrdenCompra/Form', [
            'proveedores'        => $proveedores,
            'productos'          => $productos,
            'proveedor_id_init'  => $request->query('proveedor_id'),
        ]);
    }

    public function store(OrdenCompraRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $orden = DB::transaction(function () use ($data) {
            $orden = OrdenCompra::create([
                'proveedor_id'  => $data['proveedor_id'],
                'user_id'       => auth()->id(),
                'fecha'         => $data['fecha'],
                'fecha_esperada' => $data['fecha_esperada'] ?? null,
                'estado'        => 'borrador',
                'subtotal'      => 0,
                'iva'           => 0,
                'total'         => 0,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            $subtotalTotal = 0;
            $ivaTotal      = 0;

            foreach ($data['items'] as $item) {
                $producto  = Producto::with('tarifaIva')->findOrFail($item['producto_id']);
                $subtotal  = round($item['cantidad'] * $item['precio_unitario'], 2);
                $porcentaje = (float) ($producto->tarifaIva?->porcentaje ?? 0);
                $ivaItem   = round($subtotal * $porcentaje / 100, 2);

                DetalleOrdenCompra::create([
                    'orden_id'        => $orden->id,
                    'producto_id'     => $item['producto_id'],
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal'        => $subtotal,
                ]);

                $subtotalTotal += $subtotal;
                $ivaTotal      += $ivaItem;
            }

            $orden->update([
                'subtotal' => round($subtotalTotal, 2),
                'iva'      => round($ivaTotal, 2),
                'total'    => round($subtotalTotal + $ivaTotal, 2),
            ]);

            return $orden;
        });

        return redirect()->route('compras.ordenes.show', $orden)
            ->with('success', 'Orden de compra creada exitosamente.');
    }

    public function show(OrdenCompra $orden): Response
    {
        $orden->load([
            'proveedor.persona',
            'proveedor.empresa',
            'user',
            'detalles.producto.unidadMedida',
            'detalles.producto.tarifaIva',
            'recepciones.detalles.producto',
            'recepciones.user',
        ]);

        return Inertia::render('Compras/OrdenCompra/Show', [
            'orden' => $orden,
        ]);
    }

    public function updateEstado(Request $request, OrdenCompra $orden): RedirectResponse
    {
        $request->validate([
            'estado' => ['required', 'in:enviada,cancelada'],
        ], [
            'estado.required' => 'El estado es obligatorio.',
            'estado.in'       => 'El estado solicitado no es válido.',
        ]);

        $nuevoEstado = $request->input('estado');

        $transicionesValidas = [
            'borrador' => ['enviada'],
            'enviada'  => ['cancelada'],
        ];

        $estadoActual = $orden->estado;
        $permitidos   = $transicionesValidas[$estadoActual] ?? [];

        if (! in_array($nuevoEstado, $permitidos)) {
            return back()->withErrors(['estado' => "No es posible cambiar el estado de '{$estadoActual}' a '{$nuevoEstado}'."]);
        }

        $orden->update(['estado' => $nuevoEstado]);

        return back()->with('success', 'Estado de la orden actualizado.');
    }

    public function destroy(OrdenCompra $orden): RedirectResponse
    {
        if ($orden->estado !== 'borrador') {
            return back()->withErrors(['orden' => 'Solo se pueden eliminar órdenes en estado borrador.']);
        }

        $orden->detalles()->delete();
        $orden->delete();

        return redirect()->route('compras.ordenes.index')
            ->with('success', 'Orden de compra eliminada.');
    }
}
