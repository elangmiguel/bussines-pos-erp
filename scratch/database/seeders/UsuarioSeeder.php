<?php
/**
 * Seeder para poblar la tabla 'usuario' con datos iniciales.
 *
 * Inserta usuarios con referencia a personas y contraseñas cifradas.
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
use Illuminate\Support\Facades\Hash;

/**
 * Clase Seeder para insertar datos iniciales en la tabla 'usuario'.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class UsuarioSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de registros en la tabla 'usuario'.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('usuario')->insert(
            [
                [
                    'persona_id' => 1,
                    'usuario' => 'admin',
                    'rol' => 'admin',
                    'contrasena' => Hash::make('admin123')
                ],
                [
                    'persona_id' => 2,
                    'usuario' => 'usuario1',
                    'rol' => 'colaborador',
                    'contrasena' => Hash::make('contrasena123')
                ],
                [
                    'persona_id' => 3,
                    'usuario' => 'usuario2',
                    'rol' => 'colaborador',
                    'contrasena' => Hash::make('contrasena123')
                ],
                [
                    'persona_id' => 4,
                    'usuario' => 'usuario3',
                    'rol' => 'colaborador',
                    'contrasena' => Hash::make('contrasena123')
                ],
                [
                    'persona_id' => 5,
                    'usuario' => 'usuario4',
                    'rol' => 'colaborador',
                    'contrasena' => Hash::make('contrasena123')
                ],
            ]
        );
    }
}
