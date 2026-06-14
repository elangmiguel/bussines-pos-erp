<?php

namespace Database\Seeders;

use App\Models\ResolucionDian;
use Illuminate\Database\Seeder;

class ResolucionDianSeeder extends Seeder
{
    public function run(): void
    {
        ResolucionDian::firstOrCreate(
            ['numero_resolucion' => '18764046493442'],
            [
                'fecha_resolucion' => '2024-01-15',
                'fecha_inicio'     => '2024-01-15',
                'fecha_fin'        => '2028-01-15',
                'prefijo'          => 'FE',
                'rango_desde'      => 1,
                'rango_hasta'      => 100000,
                'numero_actual'    => 0,
                'clave_tecnica'    => 'fc8eac422eba16e22ffd8c6f94b3f40a6e38162c',
                'activo'           => true,
            ]
        );
    }
}
