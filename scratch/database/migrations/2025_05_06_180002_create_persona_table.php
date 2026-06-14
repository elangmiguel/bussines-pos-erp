<?php

/**
 * Migración para crear la tabla 'persona' en la base de datos.
 *
 * Define la estructura de la tabla que almacena información personal,
 * incluyendo nombres, identificación, fecha de nacimiento y relación con contacto.
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
     * Ejecuta la migración: crea la tabla 'persona' con sus columnas y comentarios.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'persona', function (Blueprint $table) {
                $table->id()
                    ->comment('Identificador único para cada persona');

                $table->string('nombre', 100)
                    ->comment('Nombre de la persona');

                $table->string('apellido', 100)
                    ->comment('Apellido de la persona');

                $table->string('identificacion_tipo')
                    ->comment('Tipo de documento (Ej. Cédula, DNI, Pasaporte)');

                $table->string('identificacion_numero')
                    ->unique()
                    ->comment('Número/Código de identificación única de la persona');

                $table->date('fecha_nacimiento')
                    ->comment('Fecha de nacimiento de la persona');

                $table->foreignId('contacto_id')
                    ->nullable()
                    ->constrained('contacto')
                    ->nullOnDelete()
                    ->comment('ID del contacto relacionado con la persona');

                $table->boolean('activo')
                    ->default(true)
                    ->comment('Indica si la persona está activa');

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
     * Revierte la migración: elimina la tabla 'persona' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('persona');
    }
};
