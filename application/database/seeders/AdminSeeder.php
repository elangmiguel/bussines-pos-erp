<?php

namespace Database\Seeders;

use App\Models\Cajero;
use App\Models\Colaborador;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $rol = Rol::where('nombre', 'administrador')->first();

        $persona = Persona::firstOrCreate(
            ['tipo_identificacion' => 'CC', 'numero_identificacion' => '1000000000'],
            [
                'nombres' => 'Administrador',
                'apellidos' => 'Sistema',
                'email' => 'admin@businesscmd.co',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'admin@businesscmd.co'],
            [
                'name'       => 'Administrador Sistema',
                'password'   => Hash::make('Admin123!'),
                'persona_id' => $persona->id,
                'rol_id'     => $rol?->id,
                'activo'     => true,
            ]
        );

        $colaborador = Colaborador::firstOrCreate(
            ['user_id' => $user->id],
            ['turno' => 'completo', 'fecha_contratacion' => '2024-01-01']
        );

        Cajero::firstOrCreate(
            ['colaborador_id' => $colaborador->id],
            ['codigo' => 'ADM001', 'activo' => true]
        );
    }
}
