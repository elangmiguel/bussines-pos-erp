<?php
/**
 * Seeder para poblar la tabla 'canal' con datos iniciales.
 *
 * Inserta registros de canales que representan los distintos
 * métodos de pago disponibles.
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
 * Clase Seeder para insertar datos iniciales en la tabla 'canal'.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class CanalSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de registros en la tabla 'canal'.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('canal')->insert(
            [
                ['tipo' => 'efectivo',     'descripcion' => 'Pago en efectivo'],
                ['tipo' => 'tarjeta',      'descripcion' => 'Pago con tarjeta'],
                ['tipo' => 'nequi',        'descripcion' => 'Pago con Nequi'],
                ['tipo' => 'bancolombia',  'descripcion' => 'Transferencia Bancolombia'],
                ['tipo' => 'otro',         'descripcion' => 'Otro método de pago'],
            ]
        );
    }
}
