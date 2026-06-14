<?php

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clientes\AbonoRequest;
use App\Models\AbonoCartera;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CarteraController extends Controller
{
    /**
     * Display all pending/partial accounts receivable with stats.
     */
    public function index(): Response
    {
        $cuentas = CuentaPorCobrar::with([
            'cliente.persona',
            'cliente.empresa',
            'factura',
        ])
            ->whereIn('estado', ['pendiente', 'parcial', 'vencida'])
            ->orderBy('fecha_vencimiento')
            ->paginate(25)
            ->withQueryString()
            ->toInertia();

        $allPending = CuentaPorCobrar::whereIn('estado', ['pendiente', 'parcial', 'vencida'])->get();

        $today        = Carbon::today();
        $totalCartera = $allPending->sum(fn ($c) => $c->monto_total - $c->monto_pagado);
        $vencida      = $allPending
            ->filter(fn ($c) => Carbon::parse($c->fecha_vencimiento)->lt($today))
            ->sum(fn ($c) => $c->monto_total - $c->monto_pagado);
        $porVencer    = $totalCartera - $vencida;

        return Inertia::render('Cartera/Index', [
            'cuentas' => $cuentas,
            'stats'   => [
                'total_cartera' => $totalCartera,
                'vencida'       => $vencida,
                'por_vencer'    => $porVencer,
            ],
        ]);
    }

    /**
     * Display a specific client's accounts receivable with their payments.
     */
    public function show(Cliente $cliente): Response
    {
        $cliente->load(['persona', 'empresa']);

        $cuentas = $cliente->cuentasPorCobrar()
            ->with(['factura', 'abonos.medioPago', 'abonos.user'])
            ->orderBy('fecha_vencimiento')
            ->get();

        return Inertia::render('Cartera/Show', [
            'cliente' => $cliente,
            'cuentas' => $cuentas,
        ]);
    }

    /**
     * Register a payment (abono) against an account receivable.
     */
    public function abonar(AbonoRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $cuenta = CuentaPorCobrar::findOrFail($validated['cuenta_cobrar_id']);

        $saldo = $cuenta->monto_total - $cuenta->monto_pagado;

        if ($validated['monto'] > $saldo) {
            return back()->withErrors([
                'monto' => "El monto del abono ({$validated['monto']}) supera el saldo pendiente ({$saldo}).",
            ])->withInput();
        }

        AbonoCartera::create([
            'cuenta_cobrar_id' => $cuenta->id,
            'medio_pago_id'    => $validated['medio_pago_id'],
            'monto'            => $validated['monto'],
            'fecha'            => Carbon::now(),
            'user_id'          => auth()->id(),
            'observaciones'    => $validated['observaciones'] ?? null,
        ]);

        $nuevoPagado = $cuenta->monto_pagado + $validated['monto'];
        $cuenta->monto_pagado = $nuevoPagado;
        $cuenta->estado = $nuevoPagado >= $cuenta->monto_total ? 'pagada' : 'parcial';
        $cuenta->save();

        return back()->with('success', 'Abono registrado exitosamente.');
    }

    /**
     * Return cartera grouped by aging buckets (días de vencimiento).
     */
    public function antiguedad(): Response
    {
        $cuentas = CuentaPorCobrar::with(['cliente.persona', 'cliente.empresa'])
            ->whereIn('estado', ['pendiente', 'parcial', 'vencida'])
            ->get();

        $today = Carbon::today();

        $buckets = [
            '0_30'  => ['label' => '0 – 30 días',  'cuentas' => [], 'total' => 0],
            '31_60' => ['label' => '31 – 60 días', 'cuentas' => [], 'total' => 0],
            '61_90' => ['label' => '61 – 90 días', 'cuentas' => [], 'total' => 0],
            'mas90' => ['label' => 'Más de 90 días', 'cuentas' => [], 'total' => 0],
        ];

        foreach ($cuentas as $cuenta) {
            $vencimiento = Carbon::parse($cuenta->fecha_vencimiento);
            $diasVencida = max(0, $today->diffInDays($vencimiento, false) * -1);
            $saldo       = $cuenta->monto_total - $cuenta->monto_pagado;

            $data = [
                'id'               => $cuenta->id,
                'cliente'          => $cuenta->cliente?->nombre ?? '—',
                'factura_id'       => $cuenta->factura_id,
                'saldo'            => $saldo,
                'fecha_vencimiento' => $cuenta->fecha_vencimiento,
                'dias_vencida'     => $diasVencida,
                'estado'           => $cuenta->estado,
            ];

            if ($diasVencida <= 30) {
                $buckets['0_30']['cuentas'][] = $data;
                $buckets['0_30']['total'] += $saldo;
            } elseif ($diasVencida <= 60) {
                $buckets['31_60']['cuentas'][] = $data;
                $buckets['31_60']['total'] += $saldo;
            } elseif ($diasVencida <= 90) {
                $buckets['61_90']['cuentas'][] = $data;
                $buckets['61_90']['total'] += $saldo;
            } else {
                $buckets['mas90']['cuentas'][] = $data;
                $buckets['mas90']['total'] += $saldo;
            }
        }

        return Inertia::render('Cartera/Antiguedad', [
            'buckets'        => $buckets,
            'total_general'  => array_sum(array_column($buckets, 'total')),
        ]);
    }
}
