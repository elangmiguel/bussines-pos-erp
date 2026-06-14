<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\Fondo;
use App\Models\MedioPago;
use Illuminate\Database\Seeder;

class CajaFondoSeeder extends Seeder
{
    public function run(): void
    {
        Caja::firstOrCreate(['nombre' => 'Caja 1'], [
            'ubicacion' => 'Entrada principal',
            'activo'    => true,
        ]);

        Caja::firstOrCreate(['nombre' => 'Caja 2'], [
            'ubicacion' => 'Fondo de tienda',
            'activo'    => true,
        ]);

        $efectivo   = MedioPago::where('tipo', 'efectivo')->first();
        $nequi      = MedioPago::where('tipo', 'nequi')->first();
        $transferencia = MedioPago::where('tipo', 'transferencia')->first();

        Fondo::firstOrCreate(['nombre' => 'Caja Fuerte Principal'], [
            'tipo'          => 'caja',
            'medio_pago_id' => $efectivo?->id,
            'saldo_actual'  => 500000,
            'activo'        => true,
        ]);

        Fondo::firstOrCreate(['nombre' => 'Fondo Nequi'], [
            'tipo'          => 'digital',
            'medio_pago_id' => $nequi?->id,
            'saldo_actual'  => 0,
            'activo'        => true,
        ]);

        Fondo::firstOrCreate(['nombre' => 'Cuenta Bancaria'], [
            'tipo'          => 'banco',
            'medio_pago_id' => $transferencia?->id,
            'saldo_actual'  => 0,
            'activo'        => true,
        ]);
    }
}
