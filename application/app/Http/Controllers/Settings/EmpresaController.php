<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Empresa;
use App\Models\ResolucionDian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EmpresaController extends Controller
{
    public function show(): Response
    {
        $configuracion = Configuracion::with('empresa')->first();
        $resolucion    = ResolucionDian::where('activo', true)->latest()->first();

        return Inertia::render('settings/Empresa', [
            'configuracion' => $configuracion,
            'resolucion'    => $resolucion,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Empresa fields
            'razon_social'          => ['required', 'string', 'max:255'],
            'nit'                   => ['required', 'string', 'max:20'],
            'digito_verificacion'   => ['required', 'string', 'max:1'],
            'regimen_tributario'    => ['required', 'in:responsable_iva,no_responsable_iva'],
            'email'                 => ['nullable', 'email', 'max:255'],
            'telefono'              => ['nullable', 'string', 'max:20'],
            'direccion'             => ['nullable', 'string', 'max:255'],
            'ciudad'                => ['nullable', 'string', 'max:100'],
            'departamento'          => ['nullable', 'string', 'max:100'],
            // Configuracion fields
            'zona_horaria'          => ['nullable', 'string', 'max:50'],
            'dias_vencimiento_cred' => ['nullable', 'integer', 'min:1', 'max:365'],
            'prefijo_nota_credito'  => ['nullable', 'string', 'max:10'],
            'prefijo_nota_debito'   => ['nullable', 'string', 'max:10'],
            'logo'                  => ['nullable', 'image', 'max:2048'],
        ]);

        $configuracion = Configuracion::with('empresa')->firstOrFail();

        // Update empresa
        $configuracion->empresa->update([
            'razon_social'        => $validated['razon_social'],
            'nit'                 => $validated['nit'],
            'digito_verificacion' => $validated['digito_verificacion'],
            'regimen_tributario'  => $validated['regimen_tributario'],
            'email'               => $validated['email'] ?? null,
            'telefono'            => $validated['telefono'] ?? null,
            'direccion'           => $validated['direccion'] ?? null,
            'ciudad'              => $validated['ciudad'] ?? null,
            'departamento'        => $validated['departamento'] ?? null,
        ]);

        // Handle logo upload
        $logoPath = $configuracion->logo;
        if ($request->hasFile('logo')) {
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        // Update configuracion
        $configuracion->update([
            'logo'                  => $logoPath,
            'zona_horaria'          => $validated['zona_horaria'] ?? $configuracion->zona_horaria,
            'dias_vencimiento_cred' => $validated['dias_vencimiento_cred'] ?? $configuracion->dias_vencimiento_cred,
            'prefijo_nota_credito'  => $validated['prefijo_nota_credito'] ?? $configuracion->prefijo_nota_credito,
            'prefijo_nota_debito'   => $validated['prefijo_nota_debito'] ?? $configuracion->prefijo_nota_debito,
        ]);

        return redirect()->back()->with('flash', [
            'success' => 'Configuración de empresa actualizada correctamente.',
        ]);
    }
}
