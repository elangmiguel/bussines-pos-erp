<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Persona;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            [
                'tipo'   => 'juridico',
                'empresa' => [
                    'razon_social'       => 'Postobón S.A.',
                    'nit'                => '860002528',
                    'digito_verificacion' => '1',
                    'regimen_tributario' => 'responsable_iva',
                    'tipo_empresa'       => 'proveedor',
                    'email'              => 'pedidos@postobon.com',
                    'telefono'           => '6042524000',
                    'ciudad'             => 'Medellín',
                    'departamento'       => 'Antioquia',
                ],
                'condiciones_pago' => 'Pago a 30 días',
                'plazo_dias'       => 30,
            ],
            [
                'tipo'   => 'juridico',
                'empresa' => [
                    'razon_social'       => 'Alpina Productos Alimenticios S.A.',
                    'nit'                => '890800758',
                    'digito_verificacion' => '4',
                    'regimen_tributario' => 'responsable_iva',
                    'tipo_empresa'       => 'proveedor',
                    'email'              => 'ventas@alpina.com.co',
                    'telefono'           => '6013140000',
                    'ciudad'             => 'Bogotá',
                    'departamento'       => 'Cundinamarca',
                ],
                'condiciones_pago' => 'Pago a 15 días',
                'plazo_dias'       => 15,
            ],
            [
                'tipo'   => 'juridico',
                'empresa' => [
                    'razon_social'       => 'Nestlé de Colombia S.A.',
                    'nit'                => '860002521',
                    'digito_verificacion' => '0',
                    'regimen_tributario' => 'responsable_iva',
                    'tipo_empresa'       => 'proveedor',
                    'email'              => 'contacto@nestle.com.co',
                    'telefono'           => '6013148000',
                    'ciudad'             => 'Bogotá',
                    'departamento'       => 'Cundinamarca',
                ],
                'condiciones_pago' => 'Pago a 30 días',
                'plazo_dias'       => 30,
            ],
            [
                'tipo'   => 'juridico',
                'empresa' => [
                    'razon_social'       => 'Colombina S.A.',
                    'nit'                => '890300689',
                    'digito_verificacion' => '9',
                    'regimen_tributario' => 'responsable_iva',
                    'tipo_empresa'       => 'proveedor',
                    'email'              => 'pedidos@colombina.com',
                    'telefono'           => '6023820000',
                    'ciudad'             => 'Cali',
                    'departamento'       => 'Valle del Cauca',
                ],
                'condiciones_pago' => 'Contado',
                'plazo_dias'       => 0,
            ],
            [
                'tipo'   => 'natural',
                'persona' => [
                    'tipo_identificacion'   => 'CC',
                    'numero_identificacion' => '71234567',
                    'nombres'              => 'Distribuidora',
                    'apellidos'            => 'Nacional Ltda',
                    'email'                => 'ventas@distnacional.co',
                    'celular'              => '3109876543',
                    'ciudad'               => 'Bogotá',
                    'departamento'         => 'Cundinamarca',
                ],
                'condiciones_pago' => 'Contado',
                'plazo_dias'       => 0,
            ],
        ];

        foreach ($proveedores as $data) {
            if ($data['tipo'] === 'juridico') {
                $empresa = Empresa::firstOrCreate(
                    ['nit' => $data['empresa']['nit']],
                    $data['empresa']
                );

                Proveedor::firstOrCreate(
                    ['empresa_id' => $empresa->id, 'tipo' => 'juridico'],
                    [
                        'condiciones_pago' => $data['condiciones_pago'],
                        'plazo_dias'       => $data['plazo_dias'],
                        'activo'           => true,
                    ]
                );
            } else {
                $persona = Persona::firstOrCreate(
                    [
                        'tipo_identificacion'   => $data['persona']['tipo_identificacion'],
                        'numero_identificacion' => $data['persona']['numero_identificacion'],
                    ],
                    $data['persona']
                );

                Proveedor::firstOrCreate(
                    ['persona_id' => $persona->id, 'tipo' => 'natural'],
                    [
                        'condiciones_pago' => $data['condiciones_pago'],
                        'plazo_dias'       => $data['plazo_dias'],
                        'activo'           => true,
                    ]
                );
            }
        }
    }
}
