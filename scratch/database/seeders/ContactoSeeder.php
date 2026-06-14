<?php
/**
 * Seeder to populate the 'contacto' table with initial data.
 *
 * Inserts contact records with phone, email, and address information.
 *
 * PHP version 8.1
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - All Rights Reserved
 * @link     https://localhost:8000/api/documentation
 */
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder class to insert initial data into the 'contacto' table.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - All Rights Reserved
 * @link     https://localhost:8000/api/documentation
 */
class ContactoSeeder extends Seeder
{
    /**
     * Executes the insertion of records into the 'contacto' table.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('contacto')->insert(
            [
                [
                    'telefono' => '1234567890',
                    'email' => 'contacto1@dominio.com',
                    'direccion' => 'Address 1'
                ],
                [
                    'telefono' => '1234567891',
                    'email' => 'contacto2@dominio.com',
                    'direccion' => 'Address 2'
                ],
                [
                    'telefono' => '1234567892',
                    'email' => 'contacto3@dominio.com',
                    'direccion' => 'Address 3'
                ],
                [
                    'telefono' => '1234567893',
                    'email' => 'contacto4@dominio.com',
                    'direccion' => 'Address 4'
                ],
                [
                    'telefono' => '1234567894',
                    'email' => 'contacto5@dominio.com',
                    'direccion' => 'Address 5'
                ],
            ]
        );
    }
}
