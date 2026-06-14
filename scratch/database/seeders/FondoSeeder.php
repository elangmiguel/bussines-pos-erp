<?php
/**
 * Seeder para poblar la tabla 'fondo' con datos iniciales.
 *
 * Inserta registros de fondos asociados a canales existentes en la base de datos.
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
 * Clase Seeder para insertar datos iniciales en la tabla 'fondo'.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class FondoSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de registros en la tabla 'fondo'.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('fondo')->insert(
            [
                [
                    'nombre' => 'C1',
                    'canal_id' => 1,
                    'tipo' => 'caja'
                ],
                [
                    'nombre' => 'C2',
                    'canal_id' => 2,
                    'tipo' => 'caja'
                ],
                [
                    'nombre' => 'C3',
                    'canal_id' => 3,
                    'tipo' => 'caja'
                ],
                [
                    'nombre' => 'C3',
                    'canal_id' => 4,
                    'tipo' => 'caja'
                ],
                [
                    'nombre' => 'Nequi',
                    'canal_id' => 5,
                    'tipo' => 'digital'
                ],
            ]
        );
    }
}
