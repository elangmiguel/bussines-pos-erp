<?php
/**
 * Seeder para poblar la tabla 'detalle' con datos iniciales.
 *
 * Inserta registros de detalle que relacionan transacciones con productos,
 * incluyendo cantidad y precio unitario.
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
 * Clase Seeder para insertar datos iniciales en la tabla 'detalle'.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class DetalleSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de registros en la tabla 'detalle'.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('detalle')->insert(
            [
                [
                    'transaccion_id' => 1,
                    'producto_id' => 1,
                    'cantidad' => 5,
                    'precio_unitario' => 10.5
                ],
                [
                    'transaccion_id' => 2,
                    'producto_id' => 2,
                    'cantidad' => 3,
                    'precio_unitario' => 20.0
                ],
                [
                    'transaccion_id' => 3,
                    'producto_id' => 4,
                    'cantidad' => 2,
                    'precio_unitario' => 15.0
                ],
                [
                    'transaccion_id' => 4,
                    'producto_id' => 5,
                    'cantidad' => 1,
                    'precio_unitario' => 25.5
                ],
                [
                    'transaccion_id' => 5,
                    'producto_id' => 1,
                    'cantidad' => 4,
                    'precio_unitario' => 12.5
                ],
            ]
        );
    }
}
