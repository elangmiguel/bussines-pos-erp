<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Lookup / catalogue tables (no dependencies)
            TarifaIvaSeeder::class,
            UnidadMedidaSeeder::class,
            CategoriaProductoSeeder::class,
            MedioPagoSeeder::class,
            CategoriaGastoSeeder::class,

            // 2. Auth & people
            RolSeeder::class,
            AdminSeeder::class,

            // 3. Company, config & DIAN
            EmpresaSeeder::class,
            ResolucionDianSeeder::class,

            // 4. Cash registers & funds (depend on MedioPago)
            CajaFondoSeeder::class,

            // 5. Staff & cashiers (depend on Roles)
            ColaboradorSeeder::class,

            // 6. Suppliers (depend on Persona/Empresa)
            ProveedorSeeder::class,

            // 7. Products (depend on Categoria, Unidad, TarifaIva)
            ProductoSeeder::class,

            // 8. Customers (depend on Persona/Empresa)
            ClienteSeeder::class,
        ]);
    }
}
