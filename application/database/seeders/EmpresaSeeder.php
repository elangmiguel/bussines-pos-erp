<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use App\Models\Empresa;
use App\Models\Persona;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        $representante = Persona::firstOrCreate(
            ['tipo_identificacion' => 'CC', 'numero_identificacion' => '79512345'],
            [
                'nombres'     => 'Roberto',
                'apellidos'   => 'Medina Torres',
                'email'       => 'gerencia@laeconomia.com.co',
                'celular'     => '3001234567',
                'ciudad'      => 'Bogotá',
                'departamento' => 'Cundinamarca',
            ]
        );

        $empresa = Empresa::firstOrCreate(
            ['nit' => '901234567'],
            [
                'razon_social'       => 'Supermercado La Economía S.A.S.',
                'digito_verificacion' => '8',
                'regimen_tributario' => 'responsable_iva',
                'tipo_empresa'       => 'propia',
                'representante_id'   => $representante->id,
                'email'              => 'info@laeconomia.com.co',
                'telefono'           => '6011234567',
                'direccion'          => 'Cra 15 # 80-25 Local 101',
                'ciudad'             => 'Bogotá',
                'departamento'       => 'Cundinamarca',
            ]
        );

        Configuracion::firstOrCreate(
            ['empresa_id' => $empresa->id],
            [
                'moneda'                  => 'COP',
                'zona_horaria'            => 'America/Bogota',
                'dias_vencimiento_cred'   => 30,
                'prefijo_nota_credito'    => 'NC',
                'prefijo_nota_debito'     => 'ND',
            ]
        );
    }
}
