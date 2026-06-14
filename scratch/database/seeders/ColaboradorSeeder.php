<?php
/**
 * Seeder para poblar la tabla 'colaborador' con datos iniciales.
 *
 * Inserta registros de colaboradores asociados a usuarios,
 * indicando salario, turno y estado de actividad.
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
 * Clase Seeder para insertar datos iniciales en la tabla 'colaborador'.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class ColaboradorSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de registros en la tabla 'colaborador'.
     *
     * @return void
     */
    public function run(): void
    {
        // Asegúrese de que los usuarios con estos IDs estén previamente registrados.
        DB::table('colaborador')->insert(
            [
                [
                    'usuario_id' => 1,
                    'salario'    => 2500.00,
                    'turno'      => 'mañana',
                    'activo'     => true,
                ],
                [
                    'usuario_id' => 2,
                    'salario'    => 2700.00,
                    'turno'      => 'tarde',
                    'activo'     => true,
                ],
                [
                    'usuario_id' => 3,
                    'salario'    => 2600.00,
                    'turno'      => 'noche',
                    'activo'     => false,
                ],
                [
                    'usuario_id' => 4,
                    'salario'    => 2800.00,
                    'turno'      => 'mañana',
                    'activo'     => true,
                ],
                [
                    'usuario_id' => 5,
                    'salario'    => 2550.00,
                    'turno'      => 'tarde',
                    'activo'     => true,
                ],
            ]
        );
    }
}
