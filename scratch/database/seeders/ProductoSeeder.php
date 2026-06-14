<?php
/**
 * Seeder para poblar la tabla 'producto' con datos iniciales.
 *
 * Inserta registros de productos con nombre, precio, stock, categoría
 * y código de barras.
 *
 * PHP version 8.1
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Clase Seeder para insertar datos iniciales en la tabla 'producto'.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class ProductoSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de registros en la tabla 'producto'.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('producto')->insert(
            [
                [
                    'nombre'        => 'Producto A',
                    'precio'        => 10.5,
                    'stock'         => 100,
                    'categoria'     => 'Categoria 1',
                    'codigo_barras' => 'A123',
                ],
                [
                    'nombre'        => 'Producto B',
                    'precio'        => 20.0,
                    'stock'         => 50,
                    'categoria'     => 'Categoria 2',
                    'codigo_barras' => 'B123',
                ],
                [
                    'nombre'        => 'Producto C',
                    'precio'        => 30.0,
                    'stock'         => 75,
                    'categoria'     => 'Categoria 3',
                    'codigo_barras' => 'C123',
                ],
                [
                    'nombre'        => 'Producto D',
                    'precio'        => 15.0,
                    'stock'         => 200,
                    'categoria'     => 'Categoria 4',
                    'codigo_barras' => 'D123',
                ],
                [
                    'nombre'        => 'Producto E',
                    'precio'        => 25.5,
                    'stock'         => 30,
                    'categoria'     => 'Categoria 5',
                    'codigo_barras' => 'E123',
                ],
            ]
        );
    }
}
