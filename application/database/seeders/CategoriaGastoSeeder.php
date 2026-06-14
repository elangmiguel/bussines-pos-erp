<?php

namespace Database\Seeders;

use App\Models\CategoriaGasto;
use Illuminate\Database\Seeder;

class CategoriaGastoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Arriendo',            'descripcion' => 'Canon de arrendamiento del local'],
            ['nombre' => 'Servicios públicos',  'descripcion' => 'Agua, luz, gas, internet, teléfono'],
            ['nombre' => 'Nómina',              'descripcion' => 'Salarios y prestaciones sociales'],
            ['nombre' => 'Insumos de oficina',  'descripcion' => 'Papelería, elementos de aseo, etc.'],
            ['nombre' => 'Mantenimiento',       'descripcion' => 'Reparaciones y mantenimiento de equipos'],
            ['nombre' => 'Publicidad',          'descripcion' => 'Pauta publicitaria y marketing'],
            ['nombre' => 'Transporte',          'descripcion' => 'Fletes, mensajería y transporte'],
            ['nombre' => 'Impuestos',           'descripcion' => 'ICA, predial y otros impuestos locales'],
            ['nombre' => 'Seguridad',           'descripcion' => 'Vigilancia y seguridad'],
            ['nombre' => 'Otros',               'descripcion' => 'Gastos varios no clasificados'],
        ];

        foreach ($categorias as $categoria) {
            CategoriaGasto::firstOrCreate(
                ['nombre' => $categoria['nombre']],
                $categoria + ['activo' => true]
            );
        }
    }
}
