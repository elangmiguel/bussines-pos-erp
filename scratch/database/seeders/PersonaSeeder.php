<?php
/**
 * Seeder para poblar la tabla 'persona' con datos iniciales.
 *
 * Inserta registros de personas con datos de nombre, apellido, tipo
 * y número de identificación, fecha de nacimiento y referencia a contacto existente.
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
 * Clase Seeder para insertar datos iniciales en la tabla 'persona'.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class PersonaSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de registros en la tabla 'persona'.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('persona')->insert(
            [
                [
                    'nombre'                => 'Juan',
                    'apellido'              => 'Perez',
                    'identificacion_tipo'   => 'cedula',
                    'identificacion_numero' => 12345678,
                    'fecha_nacimiento'      => '1980-01-01',
                    'contacto_id'           => 1
                ],
                [
                    'nombre'                => 'Maria',
                    'apellido'              => 'Gomez',
                    'identificacion_tipo'   => 'cedula',
                    'identificacion_numero' => 23456789,
                    'fecha_nacimiento'      => '1990-05-15',
                    'contacto_id'           => 2
                ],
                [
                    'nombre'                => 'Carlos',
                    'apellido'              => 'Lopez',
                    'identificacion_tipo'   => 'cedula',
                    'identificacion_numero' => 34567890,
                    'fecha_nacimiento'      => '1985-09-10',
                    'contacto_id'           => 3
                ],
                [
                    'nombre'                => 'Ana',
                    'apellido'              => 'Martinez',
                    'identificacion_tipo'   => 'cedula',
                    'identificacion_numero' => 45678901,
                    'fecha_nacimiento'      => '1992-12-20',
                    'contacto_id'           => 4
                ],
                [
                    'nombre'                => 'Luis',
                    'apellido'              => 'Ramirez',
                    'identificacion_tipo'   => 'cedula',
                    'identificacion_numero' => 56789012,
                    'fecha_nacimiento'      => '1978-04-25',
                    'contacto_id'           => 5
                ],
            ]
        );
    }
}
