<?php
/**
 * Seeder para poblar la tabla 'cliente' con datos iniciales.
 *
 * Inserta registros de clientes asociados a personas existentes en la base de datos.
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
 * Clase Seeder para insertar datos iniciales en la tabla 'cliente'.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class ClienteSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de registros en la tabla 'cliente'.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('cliente')->insert(
            [
                ['persona_id' => 1],
                ['persona_id' => 2],
                ['persona_id' => 3],
                ['persona_id' => 4],
                ['persona_id' => 5],
            ]
        );
    }
}
