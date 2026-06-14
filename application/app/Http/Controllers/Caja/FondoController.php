<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\Controller;
use App\Models\Fondo;
use App\Models\MedioPago;
use App\Services\FondoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FondoController extends Controller
{
    public function __construct(private readonly FondoService $fondoService)
    {
    }

    public function index(): Response
    {
        $fondos = Fondo::with([
            'medioPago',
            'movimientos' => fn ($q) => $q->orderByDesc('created_at')->limit(5),
            'movimientos.user',
        ])->orderBy('nombre')->get();

        $mediosPago = MedioPago::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('Caja/Fondos/Index', [
            'fondos'      => $fondos,
            'medios_pago' => $mediosPago,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'       => ['required', 'string', 'max:100'],
            'tipo'         => ['required', 'in:caja,digital,banco,reserva,otro'],
            'medio_pago_id' => ['nullable', 'exists:medios_pago,id'],
            'activo'       => ['boolean'],
        ]);

        $validated['saldo_actual'] = 0;
        $validated['activo'] = $validated['activo'] ?? true;

        Fondo::create($validated);

        return redirect()->route('caja.fondos.index')
            ->with('success', 'Fondo creado correctamente.');
    }

    public function update(Request $request, Fondo $fondo): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'        => ['required', 'string', 'max:100'],
            'tipo'          => ['required', 'in:caja,digital,banco,reserva,otro'],
            'medio_pago_id' => ['nullable', 'exists:medios_pago,id'],
            'activo'        => ['boolean'],
        ]);

        $fondo->update($validated);

        return redirect()->route('caja.fondos.index')
            ->with('success', 'Fondo actualizado correctamente.');
    }

    public function movimiento(Request $request, Fondo $fondo): RedirectResponse
    {
        $validated = $request->validate([
            'tipo'        => ['required', 'in:ingreso,egreso'],
            'monto'       => ['required', 'numeric', 'min:1'],
            'descripcion' => ['required', 'string', 'min:3', 'max:300'],
        ]);

        $userId = auth()->id();

        if ($validated['tipo'] === 'ingreso') {
            $this->fondoService->registrarIngreso(
                $fondo->id,
                (float) $validated['monto'],
                $validated['descripcion'],
                'manual',
                $fondo->id,
                $userId
            );
        } else {
            $this->fondoService->registrarEgreso(
                $fondo->id,
                (float) $validated['monto'],
                $validated['descripcion'],
                'manual',
                $fondo->id,
                $userId
            );
        }

        return back()->with('success', ucfirst($validated['tipo']) . ' registrado correctamente.');
    }

    public function transferencia(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fondo_origen_id'  => ['required', 'exists:fondos,id'],
            'fondo_destino_id' => ['required', 'exists:fondos,id', 'different:fondo_origen_id'],
            'monto'            => ['required', 'numeric', 'min:1'],
            'descripcion'      => ['required', 'string', 'min:3', 'max:300'],
        ]);

        $this->fondoService->transferir(
            (int) $validated['fondo_origen_id'],
            (int) $validated['fondo_destino_id'],
            (float) $validated['monto'],
            $validated['descripcion'],
            auth()->id()
        );

        return back()->with('success', 'Transferencia realizada correctamente.');
    }
}
