<?php
/**
 * Seeder para poblar la tabla 'transaccion' con datos iniciales.
 *
 * Inserta registros de transacciones de venta y compra con información relacionada a
 * cajero, cliente, proveedor, canal y estado.
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
 * Clase Seeder para insertar datos iniciales en la tabla 'transaccion'.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class TransaccionSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de registros en la tabla 'transaccion'.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('transaccion')->insert(
            [
                [
                    'tipo' => 'venta',
                    'cajero_id' => 1,
                    'cliente_id' => 1,
                    'proveedor_id' => 1,
                    'canal_id' => 1,
                    'estado' => 'completada'
                ],
                [
                    'tipo' => 'compra',
                    'cajero_id' => 2,
                    'cliente_id' => 2,
                    'proveedor_id' => 2,
                    'canal_id' => 2,
                    'estado' => 'pendiente'
                ],
                [
                    'tipo' => 'venta',
                    'cajero_id' => 3,
                    'cliente_id' => 4,
                    'proveedor_id' => 4,
                    'canal_id' => 3,
                    'estado' => 'completada'
                ],
                [
                    'tipo' => 'compra',
                    'cajero_id' => 4,
                    'cliente_id' => 5,
                    'proveedor_id' => 5,
                    'canal_id' => 4,
                    'estado' => 'pendiente'
                ],
                [
                    'tipo' => 'venta',
                    'cajero_id' => 1,
                    'cliente_id' => 1,
                    'proveedor_id' => 1,
                    'canal_id' => 5, 'estado' => 'completada'
                ],
            ]
        );
    }
}
