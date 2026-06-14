<?php
/**
 * Seeder principal para poblar la base de datos completa de la aplicación.
 *
 * Ejecuta en orden los seeders individuales
 * para insertar datos iniciales en las tablas.
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

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Clase principal que orquesta la ejecución de todos los seeders.
 *
 * @category Database
 * @package  Seeders
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta todos los seeders definidos para poblar la base de datos.
     *
     * El orden de llamada respeta las dependencias entre tablas.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call(
            [
                ContactoSeeder::class,
                PersonaSeeder::class,
                CanalSeeder::class,
                ClienteSeeder::class,
                UsuarioSeeder::class,
                ColaboradorSeeder::class,
                CajeroSeeder::class,
                EmpresaSeeder::class,
                ProveedorSeeder::class,
                ProductoSeeder::class,
                ProveedorProductoSeeder::class,
                TransaccionSeeder::class,
                DetalleSeeder::class,
                CajaSeeder::class,
                FondoSeeder::class,
                MovimientoSeeder::class,
            ]
        );
    }
}
