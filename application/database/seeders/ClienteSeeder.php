<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Persona;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        // ── Consumidor Final (required for POS anonymous sales) ───────────
        $cf = Persona::firstOrCreate(
            ['tipo_identificacion' => 'CC', 'numero_identificacion' => '222222222'],
            [
                'nombres'      => 'Consumidor',
                'apellidos'    => 'Final',
                'ciudad'       => 'Bogotá',
                'departamento' => 'Cundinamarca',
            ]
        );
        Cliente::firstOrCreate(
            ['persona_id' => $cf->id, 'tipo' => 'natural'],
            [
                'tipo_cliente'   => 'regular',
                'credito_activo' => false,
                'activo'         => true,
            ]
        );

        // ── Natural clients ───────────────────────────────────────────────
        $naturales = [
            [
                'persona' => [
                    'tipo_identificacion'   => 'CC',
                    'numero_identificacion' => '52987654',
                    'nombres'              => 'Laura',
                    'apellidos'            => 'Suárez Peña',
                    'email'                => 'laura.suarez@gmail.com',
                    'celular'              => '3112345678',
                    'ciudad'               => 'Bogotá',
                    'departamento'         => 'Cundinamarca',
                ],
                'tipo_cliente'   => 'frecuente',
                'credito_activo' => false,
                'limite_credito' => 0,
            ],
            [
                'persona' => [
                    'tipo_identificacion'   => 'CC',
                    'numero_identificacion' => '80456789',
                    'nombres'              => 'Andrés',
                    'apellidos'            => 'García López',
                    'email'                => 'agarcia@hotmail.com',
                    'celular'              => '3209876543',
                    'ciudad'               => 'Bogotá',
                    'departamento'         => 'Cundinamarca',
                ],
                'tipo_cliente'   => 'frecuente',
                'credito_activo' => true,
                'limite_credito' => 500000,
                'plazo_dias'     => 15,
            ],
            [
                'persona' => [
                    'tipo_identificacion'   => 'CC',
                    'numero_identificacion' => '43876543',
                    'nombres'              => 'María',
                    'apellidos'            => 'Rodríguez Castro',
                    'email'                => 'mrodriguez@yahoo.com',
                    'celular'              => '3151234567',
                    'ciudad'               => 'Bogotá',
                    'departamento'         => 'Cundinamarca',
                ],
                'tipo_cliente'   => 'regular',
                'credito_activo' => false,
                'limite_credito' => 0,
            ],
            [
                'persona' => [
                    'tipo_identificacion'   => 'CC',
                    'numero_identificacion' => '11345678',
                    'nombres'              => 'Juan Carlos',
                    'apellidos'            => 'Morales Ríos',
                    'celular'              => '3006789012',
                    'ciudad'               => 'Bogotá',
                    'departamento'         => 'Cundinamarca',
                ],
                'tipo_cliente'   => 'frecuente',
                'credito_activo' => true,
                'limite_credito' => 800000,
                'plazo_dias'     => 30,
            ],
        ];

        foreach ($naturales as $data) {
            $persona = Persona::firstOrCreate(
                [
                    'tipo_identificacion'   => $data['persona']['tipo_identificacion'],
                    'numero_identificacion' => $data['persona']['numero_identificacion'],
                ],
                $data['persona']
            );

            Cliente::firstOrCreate(
                ['persona_id' => $persona->id, 'tipo' => 'natural'],
                [
                    'tipo_cliente'   => $data['tipo_cliente'],
                    'credito_activo' => $data['credito_activo'],
                    'limite_credito' => $data['limite_credito'],
                    'plazo_dias'     => $data['plazo_dias'] ?? 0,
                    'activo'         => true,
                ]
            );
        }

        // ── Corporate clients ─────────────────────────────────────────────
        $corporativos = [
            [
                'empresa' => [
                    'razon_social'       => 'Restaurante El Buen Sabor S.A.S.',
                    'nit'                => '900876543',
                    'digito_verificacion' => '2',
                    'regimen_tributario' => 'responsable_iva',
                    'tipo_empresa'       => 'cliente',
                    'email'              => 'compras@elbuensabor.com',
                    'telefono'           => '6013456789',
                    'ciudad'             => 'Bogotá',
                    'departamento'       => 'Cundinamarca',
                ],
                'tipo_cliente'   => 'corporativo',
                'credito_activo' => true,
                'limite_credito' => 3000000,
                'plazo_dias'     => 30,
            ],
            [
                'empresa' => [
                    'razon_social'       => 'Panadería y Cafetería La Cosecha Ltda.',
                    'nit'                => '800654321',
                    'digito_verificacion' => '5',
                    'regimen_tributario' => 'no_responsable_iva',
                    'tipo_empresa'       => 'cliente',
                    'email'              => 'lacosecha@gmail.com',
                    'telefono'           => '3178901234',
                    'ciudad'             => 'Bogotá',
                    'departamento'       => 'Cundinamarca',
                ],
                'tipo_cliente'   => 'corporativo',
                'credito_activo' => true,
                'limite_credito' => 1500000,
                'plazo_dias'     => 15,
            ],
        ];

        foreach ($corporativos as $data) {
            $empresa = Empresa::firstOrCreate(
                ['nit' => $data['empresa']['nit']],
                $data['empresa']
            );

            Cliente::firstOrCreate(
                ['empresa_id' => $empresa->id, 'tipo' => 'juridico'],
                [
                    'tipo_cliente'   => $data['tipo_cliente'],
                    'credito_activo' => $data['credito_activo'],
                    'limite_credito' => $data['limite_credito'],
                    'plazo_dias'     => $data['plazo_dias'],
                    'activo'         => true,
                ]
            );
        }
    }
}
