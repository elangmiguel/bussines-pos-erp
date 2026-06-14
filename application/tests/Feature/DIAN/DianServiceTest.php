<?php

use App\Models\Caja;
use App\Models\CategoriaProducto;
use App\Models\Cajero;
use App\Models\Cliente;
use App\Models\Colaborador;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\MedioPago;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\ResolucionDian;
use App\Models\TarifaIva;
use App\Models\TurnoCaja;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Services\DianService;

// ---------------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------------

function crearFacturaDian(): Factura
{
    $user = User::factory()->create();

    $tarifa = TarifaIva::create([
        'nombre'     => 'IVA 19%',
        'tipo'       => 'iva',
        'porcentaje' => 19,
        'activo'     => true,
    ]);

    $unidad   = UnidadMedida::create(['nombre' => 'Unidad', 'abreviatura' => 'UND']);
    $categoria = CategoriaProducto::create(['nombre' => 'General', 'activo' => true]);

    $producto = Producto::create([
        'codigo'           => 'DIAN-' . uniqid(),
        'nombre'           => 'Producto DIAN',
        'categoria_id'     => $categoria->id,
        'unidad_medida_id' => $unidad->id,
        'tarifa_iva_id'    => $tarifa->id,
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
        'clave_tecnica'     => 'abc123testclave',
        'activo'            => true,
    ]);

    $caja = Caja::create(['nombre' => 'Caja DIAN', 'activo' => true]);

    $persona = Persona::create([
        'tipo_identificacion'   => 'CC',
        'numero_identificacion' => '87654321',
        'nombres'               => 'Maria',
        'apellidos'             => 'Lopez',
    ]);

    $colaborador = Colaborador::create(['user_id' => $user->id]);

    $cajero = Cajero::create([
        'colaborador_id' => $colaborador->id,
        'codigo'         => 'CAJ-DIAN',
        'activo'         => true,
    ]);

    $turno = TurnoCaja::create([
        'caja_id'       => $caja->id,
        'cajero_id'     => $cajero->id,
        'saldo_inicial' => 0,
        'apertura'      => now(),
        'estado'        => 'abierto',
    ]);

    $cliente = Cliente::create([
        'tipo'       => 'natural',
        'persona_id' => $persona->id,
        'activo'     => true,
    ]);

    $factura = Factura::create([
        'numero'           => 1,
        'prefijo'          => 'FE',
        'numero_completo'  => 'FE1',
        'resolucion_id'    => $resolucion->id,
        'turno_caja_id'    => $turno->id,
        'cliente_id'       => $cliente->id,
        'user_id'          => $user->id,
        'fecha'            => now(),
        'tipo_pago'        => 'contado',
        'subtotal'         => 10000,
        'descuento_global' => 0,
        'base_iva_0'       => 0,
        'base_iva_5'       => 0,
        'base_iva_19'      => 10000,
        'iva_5'            => 0,
        'iva_19'           => 1900,
        'inc'              => 0,
        'total'            => 11900,
        'estado'           => 'emitida',
        'estado_dian'      => 'pendiente',
        'cufe'             => null,
    ]);

    $factura->detalles()->create([
        'producto_id'     => $producto->id,
        'descripcion'     => $producto->nombre,
        'cantidad'        => 1,
        'precio_unitario' => 10000,
        'descuento_pct'   => 0,
        'tarifa_iva_id'   => $tarifa->id,
        'subtotal'        => 10000,
        'iva'             => 1900,
        'total'           => 11900,
    ]);

    return $factura->fresh();
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

it('genera CUFE con formato SHA-384', function () {
    $factura = crearFacturaDian();

    $cufe = app(DianService::class)->generarCufe($factura);

    expect($cufe)->toBeString()->not->toBeEmpty();
    // SHA-384 produces 48 bytes = 96 lowercase hex characters
    expect(strlen($cufe))->toBe(96);
    expect(ctype_xdigit($cufe))->toBeTrue();
});

it('genera QR data con URL DIAN', function () {
    $factura = crearFacturaDian();

    // generarQrData uses $factura->cufe — set a known cufe first
    $cufe = hash('sha384', 'test-cufe-input');
    $factura->update(['cufe' => $cufe]);
    $factura = $factura->fresh();

    $qrData = app(DianService::class)->generarQrData($factura);

    expect($qrData)->toBeString()->not->toBeEmpty();
    expect($qrData)->toContain('documentkey');
    expect($qrData)->toContain($cufe);
});

it('genera XML UBL 2.1 válido', function () {
    $factura = crearFacturaDian();

    $cufe = hash('sha384', 'test-cufe-xml');
    $factura->update(['cufe' => $cufe]);
    $factura = $factura->fresh();

    $xml = app(DianService::class)->generarXml($factura);

    expect($xml)->toBeString()->not->toBeEmpty();
    expect($xml)->toContain('<?xml');
    expect($xml)->toContain('Invoice');
    expect($xml)->toContain($factura->numero_completo);
});
