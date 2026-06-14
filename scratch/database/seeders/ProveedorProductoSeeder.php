<?php
/**
 * Seeder para poblar la tabla 'proveedor_producto' con datos iniciales.
 *
 * Inserta registros que relacionan proveedores con productos
 * existentes en la base de datos.
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
 * Clase Seeder para insertar datos iniciales en la tabla 'proveedor_producto'.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class ProveedorProductoSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de registros en la tabla 'proveedor_producto'.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('proveedor_producto')->insert(
            [
                ['proveedor_id' => 1, 'producto_id' => 1],
                ['proveedor_id' => 2, 'producto_id' => 2],
                ['proveedor_id' => 3, 'producto_id' => 3],
                ['proveedor_id' => 4, 'producto_id' => 4],
                ['proveedor_id' => 5, 'producto_id' => 5],
            ]
        );
    }
}
