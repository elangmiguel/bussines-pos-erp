<?php
/**
 * Seeder para poblar la tabla 'movimiento' con datos iniciales.
 *
 * Inserta registros de movimientos financieros asociados a fondos existentes.
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
 * Clase Seeder para insertar datos iniciales en la tabla 'movimiento'.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class MovimientoSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de registros en la tabla 'movimiento'.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('movimiento')->insert(
            [
                [
                    'fondo_id' => 1,
                    'tipo' => 'ingreso',
                    'monto' => 500
                ],
                [
                    'fondo_id' => 2,
                    'tipo' => 'egreso',
                    'monto' => 1000
                ],
                [
                    'fondo_id' => 3,
                    'tipo' => 'ingreso',
                    'monto' => 1500
                ],
                [
                    'fondo_id' => 4,
                    'tipo' => 'egreso',
                    'monto' => 2000
                ],
                [
                    'fondo_id' => 5,
                    'tipo' => 'ingreso',
                    'monto' => 2500
                ],
            ]
        );
    }
}
