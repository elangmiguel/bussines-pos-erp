<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'administrador',
                'descripcion' => 'Acceso total al sistema',
                'permisos' => [
                    'dashboard.ver' => true,
                    'pos.usar' => true,
                    'inventario.ver' => true,
                    'inventario.crear' => true,
                    'inventario.editar' => true,
                    'inventario.eliminar' => true,
                    'inventario.ajustar' => true,
                    'clientes.ver' => true,
                    'clientes.crear' => true,
                    'clientes.editar' => true,
                    'clientes.eliminar' => true,
                    'cartera.ver' => true,
                    'cartera.abonar' => true,
                    'proveedores.ver' => true,
                    'proveedores.crear' => true,
                    'proveedores.editar' => true,
                    'proveedores.eliminar' => true,
                    'compras.ver' => true,
                    'compras.crear' => true,
                    'compras.recibir' => true,
                    'facturacion.ver' => true,
                    'facturacion.crear' => true,
                    'facturacion.anular' => true,
                    'notas.crear' => true,
                    'caja.abrir' => true,
                    'caja.cerrar' => true,
                    'caja.ver' => true,
                    'gastos.ver' => true,
                    'gastos.crear' => true,
                    'reportes.ver' => true,
                    'reportes.dian' => true,
                    'config.ver' => true,
                    'config.editar' => true,
                    'usuarios.ver' => true,
                    'usuarios.crear' => true,
                    'usuarios.editar' => true,
                ],
            ],
            [
                'nombre' => 'vendedor',
                'descripcion' => 'Crea ventas y gestiona clientes',
                'permisos' => [
                    'dashboard.ver' => true,
                    'pos.usar' => true,
                    'inventario.ver' => true,
                    'clientes.ver' => true,
                    'clientes.crear' => true,
                    'clientes.editar' => true,
                    'cartera.ver' => true,
                    'facturacion.ver' => true,
                    'facturacion.crear' => true,
                    'caja.ver' => true,
                    'reportes.ver' => true,
                ],
            ],
            [
                'nombre' => 'cajero',
                'descripcion' => 'Opera la caja registradora',
                'permisos' => [
                    'dashboard.ver' => true,
                    'pos.usar' => true,
                    'inventario.ver' => true,
                    'clientes.ver' => true,
                    'clientes.crear' => true,
                    'facturacion.ver' => true,
                    'facturacion.crear' => true,
                    'caja.abrir' => true,
                    'caja.cerrar' => true,
                    'caja.ver' => true,
                    'gastos.crear' => true,
                ],
            ],
            [
                'nombre' => 'bodeguero',
                'descripcion' => 'Gestiona inventario y recibe mercancía',
                'permisos' => [
                    'dashboard.ver' => true,
                    'inventario.ver' => true,
                    'inventario.editar' => true,
                    'inventario.ajustar' => true,
                    'proveedores.ver' => true,
                    'compras.ver' => true,
                    'compras.recibir' => true,
                ],
            ],
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(
                ['nombre' => $rol['nombre']],
                $rol
            );
        }
    }
}
