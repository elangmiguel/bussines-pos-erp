<?php

namespace App\Http\Controllers\Gastos;

use App\Http\Controllers\Controller;
use App\Models\CategoriaGasto;
use App\Models\Gasto;
use App\Models\MedioPago;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class GastoController extends Controller
{
    /**
     * Display a paginated listing of gastos with filters and stats.
     */
    public function index(Request $request): Response
    {
        $query = Gasto::with(['categoria', 'proveedor', 'medioPago', 'user'])
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('descripcion', 'like', "%{$search}%")
                          ->orWhere('numero_documento', 'like', "%{$search}%");
                });
            })
            ->when($request->input('categoria_id'), fn ($q, $id) => $q->where('categoria_id', $id))
            ->when($request->input('medio_pago_id'), fn ($q, $id) => $q->where('medio_pago_id', $id))
            ->when($request->input('fecha_desde'), fn ($q, $fecha) => $q->where('fecha', '>=', $fecha))
            ->when($request->input('fecha_hasta'), fn ($q, $fecha) => $q->where('fecha', '<=', $fecha));

        $gastos = $query
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->toInertia();

        $now        = Carbon::now();
        $totalMes   = Gasto::whereYear('fecha', $now->year)
            ->whereMonth('fecha', $now->month)
            ->sum('monto');
        $totalIvaMes = Gasto::whereYear('fecha', $now->year)
            ->whereMonth('fecha', $now->month)
            ->sum('iva');
        $countMes = Gasto::whereYear('fecha', $now->year)
            ->whereMonth('fecha', $now->month)
            ->count();

        return Inertia::render('Gastos/Index', [
            'gastos'      => $gastos,
            'filtros'     => $request->only(['search', 'categoria_id', 'medio_pago_id', 'fecha_desde', 'fecha_hasta']),
            'stats'       => [
                'total_mes'     => round((float) $totalMes, 2),
                'total_iva_mes' => round((float) $totalIvaMes, 2),
                'count_mes'     => $countMes,
            ],
            'categorias'  => CategoriaGasto::orderBy('nombre')->get(['id', 'nombre']),
            'medios_pago' => MedioPago::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    /**
     * Show the form for creating a new gasto.
     */
    public function create(): Response
    {
        return Inertia::render('Gastos/Form', [
            'gasto'       => null,
            'categorias'  => CategoriaGasto::orderBy('nombre')->get(['id', 'nombre']),
            'proveedores' => Proveedor::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'medios_pago' => MedioPago::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    /**
     * Store a newly created gasto in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'categoria_id'     => ['required', 'exists:categoria_gastos,id'],
            'descripcion'      => ['required', 'string', 'max:500'],
            'monto'            => ['required', 'numeric', 'min:0.01'],
            'iva'              => ['nullable', 'numeric', 'min:0'],
            'fecha'            => ['required', 'date'],
            'proveedor_id'     => ['nullable', 'exists:proveedores,id'],
            'medio_pago_id'    => ['nullable', 'exists:medios_pago,id'],
            'numero_documento' => ['nullable', 'string', 'max:100'],
            'comprobante'      => ['nullable', 'string', 'max:255'],
        ]);

        $validated['user_id'] = auth()->id();
        $validated['iva']     = $validated['iva'] ?? 0;

        Gasto::create($validated);

        return redirect()->route('gastos.index')
            ->with('success', 'Gasto registrado exitosamente.');
    }

    /**
     * Show the form for editing the specified gasto.
     */
    public function edit(Gasto $gasto): Response
    {
        return Inertia::render('Gastos/Form', [
            'gasto'       => $gasto->load(['categoria', 'proveedor', 'medioPago']),
            'categorias'  => CategoriaGasto::orderBy('nombre')->get(['id', 'nombre']),
            'proveedores' => Proveedor::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'medios_pago' => MedioPago::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    /**
     * Update the specified gasto in storage.
     */
    public function update(Request $request, Gasto $gasto): RedirectResponse
    {
        $validated = $request->validate([
            'categoria_id'     => ['required', 'exists:categoria_gastos,id'],
            'descripcion'      => ['required', 'string', 'max:500'],
            'monto'            => ['required', 'numeric', 'min:0.01'],
            'iva'              => ['nullable', 'numeric', 'min:0'],
            'fecha'            => ['required', 'date'],
            'proveedor_id'     => ['nullable', 'exists:proveedores,id'],
            'medio_pago_id'    => ['nullable', 'exists:medios_pago,id'],
            'numero_documento' => ['nullable', 'string', 'max:100'],
            'comprobante'      => ['nullable', 'string', 'max:255'],
        ]);

        $validated['iva'] = $validated['iva'] ?? 0;

        $gasto->update($validated);

        return redirect()->route('gastos.index')
            ->with('success', 'Gasto actualizado exitosamente.');
    }

    /**
     * Remove the specified gasto from storage.
     */
    public function destroy(Gasto $gasto): RedirectResponse
    {
        $gasto->delete();

        return redirect()->route('gastos.index')
            ->with('success', 'Gasto eliminado exitosamente.');
    }
}
