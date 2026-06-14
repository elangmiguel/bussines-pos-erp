<?php

/**
 * Migración para crear la tabla 'colaborador' en la base de datos.
 *
 * Define la estructura de la tabla que almacena información de los colaboradores,
 * incluyendo su usuario asociado, salario, turno y estado de actividad.
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
     * Ejecuta la migración: crea la tabla 'colaborador' con sus columnas
     * y relaciones.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'colaborador', function (Blueprint $table) {
                $table->id()
                    ->comment('Identificador único del colaborador');

                $table->foreignId('usuario_id')
                    ->constrained('usuario')
                    ->cascadeOnDelete()
                    ->comment('ID del usuario asociado al colaborador');

                $table->double('salario')
                    ->comment('Salario del colaborador');

                $table->string('turno', 50)
                    ->comment('Turno de trabajo del colaborador');

                $table->boolean('activo')
                    ->default(true)
                    ->comment('Indica si el colaborador está activo');

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
     * Revierte la migración: elimina la tabla 'colaborador' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('colaborador');
    }
};
