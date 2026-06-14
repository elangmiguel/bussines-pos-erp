<?php
/**
 * Seeder para poblar la tabla 'cajero' con datos iniciales.
 *
 * Inserta registros de cajeros asociados a colaboradores con su respectivo código.
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
 * Clase Seeder para insertar datos iniciales en la tabla 'cajero'.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class CajeroSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de registros en la tabla 'cajero'.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('cajero')->insert(
            [
                ['colaborador_id' => 1, 'codigo' => 'C1'],
                ['colaborador_id' => 2, 'codigo' => 'C2'],
                ['colaborador_id' => 3, 'codigo' => 'C3'],
                ['colaborador_id' => 4, 'codigo' => 'C4'],
                ['colaborador_id' => 5, 'codigo' => 'C5'],
            ]
        );
    }
}
