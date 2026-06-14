<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ResolucionDian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResolucionDianController extends Controller
{
    public function index(): Response
    {
        $resoluciones = ResolucionDian::orderByDesc('created_at')->get();

        return Inertia::render('settings/DIAN/Index', [
            'resoluciones' => $resoluciones,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'numero_resolucion' => ['required', 'string', 'max:50'],
            'fecha_resolucion'  => ['required', 'date'],
            'fecha_inicio'      => ['required', 'date'],
            'fecha_fin'         => ['required', 'date', 'after:fecha_inicio'],
            'prefijo'           => ['nullable', 'string', 'max:10'],
            'rango_desde'       => ['required', 'integer', 'min:1'],
            'rango_hasta'       => ['required', 'integer', 'gt:rango_desde'],
            'clave_tecnica'     => ['nullable', 'string', 'max:255'],
        ]);

        // Deactivate all existing resolutions before creating the new active one
        ResolucionDian::where('activo', true)->update(['activo' => false]);

        ResolucionDian::create([
            ...$validated,
            'numero_actual' => $validated['rango_desde'] - 1,
            'activo'        => true,
        ]);

        return redirect()->back()
            ->with('flash', ['success' => 'Resolución DIAN registrada y activada correctamente.']);
    }

    public function activar(ResolucionDian $resolucion): RedirectResponse
    {
        ResolucionDian::where('activo', true)->update(['activo' => false]);
        $resolucion->update(['activo' => true]);

        return redirect()->back()
            ->with('flash', ['success' => "Resolución {$resolucion->numero_resolucion} activada correctamente."]);
    }
}
