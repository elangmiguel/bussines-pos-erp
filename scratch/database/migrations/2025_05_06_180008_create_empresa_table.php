<?php

/**
 * Migración para crear la tabla 'empresa' en la base de datos.
 *
 * Define la estructura de la tabla que almacena información de empresas,
 * incluyendo razón social, NIT y contacto asociado.
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
     * Ejecuta la migración: crea la tabla 'empresa' con sus columnas y relaciones.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'empresa', function (Blueprint $table) {
                $table->id()
                    ->comment('Identificador único de la empresa');

                $table->string('razon_social', 100)
                    ->comment('Razón social de la empresa');

                $table->string('nit', 50)
                    ->unique()
                    ->comment('Número de identificación tributaria de la empresa');

                $table->foreignId('contacto_id')
                    ->nullable()
                    ->constrained('contacto')
                    ->nullOnDelete()
                    ->comment('ID del contacto asociado a la empresa');

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
     * Revierte la migración: elimina la tabla 'empresa' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};
