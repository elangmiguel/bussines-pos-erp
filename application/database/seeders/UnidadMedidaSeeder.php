<?php

namespace Database\Seeders;

use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;

class UnidadMedidaSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            ['nombre' => 'Unidad',          'abreviatura' => 'UN'],
            ['nombre' => 'Kilogramo',        'abreviatura' => 'KG'],
            ['nombre' => 'Gramo',            'abreviatura' => 'G'],
            ['nombre' => 'Litro',            'abreviatura' => 'L'],
            ['nombre' => 'Mililitro',        'abreviatura' => 'ML'],
            ['nombre' => 'Metro',            'abreviatura' => 'M'],
            ['nombre' => 'Metro cuadrado',   'abreviatura' => 'M2'],
            ['nombre' => 'Centímetro',       'abreviatura' => 'CM'],
            ['nombre' => 'Caja',             'abreviatura' => 'CJ'],
            ['nombre' => 'Bolsa',            'abreviatura' => 'BL'],
            ['nombre' => 'Docena',           'abreviatura' => 'DOC'],
            ['nombre' => 'Par',              'abreviatura' => 'PAR'],
            ['nombre' => 'Rollo',            'abreviatura' => 'RLL'],
            ['nombre' => 'Servicio',         'abreviatura' => 'SRV'],
        ];

        foreach ($unidades as $unidad) {
            UnidadMedida::firstOrCreate(['abreviatura' => $unidad['abreviatura']], $unidad);
        }
    }
}
