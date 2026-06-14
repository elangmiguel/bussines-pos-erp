<?php

use App\Actions\Facturas\CrearFactura;
use App\Models\Caja;
use App\Models\CategoriaProducto;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\Factura;
use App\Models\Fondo;
use App\Models\MedioPago;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\ResolucionDian;
use App\Models\TarifaIva;
use App\Models\TurnoCaja;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Models\Cajero;
use App\Models\Colaborador;

// ---------------------------------------------------------------------------
// Shared setup helpers
// ---------------------------------------------------------------------------

function crearContextoFactura(): array
{
    $user = User::factory()->create();

    $tarifaIva19 = TarifaIva::create([
        'nombre'     => 'IVA 19%',
        'tipo'       => 'iva',
        'porcentaje' => 19,
        'activo'     => true,
    ]);

    TarifaIva::create([
        'nombre'     => 'Excluido',
        'tipo'       => 'excluido',
        'porcentaje' => 0,
        'activo'     => true,
    ]);

    $unidad = UnidadMedida::create(['nombre' => 'Unidad', 'abreviatura' => 'UND']);

    $categoria = CategoriaProducto::create(['nombre' => 'General', 'activo' => true]);

    $producto = Producto::create([
        'codigo'           => 'PROD-001',
        'nombre'           => 'Producto Test',
        'categoria_id'     => $categoria->id,
        'unidad_medida_id' => $unidad->id,
        'tarifa_iva_id'    => $tarifaIva19->id,
        'precio_compra'    => 5000,
        'precio_venta'     => 10000,
        'stock_actual'     => 100,
        'stock_minimo'     => 0,
        'activo'           => true,
    ]);

    $resolucion = ResolucionDian::create([
        'numero_resolucion' => '18764000001',
        'fecha_resolucion'  => now()->subYear()->toDateString(),
        'fecha_inicio'      => now()->subDay()->toDateString(),
        'fecha_fin'         => now()->addYear()->toDateString(),
        'prefijo'           => 'FE',
        'rango_desde'       => 1,
        'rango_hasta'       => 1000,
        'numero_actual'     => 0,
        'clave_tecnica'     => 'abc123',
        'activo'            => true,
    ]);

    $caja = Caja::create(['nombre' => 'Caja Principal', 'activo' => true]);

    $persona = Persona::create([
        'tipo_identificacion'   => 'CC',
        'numero_identificacion' => '12345678',
        'nombres'               => 'Juan',
        'apellidos'             => 'Perez',
    ]);

    $colaborador = Colaborador::create(['user_id' => $user->id]);

    $cajero = Cajero::create([
        'colaborador_id' => $colaborador->id,
        'codigo'         => 'CAJ-001',
        'activo'         => true,
    ]);

    $turno = TurnoCaja::create([
        'caja_id'      => $caja->id,
        'cajero_id'    => $cajero->id,
        'saldo_inicial' => 0,
        'apertura'     => now(),
        'estado'       => 'abierto',
    ]);

    $cliente = Cliente::create([
        'tipo'       => 'natural',
        'persona_id' => $persona->id,
        'activo'     => true,
    ]);

    $medioPago = MedioPago::create([
        'nombre' => 'Efectivo',
        'tipo'   => 'efectivo',
        'activo' => true,
    ]);

    $fondo = Fondo::create([
        'nombre'        => 'Caja Efectivo',
        'tipo'          => 'caja',
        'medio_pago_id' => $medioPago->id,
        'saldo_actual'  => 0,
        'activo'        => true,
    ]);

    return compact(
        'user', 'tarifaIva19', 'unidad', 'categoria', 'producto',
        'resolucion', 'caja', 'turno', 'cliente', 'medioPago', 'fondo'
    );
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

it('crea factura de contado con IVA 19% correctamente', function () {
    $ctx = crearContextoFactura();

    $data = [
        'turno_caja_id'    => $ctx['turno']->id,
        'cliente_id'       => $ctx['cliente']->id,
        'tipo_pago'        => 'contado',
        'descuento_global' => 0,
        'items'            => [
            [
                'producto_id'     => $ctx['producto']->id,
                'cantidad'        => 2,
                'precio_unitario' => 10000,
                'descuento_pct'   => 0,
            ],
        ],
        'pagos' => [
            [
                'medio_pago_id' => $ctx['medioPago']->id,
                'monto'         => 23800,
            ],
        ],
    ];

    $factura = app(CrearFactura::class)->execute($data, $ctx['user']->id);

    expect(Factura::find($factura->id))->not->toBeNull();
    expect((float) $factura->subtotal)->toBe(20000.0);
    expect((float) $factura->iva_19)->toBe(3800.0);
    expect((float) $factura->total)->toBe(23800.0);
    expect($factura->detalles()->count())->toBe(1);
    expect($factura->pagos()->count())->toBe(1);

    $productoActualizado = Producto::find($ctx['producto']->id);
    expect((float) $productoActualizado->stock_actual)->toBe(98.0);

    $fondoActualizado = Fondo::find($ctx['fondo']->id);
    expect((float) $fondoActualizado->saldo_actual)->toBe(23800.0);

    expect($factura->cufe)->not->toBeNull()->not->toBeEmpty();
});

it('crea factura de crédito y genera cuenta por cobrar', function () {
    $ctx = crearContextoFactura();

    $data = [
        'turno_caja_id'    => $ctx['turno']->id,
        'cliente_id'       => $ctx['cliente']->id,
        'tipo_pago'        => 'credito',
        'plazo_dias'       => 30,
        'descuento_global' => 0,
        'items'            => [
            [
                'producto_id'     => $ctx['producto']->id,
                'cantidad'        => 1,
                'precio_unitario' => 10000,
                'descuento_pct'   => 0,
            ],
        ],
        'pagos' => [],
    ];

    $factura = app(CrearFactura::class)->execute($data, $ctx['user']->id);

    $cuentaPorCobrar = CuentaPorCobrar::where('factura_id', $factura->id)->first();

    expect($cuentaPorCobrar)->not->toBeNull();
    expect((float) $cuentaPorCobrar->monto_total)->toBe((float) $factura->total);
    expect($cuentaPorCobrar->estado)->toBe('pendiente');

    $expectedVencimiento = now()->addDays(30)->toDateString();
    expect($factura->fecha_vencimiento->toDateString())->toBe($expectedVencimiento);
});

it('incrementa numero_actual de resolución atómicamente', function () {
    $ctx = crearContextoFactura();

    $ctx['resolucion']->update(['numero_actual' => 5]);

    $itemBase = [
        'producto_id'     => $ctx['producto']->id,
        'cantidad'        => 1,
        'precio_unitario' => 10000,
        'descuento_pct'   => 0,
    ];

    $dataBase = [
        'turno_caja_id'    => $ctx['turno']->id,
        'cliente_id'       => $ctx['cliente']->id,
        'tipo_pago'        => 'contado',
        'descuento_global' => 0,
        'items'            => [$itemBase],
        'pagos'            => [
            ['medio_pago_id' => $ctx['medioPago']->id, 'monto' => 11900],
        ],
    ];

    $factura1 = app(CrearFactura::class)->execute($dataBase, $ctx['user']->id);
    $factura2 = app(CrearFactura::class)->execute($dataBase, $ctx['user']->id);

    expect(ResolucionDian::first()->numero_actual)->toBe(7);
    expect($factura1->numero)->toBe(6);
    expect($factura2->numero)->toBe(7);
});

it('lanza excepción si no hay resolución activa', function () {
    $ctx = crearContextoFactura();

    $ctx['resolucion']->update(['activo' => false]);

    $data = [
        'turno_caja_id'    => $ctx['turno']->id,
        'cliente_id'       => $ctx['cliente']->id,
        'tipo_pago'        => 'contado',
        'descuento_global' => 0,
        'items'            => [
            [
                'producto_id'     => $ctx['producto']->id,
                'cantidad'        => 1,
                'precio_unitario' => 10000,
                'descuento_pct'   => 0,
            ],
        ],
        'pagos' => [
            ['medio_pago_id' => $ctx['medioPago']->id, 'monto' => 11900],
        ],
    ];

    expect(fn () => app(CrearFactura::class)->execute($data, $ctx['user']->id))
        ->toThrow(\Exception::class);
});

it('lanza excepción si resolución está vencida', function () {
    $ctx = crearContextoFactura();

    $ctx['resolucion']->update([
        'fecha_fin' => now()->subDay()->toDateString(),
    ]);

    $data = [
        'turno_caja_id'    => $ctx['turno']->id,
        'cliente_id'       => $ctx['cliente']->id,
        'tipo_pago'        => 'contado',
        'descuento_global' => 0,
        'items'            => [
            [
                'producto_id'     => $ctx['producto']->id,
                'cantidad'        => 1,
                'precio_unitario' => 10000,
                'descuento_pct'   => 0,
            ],
        ],
        'pagos' => [
            ['medio_pago_id' => $ctx['medioPago']->id, 'monto' => 11900],
        ],
    ];

    expect(fn () => app(CrearFactura::class)->execute($data, $ctx['user']->id))
        ->toThrow(\Exception::class);
});

it('revierte stock si la transacción falla', function () {
    $ctx = crearContextoFactura();

    $stockAntes = (float) $ctx['producto']->stock_actual;

    $data = [
        'turno_caja_id'    => $ctx['turno']->id,
        'cliente_id'       => $ctx['cliente']->id,
        'tipo_pago'        => 'contado',
        'descuento_global' => 0,
        'items'            => [
            [
                'producto_id'     => 99999, // does not exist
                'cantidad'        => 1,
                'precio_unitario' => 10000,
                'descuento_pct'   => 0,
            ],
        ],
        'pagos' => [
            ['medio_pago_id' => $ctx['medioPago']->id, 'monto' => 11900],
        ],
    ];

    expect(fn () => app(CrearFactura::class)->execute($data, $ctx['user']->id))
        ->toThrow(\Exception::class);

    $productoActualizado = Producto::find($ctx['producto']->id);
    expect((float) $productoActualizado->stock_actual)->toBe($stockAntes);
});
