<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventario\ProductoRequest;
use App\Models\CategoriaProducto;
use App\Models\Producto;
use App\Models\TarifaIva;
use App\Models\UnidadMedida;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductoController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Producto::with(['categoria', 'unidadMedida', 'tarifaIva'])
            ->withTrashed(false);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('codigo_barras', 'like', "%{$search}%");
            });
        }

        if ($categoriaId = $request->input('categoria_id')) {
            $query->where('categoria_id', $categoriaId);
        }

        if ($request->filled('activo') && $request->input('activo') !== '') {
            $query->where('activo', filter_var($request->input('activo'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->boolean('bajo_stock')) {
            $query->bajoStock();
        }

        $productos = $query->orderBy('nombre')->paginate(20)->withQueryString()->toInertia();

        return Inertia::render('Inventario/Index', [
            'productos'  => $productos,
            'categorias' => CategoriaProducto::where('activo', true)->orderBy('nombre')->get(),
            'tarifas'    => TarifaIva::where('activo', true)->orderBy('nombre')->get(),
            'unidades'   => UnidadMedida::orderBy('nombre')->get(),
            'filtros'    => [
                'search'       => $request->input('search', ''),
                'categoria_id' => $request->input('categoria_id', ''),
                'bajo_stock'   => $request->boolean('bajo_stock'),
                'activo'       => $request->input('activo', ''),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Inventario/Form', [
            'producto'   => null,
            'categorias' => CategoriaProducto::where('activo', true)->orderBy('nombre')->get(),
            'tarifas'    => TarifaIva::where('activo', true)->orderBy('nombre')->get(),
            'unidades'   => UnidadMedida::orderBy('nombre')->get(),
        ]);
    }

    public function store(ProductoRequest $request): RedirectResponse
    {
        $producto = Producto::create($request->validated());

        return redirect()
            ->route('inventario.productos.show', $producto)
            ->with('success', 'Producto creado correctamente.');
    }

    public function show(Producto $producto): Response
    {
        $producto->load(['categoria', 'unidadMedida', 'tarifaIva']);

        $movimientos = $producto->movimientosInventario()
            ->with('user')
            ->latest('created_at')
            ->limit(30)
            ->get();

        $proveedores = $producto->proveedores()->get();

        return Inertia::render('Inventario/Show', [
            'producto'    => $producto,
            'movimientos' => $movimientos,
            'proveedores' => $proveedores,
        ]);
    }

    public function edit(Producto $producto): Response
    {
        return Inertia::render('Inventario/Form', [
            'producto'   => $producto->load(['categoria', 'unidadMedida', 'tarifaIva']),
            'categorias' => CategoriaProducto::where('activo', true)->orderBy('nombre')->get(),
            'tarifas'    => TarifaIva::where('activo', true)->orderBy('nombre')->get(),
            'unidades'   => UnidadMedida::orderBy('nombre')->get(),
        ]);
    }

    public function update(ProductoRequest $request, Producto $producto): RedirectResponse
    {
        $producto->update($request->validated());

        return redirect()
            ->route('inventario.productos.show', $producto)
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $producto->delete();

        return redirect()
            ->route('inventario.productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}
