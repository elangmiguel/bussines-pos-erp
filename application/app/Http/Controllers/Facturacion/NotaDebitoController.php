<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Factura;
use App\Models\NotaDebito;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotaDebitoController extends Controller
{
    public function create(Factura $factura): Response
    {
        $factura->load([
            'cliente.persona',
            'cliente.empresa',
        ]);

        return Inertia::render('Facturacion/NotaDebito/Form', [
            'factura' => $factura,
        ]);
    }

    public function store(Request $request, Factura $factura): RedirectResponse
    {
        $validated = $request->validate([
            'motivo'   => ['required', 'string', 'min:5'],
            'subtotal' => ['required', 'numeric', 'min:1'],
            'iva'      => ['nullable', 'numeric', 'min:0'],
        ]);

        $configuracion = Configuracion::first();
        $prefijo       = $configuracion?->prefijo_nota_debito ?? 'ND';
        $numero        = NotaDebito::count() + 1;
        $numeroCompleto = $prefijo . str_pad((string) $numero, 5, '0', STR_PAD_LEFT);

        $subtotal = (float) $validated['subtotal'];
        $iva      = (float) ($validated['iva'] ?? 0);
        $total    = $subtotal + $iva;

        NotaDebito::create([
            'numero'          => $numero,
            'numero_completo' => $numeroCompleto,
            'factura_id'      => $factura->id,
            'user_id'         => auth()->id(),
            'fecha'           => now(),
            'motivo'          => $validated['motivo'],
            'subtotal'        => $subtotal,
            'iva'             => $iva,
            'total'           => $total,
            'estado'          => 'emitida',
            'estado_dian'     => 'pendiente',
        ]);

        return redirect()
            ->route('facturacion.show', $factura)
            ->with('success', 'Nota débito emitida correctamente.');
    }
}
