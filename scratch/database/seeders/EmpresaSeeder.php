<?php
/**
 * Seeder para poblar la tabla 'empresa' con datos iniciales.
 *
 * Inserta registros de empresas asociadas a contactos existentes
 * en la base de datos.
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
 * Clase Seeder para insertar datos iniciales en la tabla 'empresa'.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class EmpresaSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de registros en la tabla 'empresa'.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('empresa')->insert(
            [
                [
                    'razon_social' => 'Empresa A',
                    'nit' => 12387631,
                    'contacto_id' => 1
                ],
                [
                    'razon_social' => 'Empresa B',
                    'nit' => 12387632,
                    'contacto_id' => 2
                ],
                [
                    'razon_social' => 'Empresa C',
                    'nit' => 12387633,
                    'contacto_id' => 3
                ],
                [
                    'razon_social' => 'Empresa D',
                    'nit' => 12387634,
                    'contacto_id' => 4
                ],
                [
                    'razon_social' => 'Empresa E',
                    'nit' => 12387635,
                    'contacto_id' => 5
                ],
            ]
        );
    }
}
