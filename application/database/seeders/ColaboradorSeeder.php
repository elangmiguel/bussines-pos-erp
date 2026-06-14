<?php

namespace Database\Seeders;

use App\Models\Cajero;
use App\Models\Colaborador;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class ColaboradorSeeder extends Seeder
{
    public function run(): void
    {
        $rolCajero   = Rol::where('nombre', 'cajero')->first();
        $rolVendedor = Rol::where('nombre', 'vendedor')->first();

        $staff = [
            [
                'persona' => [
                    'tipo_identificacion'   => 'CC',
                    'numero_identificacion' => '52111222',
                    'nombres'              => 'María',
                    'apellidos'            => 'González Vargas',
                    'email'                => 'cajera1@laeconomia.com.co',
                    'celular'              => '3001112222',
                    'ciudad'               => 'Bogotá',
                    'departamento'         => 'Cundinamarca',
                ],
                'user' => [
                    'name'     => 'María González',
                    'email'    => 'cajera1@laeconomia.com.co',
                    'password' => 'Cajera123!',
                    'rol_id'   => $rolCajero?->id,
                ],
                'colaborador' => [
                    'salario'            => 1300000,
                    'turno'              => 'manana',
                    'fecha_contratacion' => '2023-03-01',
                ],
                'cajero_codigo' => 'CAJ001',
            ],
            [
                'persona' => [
                    'tipo_identificacion'   => 'CC',
                    'numero_identificacion' => '80333444',
                    'nombres'              => 'Carlos',
                    'apellidos'            => 'Rodríguez Mendez',
                    'email'                => 'cajero2@laeconomia.com.co',
                    'celular'              => '3003334444',
                    'ciudad'               => 'Bogotá',
                    'departamento'         => 'Cundinamarca',
                ],
                'user' => [
                    'name'     => 'Carlos Rodríguez',
                    'email'    => 'cajero2@laeconomia.com.co',
                    'password' => 'Cajero123!',
                    'rol_id'   => $rolCajero?->id,
                ],
                'colaborador' => [
                    'salario'            => 1300000,
                    'turno'              => 'tarde',
                    'fecha_contratacion' => '2023-06-15',
                ],
                'cajero_codigo' => 'CAJ002',
            ],
            [
                'persona' => [
                    'tipo_identificacion'   => 'CC',
                    'numero_identificacion' => '43555666',
                    'nombres'              => 'Sandra',
                    'apellidos'            => 'Bermúdez Orjuela',
                    'email'                => 'vendedora@laeconomia.com.co',
                    'celular'              => '3155556666',
                    'ciudad'               => 'Bogotá',
                    'departamento'         => 'Cundinamarca',
                ],
                'user' => [
                    'name'     => 'Sandra Bermúdez',
                    'email'    => 'vendedora@laeconomia.com.co',
                    'password' => 'Vendedor123!',
                    'rol_id'   => $rolVendedor?->id,
                ],
                'colaborador' => [
                    'salario'            => 1500000,
                    'turno'              => 'completo',
                    'fecha_contratacion' => '2022-01-10',
                ],
                'cajero_codigo' => null,
            ],
        ];

        foreach ($staff as $data) {
            $persona = Persona::firstOrCreate(
                [
                    'tipo_identificacion'   => $data['persona']['tipo_identificacion'],
                    'numero_identificacion' => $data['persona']['numero_identificacion'],
                ],
                $data['persona']
            );

            $user = User::firstOrCreate(
                ['email' => $data['user']['email']],
                [
                    'name'       => $data['user']['name'],
                    'password'   => Hash::make($data['user']['password']),
                    'persona_id' => $persona->id,
                    'rol_id'     => $data['user']['rol_id'],
                    'activo'     => true,
                ]
            );

            $colaborador = Colaborador::firstOrCreate(
                ['user_id' => $user->id],
                $data['colaborador']
            );

            if ($data['cajero_codigo']) {
                Cajero::firstOrCreate(
                    ['colaborador_id' => $colaborador->id],
                    [
                        'codigo' => $data['cajero_codigo'],
                        'activo' => true,
                    ]
                );
            }
        }
    }
}
