<?php

/**
 * Migración para crear la tabla 'cajero' en la base de datos.
 *
 * Define la estructura de la tabla que almacena información de los cajeros,
 * incluyendo su colaborador asociado y código único.
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
     * Ejecuta la migración: crea la tabla 'cajero' con sus columnas y relaciones.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'cajero', function (Blueprint $table) {
                $table->id()
                    ->comment('Identificador único del cajero');

                $table->foreignId('colaborador_id')
                    ->constrained('colaborador')
                    ->cascadeOnDelete()
                    ->comment('ID del colaborador asociado al cajero');

                $table->string('codigo', 50)
                    ->unique()
                    ->comment('Código único del cajero');

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
     * Revierte la migración: elimina la tabla 'cajero' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('cajero');
    }
};
