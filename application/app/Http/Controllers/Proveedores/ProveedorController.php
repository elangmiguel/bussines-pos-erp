<?php

namespace App\Http\Controllers\Proveedores;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedores\ProveedorRequest;
use App\Models\Empresa;
use App\Models\Persona;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProveedorController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Proveedor::with(['persona', 'empresa'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('persona', function ($p) use ($search) {
                    $p->where('nombres', 'like', "%{$search}%")
                      ->orWhere('apellidos', 'like', "%{$search}%")
                      ->orWhere('numero_identificacion', 'like', "%{$search}%");
                })->orWhereHas('empresa', function ($e) use ($search) {
                    $e->where('razon_social', 'like', "%{$search}%")
                      ->orWhere('nit', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->input('activo') === '1');
        }

        $proveedores = $query->paginate(20)->withQueryString()->toInertia();

        return Inertia::render('Proveedores/Index', [
            'proveedores' => $proveedores,
            'filtros'     => $request->only('search', 'tipo', 'activo'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Proveedores/Form', [
            'tiposIdentificacion' => $this->tiposIdentificacion(),
            'regimenesTributarios' => $this->regimenesTributarios(),
        ]);
    }

    public function store(ProveedorRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($data['tipo'] === 'natural') {
            $persona = Persona::firstOrCreate(
                ['numero_identificacion' => $data['numero_identificacion']],
                [
                    'tipo_identificacion'  => $data['tipo_identificacion'],
                    'nombres'              => $data['nombres'],
                    'apellidos'            => $data['apellidos'],
                    'email'                => $data['email'] ?? null,
                    'telefono'             => $data['telefono'] ?? null,
                    'celular'              => $data['celular'] ?? null,
                    'direccion'            => $data['direccion'] ?? null,
                    'ciudad'               => $data['ciudad'] ?? null,
                ]
            );

            $proveedor = Proveedor::create([
                'tipo'             => 'natural',
                'persona_id'       => $persona->id,
                'empresa_id'       => null,
                'condiciones_pago' => $data['condiciones_pago'] ?? null,
                'plazo_dias'       => $data['plazo_dias'] ?? 0,
                'activo'           => true,
            ]);
        } else {
            $empresa = Empresa::firstOrCreate(
                ['nit' => $data['nit']],
                [
                    'razon_social'        => $data['razon_social'],
                    'digito_verificacion' => $data['digito_verificacion'],
                    'regimen_tributario'  => $data['regimen_tributario'],
                    'email'               => $data['email'] ?? null,
                    'telefono'            => $data['telefono'] ?? null,
                    'direccion'           => $data['direccion'] ?? null,
                    'ciudad'              => $data['ciudad'] ?? null,
                ]
            );

            $proveedor = Proveedor::create([
                'tipo'             => 'juridico',
                'persona_id'       => null,
                'empresa_id'       => $empresa->id,
                'condiciones_pago' => $data['condiciones_pago'] ?? null,
                'plazo_dias'       => $data['plazo_dias'] ?? 0,
                'activo'           => true,
            ]);
        }

        return redirect()->route('proveedores.show', $proveedor)
            ->with('success', 'Proveedor creado exitosamente.');
    }

    public function show(Proveedor $proveedor): Response
    {
        $proveedor->load([
            'persona',
            'empresa',
            'productos' => fn($q) => $q->withPivot('precio_compra', 'tiempo_entrega', 'es_principal'),
        ]);

        $ordenes = $proveedor->ordenesCompra()
            ->withCount('detalles')
            ->latest('fecha')
            ->limit(5)
            ->get();

        return Inertia::render('Proveedores/Show', [
            'proveedor' => $proveedor,
            'ordenes'   => $ordenes,
            'productos' => $proveedor->productos,
        ]);
    }

    public function edit(Proveedor $proveedor): Response
    {
        $proveedor->load(['persona', 'empresa']);

        return Inertia::render('Proveedores/Form', [
            'proveedor'            => $proveedor,
            'tiposIdentificacion'  => $this->tiposIdentificacion(),
            'regimenesTributarios' => $this->regimenesTributarios(),
        ]);
    }

    public function update(ProveedorRequest $request, Proveedor $proveedor): RedirectResponse
    {
        $data = $request->validated();

        if ($proveedor->tipo === 'natural' && $proveedor->persona) {
            $proveedor->persona->update([
                'tipo_identificacion'  => $data['tipo_identificacion'] ?? $proveedor->persona->tipo_identificacion,
                'numero_identificacion' => $data['numero_identificacion'] ?? $proveedor->persona->numero_identificacion,
                'nombres'              => $data['nombres'],
                'apellidos'            => $data['apellidos'],
                'email'                => $data['email'] ?? null,
                'telefono'             => $data['telefono'] ?? null,
                'celular'              => $data['celular'] ?? null,
                'direccion'            => $data['direccion'] ?? null,
                'ciudad'               => $data['ciudad'] ?? null,
            ]);
        } elseif ($proveedor->tipo === 'juridico' && $proveedor->empresa) {
            $proveedor->empresa->update([
                'razon_social'        => $data['razon_social'],
                'nit'                 => $data['nit'],
                'digito_verificacion' => $data['digito_verificacion'],
                'regimen_tributario'  => $data['regimen_tributario'],
                'email'               => $data['email'] ?? null,
                'telefono'            => $data['telefono'] ?? null,
                'direccion'           => $data['direccion'] ?? null,
                'ciudad'              => $data['ciudad'] ?? null,
            ]);
        }

        $proveedor->update([
            'condiciones_pago' => $data['condiciones_pago'] ?? null,
            'plazo_dias'       => $data['plazo_dias'] ?? 0,
        ]);

        return redirect()->route('proveedores.show', $proveedor)
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        $proveedor->delete();

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado exitosamente.');
    }

    private function tiposIdentificacion(): array
    {
        return [
            ['value' => 'CC',  'label' => 'Cédula de Ciudadanía'],
            ['value' => 'CE',  'label' => 'Cédula de Extranjería'],
            ['value' => 'PP',  'label' => 'Pasaporte'],
            ['value' => 'NIT', 'label' => 'NIT'],
        ];
    }

    private function regimenesTributarios(): array
    {
        return [
            ['value' => 'responsable_iva',    'label' => 'Responsable de IVA'],
            ['value' => 'no_responsable_iva', 'label' => 'No responsable de IVA'],
        ];
    }
}
