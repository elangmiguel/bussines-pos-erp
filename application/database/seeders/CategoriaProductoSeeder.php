<?php

namespace Database\Seeders;

use App\Models\CategoriaProducto;
use Illuminate\Database\Seeder;

class CategoriaProductoSeeder extends Seeder
{
    public function run(): void
    {
        // Root categories
        $raices = [
            'General'          => 'Categoría general por defecto',
            'Alimentos'        => 'Alimentos y comestibles en general',
            'Bebidas'          => 'Bebidas alcohólicas y no alcohólicas',
            'Aseo y Limpieza'  => 'Productos de aseo del hogar',
            'Cuidado Personal' => 'Higiene y cuidado personal',
            'Mascotas'         => 'Alimentos y accesorios para mascotas',
            'Papelería'        => 'Útiles escolares y de oficina',
            'Servicios'        => 'Servicios prestados',
        ];

        $ids = [];
        foreach ($raices as $nombre => $desc) {
            $cat = CategoriaProducto::firstOrCreate(
                ['nombre' => $nombre, 'parent_id' => null],
                ['descripcion' => $desc, 'activo' => true]
            );
            $ids[$nombre] = $cat->id;
        }

        // Subcategories of Alimentos
        $subAlimentos = [
            'Lácteos y Huevos'      => 'Leche, queso, yogurt, mantequilla y huevos',
            'Carnes y Embutidos'    => 'Res, cerdo, pollo, embutidos y charcutería',
            'Panadería'             => 'Pan, tortas y repostería',
            'Frutas y Verduras'     => 'Frutas frescas, verduras y hortalizas',
            'Granos y Cereales'     => 'Arroz, frijoles, lentejas, pastas',
            'Conservas y Enlatados' => 'Atún, sardinas, salsas enlatadas',
            'Snacks y Confitería'   => 'Papas, galletas, dulces y chocolates',
            'Congelados'            => 'Alimentos congelados y refrigerados',
            'Condimentos y Salsas'  => 'Sal, azúcar, aceite, vinagre, salsas',
        ];

        foreach ($subAlimentos as $nombre => $desc) {
            CategoriaProducto::firstOrCreate(
                ['nombre' => $nombre, 'parent_id' => $ids['Alimentos']],
                ['descripcion' => $desc, 'activo' => true]
            );
        }

        // Subcategories of Bebidas
        $subBebidas = [
            'Gaseosas y Refrescos' => 'Bebidas carbonatadas y jugos',
            'Agua'                 => 'Agua mineral y en botella',
            'Jugos Naturales'      => 'Jugos de frutas frescos y en caja',
            'Cerveza'              => 'Cervezas nacionales e importadas',
            'Licores'              => 'Ron, aguardiente, whisky, vino',
            'Café y Té'            => 'Café molido, instantáneo, aromáticas',
        ];

        foreach ($subBebidas as $nombre => $desc) {
            CategoriaProducto::firstOrCreate(
                ['nombre' => $nombre, 'parent_id' => $ids['Bebidas']],
                ['descripcion' => $desc, 'activo' => true]
            );
        }
    }
}
