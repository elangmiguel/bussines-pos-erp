<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\TurnoCaja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TurnoCajaController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TurnoCaja::with([
            'caja',
            'cajero.colaborador.user.persona',
        ]);

        if ($cajaId = $request->input('caja_id')) {
            $query->where('caja_id', $cajaId);
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        if ($desde = $request->input('fecha_desde')) {
            $query->whereDate('apertura', '>=', $desde);
        }

        if ($hasta = $request->input('fecha_hasta')) {
            $query->whereDate('apertura', '<=', $hasta);
        }

        $turnos = $query->orderByDesc('apertura')->paginate(20)->withQueryString()->toInertia();

        $cajas = Caja::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('Caja/Turno/Index', [
            'turnos'  => $turnos,
            'cajas'   => $cajas,
            'filtros' => [
                'caja_id'     => $request->input('caja_id', ''),
                'estado'      => $request->input('estado', ''),
                'fecha_desde' => $request->input('fecha_desde', ''),
                'fecha_hasta' => $request->input('fecha_hasta', ''),
            ],
        ]);
    }

    public function show(TurnoCaja $turno): Response
    {
        $turno->load([
            'caja',
            'cajero.colaborador.user.persona',
        ]);

        $facturas = $turno->facturas()
            ->with(['cliente.persona', 'pagoFactura.medioPago'])
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString()->toInertia();

        // Build summary: total sales and breakdown by medio_pago
        $totalVentas = $turno->facturas()->sum('total');

        $porMedioPago = $turno->facturas()
            ->join('pagos_factura', 'facturas.id', '=', 'pagos_factura.factura_id')
            ->join('medios_pago', 'pagos_factura.medio_pago_id', '=', 'medios_pago.id')
            ->selectRaw('medios_pago.nombre as medio_pago, SUM(pagos_factura.monto) as total')
            ->groupBy('medios_pago.nombre')
            ->get();

        return Inertia::render('Caja/Turno/Show', [
            'turno'    => $turno,
            'facturas' => $facturas,
            'resumen'  => [
                'total_ventas'   => $totalVentas,
                'por_medio_pago' => $porMedioPago,
            ],
        ]);
    }

    public function abrirForm(): Response
    {
        $cajas = Caja::where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'ubicacion']);

        return Inertia::render('Caja/Turno/Abrir', [
            'cajas' => $cajas,
        ]);
    }

    public function abrir(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'caja_id'       => ['required', 'exists:cajas,id'],
            'saldo_inicial' => ['required', 'numeric', 'min:0'],
        ]);

        // Check for an already open turn on this caja
        $turnoAbierto = TurnoCaja::where('caja_id', $validated['caja_id'])
            ->abierto()
            ->first();

        if ($turnoAbierto) {
            return back()->withErrors([
                'caja_id' => 'Esta caja ya tiene un turno abierto. Ciérrelo antes de abrir uno nuevo.',
            ]);
        }

        // Get the Cajero from the authenticated user
        $user       = auth()->user();
        $colaborador = $user->colaborador;
        $cajero      = $colaborador
            ? $colaborador->cajero()->where('activo', true)->first()
            : null;

        if (! $cajero) {
            return back()->withErrors([
                'caja_id' => 'Su usuario no tiene un cajero activo asociado. Contacte al administrador.',
            ]);
        }

        TurnoCaja::create([
            'caja_id'       => $validated['caja_id'],
            'cajero_id'     => $cajero->id,
            'saldo_inicial' => $validated['saldo_inicial'],
            'apertura'      => now(),
            'estado'        => 'abierto',
        ]);

        return redirect()->route('caja.index')
            ->with('success', 'Turno de caja abierto correctamente.');
    }

    public function cerrar(Request $request, TurnoCaja $turno): RedirectResponse
    {
        $validated = $request->validate([
            'saldo_final'   => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $turno->update([
            'saldo_final'   => $validated['saldo_final'],
            'observaciones' => $validated['observaciones'] ?? null,
            'cierre'        => now(),
            'estado'        => 'cerrado',
        ]);

        return redirect()->route('caja.turnos.show', $turno)
            ->with('success', 'Turno cerrado correctamente.');
    }
}
