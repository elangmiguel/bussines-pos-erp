<?php

/**
 * Migración para crear la tabla 'contacto' en la base de datos.
 *
 * Define la estructura de la tabla que almacena información de contactos,
 * incluyendo teléfono, email, dirección y estado de actividad.
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
     * Ejecuta la migración: crea la tabla 'contacto' con sus columnas y comentarios.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'contacto', function (Blueprint $table) {
                $table->id()->comment('Identificador único para cada contacto');

                $table->string('telefono', 20)
                    ->comment('Número de teléfono del contacto');
                    // ->unique();

                $table->string('email', 100)
                    ->comment('Correo electrónico del contacto');
                    // ->unique();

                $table->string('direccion', 200)
                    ->comment('Dirección del contacto');

                $table->boolean('activo')
                    ->default(true)
                    ->comment('Indica si el contacto está activo');

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
     * Revierte la migración: elimina la tabla 'contacto' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('contacto');
    }
};
