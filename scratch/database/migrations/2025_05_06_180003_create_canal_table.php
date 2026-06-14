<?php

/**
 * Migración para crear la tabla 'canal' en la base de datos.
 *
 * Define la estructura de la tabla que almacena los métodos de pago
 * utilizados en transacciones, cajas y fondos.
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
     * Ejecuta la migración: crea la tabla 'canal' con sus columnas y comentarios.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'canal', function (Blueprint $table) {
                $table->id()
                    ->comment('Identificador único del canal');

                $table->string('tipo', 50)
                    ->comment('Tipo de canal (Ej: digital, físico)');

                $table->string('descripcion', 255)
                    ->comment('Descripción del canal o método de pago');

                $table->boolean('activo')
                    ->default(true)
                    ->comment('Indica si el canal está activo');

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
     * Revierte la migración: elimina la tabla 'canal' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('canal');
    }
};
