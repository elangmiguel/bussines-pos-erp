<?php

/**
 * Migración para crear la tabla 'usuario' en la base de datos.
 *
 * Define la estructura de la tabla que almacena los usuarios del sistema,
 * vinculados a una persona, con campos de autenticación y control de estado.
 *
 * PHP version 8.1
 *
 * @category Database
 * @package  Migrations
 * @author   Miguel Ángel Rodríguez Ferreira    <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo           <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Ejecuta la migración: crea la tabla 'usuario' con sus columnas
     * y claves foráneas.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'usuario', function (Blueprint $table) {
                $table->id()
                    ->comment('Identificador único del usuario');

                $table->foreignId('persona_id')
                    ->constrained('persona')
                    ->cascadeOnDelete()
                    ->comment('ID de la persona asociada al usuario');

                $table->string('usuario', 50)
                    ->unique()
                    ->comment('Nombre de usuario único para el acceso');

                $table->string('rol', 50)
                    ->comment('rol del usuario');

                $table->string('contrasena', 255)
                    ->comment('Contraseña cifrada del usuario');

                $table->boolean('activo')
                    ->default(true)
                    ->comment('Indica si el usuario está activo');

                $table->timestamp('creado_en')
                    ->useCurrent()
                    ->nullable(false)
                    ->comment('Fecha de creación del registro');

                $table->timestamp('actualizado_en')
                    ->useCurrent()
                    ->useCurrentOnUpdate()
                    ->nullable(false)
                    ->comment('Fecha de la última actualización del registro');
            }
        );
    }

    /**
     * Revierte la migración: elimina la tabla 'usuario' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
