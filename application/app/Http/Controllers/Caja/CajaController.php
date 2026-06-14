<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\Fondo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CajaController extends Controller
{
    public function index(): Response
    {
        $cajas = Caja::with([
            'turnoActivo.cajero.colaborador.user.persona',
        ])->orderBy('nombre')->get();

        $fondos = Fondo::with('medioPago')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Caja/Index', [
            'cajas'  => $cajas,
            'fondos' => $fondos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'    => ['required', 'string', 'max:100'],
            'ubicacion' => ['nullable', 'string', 'max:200'],
            'activo'    => ['boolean'],
        ]);

        $validated['activo'] = $validated['activo'] ?? true;

        Caja::create($validated);

        return redirect()->route('caja.index')
            ->with('success', 'Caja creada correctamente.');
    }

    public function update(Request $request, Caja $caja): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'    => ['required', 'string', 'max:100'],
            'ubicacion' => ['nullable', 'string', 'max:200'],
            'activo'    => ['boolean'],
        ]);

        $caja->update($validated);

        return redirect()->route('caja.index')
            ->with('success', 'Caja actualizada correctamente.');
    }

    public function destroy(Caja $caja): RedirectResponse
    {
        $caja->update(['activo' => false]);

        return redirect()->route('caja.index')
            ->with('success', 'Caja desactivada correctamente.');
    }
}
