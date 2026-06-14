<?php

namespace Database\Seeders;

use App\Models\MedioPago;
use Illuminate\Database\Seeder;

class MedioPagoSeeder extends Seeder
{
    public function run(): void
    {
        $medios = [
            ['nombre' => 'Efectivo',            'tipo' => 'efectivo'],
            ['nombre' => 'Tarjeta Débito',      'tipo' => 'tarjeta_debito'],
            ['nombre' => 'Tarjeta Crédito',     'tipo' => 'tarjeta_credito'],
            ['nombre' => 'Transferencia',        'tipo' => 'transferencia'],
            ['nombre' => 'Nequi',               'tipo' => 'nequi'],
            ['nombre' => 'Daviplata',           'tipo' => 'daviplata'],
            ['nombre' => 'Cheque',              'tipo' => 'cheque'],
            ['nombre' => 'Crédito',             'tipo' => 'credito'],
        ];

        foreach ($medios as $medio) {
            MedioPago::firstOrCreate(
                ['nombre' => $medio['nombre']],
                $medio + ['activo' => true]
            );
        }
    }
}
