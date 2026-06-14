<?php

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clientes\ClienteRequest;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Persona;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClienteController extends Controller
{
    /**
     * Display a paginated listing of clients with filters.
     */
    public function index(Request $request): Response
    {
        $query = Cliente::with(['persona', 'empresa'])
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->whereHas('persona', function ($p) use ($search) {
                        $p->where('nombres', 'like', "%{$search}%")
                          ->orWhere('apellidos', 'like', "%{$search}%")
                          ->orWhere('numero_identificacion', 'like', "%{$search}%");
                    })->orWhereHas('empresa', function ($e) use ($search) {
                        $e->where('razon_social', 'like', "%{$search}%")
                          ->orWhere('nit', 'like', "%{$search}%");
                    });
                });
            })
            ->when($request->input('tipo'), fn ($q, $tipo) => $q->where('tipo', $tipo))
            ->when($request->input('tipo_cliente'), fn ($q, $tc) => $q->where('tipo_cliente', $tc))
            ->when($request->filled('activo'), fn ($q) => $q->where('activo', $request->boolean('activo')))
            ->when($request->filled('credito_activo'), fn ($q) => $q->where('credito_activo', $request->boolean('credito_activo')));

        $clientes = $query->latest()->paginate(20)->withQueryString()->toInertia();

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'filtros'  => $request->only(['search', 'tipo', 'tipo_cliente', 'activo', 'credito_activo']),
        ]);
    }

    /**
     * Show the form for creating a new client.
     */
    public function create(): Response
    {
        return Inertia::render('Clientes/Form', [
            'cliente'   => null,
            'errors'    => [],
            'lookups'   => $this->getLookups(),
        ]);
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(ClienteRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $cliente   = null;

        if ($validated['tipo'] === 'natural') {
            $persona = Persona::firstOrCreate(
                [
                    'tipo_identificacion'   => $validated['tipo_identificacion'],
                    'numero_identificacion' => $validated['numero_identificacion'],
                ],
                [
                    'nombres'   => $validated['nombres'],
                    'apellidos' => $validated['apellidos'] ?? null,
                    'email'     => $validated['email'] ?? null,
                    'telefono'  => $validated['telefono'] ?? null,
                    'celular'   => $validated['celular'] ?? null,
                    'direccion' => $validated['direccion'] ?? null,
                    'ciudad'    => $validated['ciudad'] ?? null,
                    'pais'      => 'Colombia',
                ]
            );

            $persona->update([
                'nombres'   => $validated['nombres'],
                'apellidos' => $validated['apellidos'] ?? null,
                'email'     => $validated['email'] ?? null,
                'telefono'  => $validated['telefono'] ?? null,
                'celular'   => $validated['celular'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'ciudad'    => $validated['ciudad'] ?? null,
            ]);

            $cliente = Cliente::create([
                'tipo'           => 'natural',
                'persona_id'     => $persona->id,
                'empresa_id'     => null,
                'tipo_cliente'   => $validated['tipo_cliente'],
                'credito_activo' => $validated['credito_activo'] ?? false,
                'limite_credito' => $validated['limite_credito'] ?? 0,
                'plazo_dias'     => $validated['plazo_dias'] ?? 0,
                'observaciones'  => $validated['observaciones'] ?? null,
                'activo'         => true,
            ]);
        } else {
            $empresa = Empresa::firstOrCreate(
                ['nit' => $validated['nit']],
                [
                    'razon_social'       => $validated['razon_social'],
                    'digito_verificacion' => $validated['digito_verificacion'],
                    'regimen_tributario' => $validated['regimen_tributario'],
                    'email'              => $validated['email'] ?? null,
                    'telefono'           => $validated['telefono'] ?? null,
                    'direccion'          => $validated['direccion'] ?? null,
                    'ciudad'             => $validated['ciudad'] ?? null,
                    'pais'               => 'Colombia',
                    'activo'             => true,
                ]
            );

            $empresa->update([
                'razon_social'       => $validated['razon_social'],
                'digito_verificacion' => $validated['digito_verificacion'],
                'regimen_tributario' => $validated['regimen_tributario'],
                'email'              => $validated['email'] ?? null,
                'telefono'           => $validated['telefono'] ?? null,
                'direccion'          => $validated['direccion'] ?? null,
                'ciudad'             => $validated['ciudad'] ?? null,
            ]);

            $cliente = Cliente::create([
                'tipo'           => 'juridico',
                'persona_id'     => null,
                'empresa_id'     => $empresa->id,
                'tipo_cliente'   => $validated['tipo_cliente'],
                'credito_activo' => $validated['credito_activo'] ?? false,
                'limite_credito' => $validated['limite_credito'] ?? 0,
                'plazo_dias'     => $validated['plazo_dias'] ?? 0,
                'observaciones'  => $validated['observaciones'] ?? null,
                'activo'         => true,
            ]);
        }

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Display the specified client with related data.
     */
    public function show(Cliente $cliente): Response
    {
        $cliente->load(['persona', 'empresa']);

        $facturas = $cliente->facturas()
            ->latest()
            ->limit(5)
            ->get();

        $cuentas = $cliente->cuentasPorCobrar()
            ->with('factura')
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->orderBy('fecha_vencimiento')
            ->get();

        $mediosPago = \App\Models\MedioPago::where('activo', true)->get(['id', 'nombre', 'tipo']);

        return Inertia::render('Clientes/Show', [
            'cliente'     => $cliente,
            'facturas'    => $facturas,
            'cuentas'     => $cuentas,
            'medios_pago' => $mediosPago,
        ]);
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Cliente $cliente): Response
    {
        $cliente->load(['persona', 'empresa']);

        return Inertia::render('Clientes/Form', [
            'cliente' => $cliente,
            'errors'  => [],
            'lookups' => $this->getLookups(),
        ]);
    }

    /**
     * Update the specified client in storage.
     */
    public function update(ClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $validated = $request->validated();

        if ($validated['tipo'] === 'natural' && $cliente->persona) {
            $cliente->persona->update([
                'tipo_identificacion'   => $validated['tipo_identificacion'],
                'numero_identificacion' => $validated['numero_identificacion'],
                'nombres'               => $validated['nombres'],
                'apellidos'             => $validated['apellidos'] ?? null,
                'email'                 => $validated['email'] ?? null,
                'telefono'              => $validated['telefono'] ?? null,
                'celular'               => $validated['celular'] ?? null,
                'direccion'             => $validated['direccion'] ?? null,
                'ciudad'                => $validated['ciudad'] ?? null,
            ]);
        } elseif ($validated['tipo'] === 'juridico' && $cliente->empresa) {
            $cliente->empresa->update([
                'razon_social'        => $validated['razon_social'],
                'nit'                 => $validated['nit'],
                'digito_verificacion' => $validated['digito_verificacion'],
                'regimen_tributario'  => $validated['regimen_tributario'],
                'email'               => $validated['email'] ?? null,
                'telefono'            => $validated['telefono'] ?? null,
                'direccion'           => $validated['direccion'] ?? null,
                'ciudad'              => $validated['ciudad'] ?? null,
            ]);
        }

        $cliente->update([
            'tipo_cliente'   => $validated['tipo_cliente'],
            'credito_activo' => $validated['credito_activo'] ?? false,
            'limite_credito' => $validated['limite_credito'] ?? 0,
            'plazo_dias'     => $validated['plazo_dias'] ?? 0,
            'observaciones'  => $validated['observaciones'] ?? null,
        ]);

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Soft-delete the specified client.
     */
    public function destroy(Cliente $cliente): RedirectResponse
    {
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }

    /**
     * Return shared lookup data for forms.
     */
    private function getLookups(): array
    {
        return [
            'tipos_identificacion' => ['CC', 'CE', 'TI', 'PAS', 'RC'],
            'tipos_cliente'        => ['regular', 'frecuente', 'corporativo'],
            'regimenes'            => ['responsable_iva', 'no_responsable_iva'],
        ];
    }
}
