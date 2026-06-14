<?php
/**
 * Seeder para poblar la tabla 'caja' con datos iniciales.
 *
 * Inserta registros de cajas asociadas a cajeros y canales con saldo inicial.
 *
 * PHP version 8.1
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Clase para insertar datos iniciales en la tabla 'caja'.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira    <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo           <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class CajaSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de registros en la tabla 'caja'.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('caja')->insert(
            [
                ['cajero_id' => 1, 'canal_id' => 1, 'saldo_inicial' => 5000],
                ['cajero_id' => 2, 'canal_id' => 2, 'saldo_inicial' => 10000],
                ['cajero_id' => 3, 'canal_id' => 3, 'saldo_inicial' => 15000],
                ['cajero_id' => 4, 'canal_id' => 4, 'saldo_inicial' => 20000],
                ['cajero_id' => 5, 'canal_id' => 5, 'saldo_inicial' => 25000],
            ]
        );
    }
}
