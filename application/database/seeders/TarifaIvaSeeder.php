<?php

namespace Database\Seeders;

use App\Models\TarifaIva;
use Illuminate\Database\Seeder;

class TarifaIvaSeeder extends Seeder
{
    public function run(): void
    {
        $tarifas = [
            ['nombre' => 'IVA 19%',     'tipo' => 'iva',      'porcentaje' => 19.00],
            ['nombre' => 'IVA 5%',      'tipo' => 'iva',      'porcentaje' => 5.00],
            ['nombre' => 'Excluido',    'tipo' => 'excluido', 'porcentaje' => 0.00],
            ['nombre' => 'Exento',      'tipo' => 'exento',   'porcentaje' => 0.00],
            ['nombre' => 'INC 8%',      'tipo' => 'inc',      'porcentaje' => 8.00],
            ['nombre' => 'INC 16%',     'tipo' => 'inc',      'porcentaje' => 16.00],
        ];

        foreach ($tarifas as $tarifa) {
            TarifaIva::firstOrCreate(
                ['nombre' => $tarifa['nombre']],
                $tarifa + ['activo' => true]
            );
        }
    }
}
