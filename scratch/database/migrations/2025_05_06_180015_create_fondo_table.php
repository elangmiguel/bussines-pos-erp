<?php

/**
 * Migración para crear la tabla 'fondo', que registra diferentes tipos de fondos
 * financieros asociados a métodos de pago.
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
     * Ejecuta la migración creando la tabla 'fondo' con sus columnas
     * y claves foráneas.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'fondo', function (Blueprint $table) {
                $table->id()
                    ->comment('Identificador único del fondo');

                $table->string('nombre', 100)
                    ->comment('Nombre del fondo (ej: Caja Cajero 1, Cuenta Nequi)');

                $table->enum('tipo', ['caja', 'digital', 'banco', 'reserva', 'otros'])
                    ->comment('Tipo de fondo (caja, digital, banco, reserva, otros)');

                $table->foreignId('canal_id')
                    ->constrained('canal')
                    ->onDelete('cascade')
                    ->comment('Método de pago asociado a este fondo');

                $table->text('descripcion')
                    ->nullable()
                    ->comment('Descripción del fondo');

                $table->boolean('activo')
                    ->default(true)
                    ->comment('Indica si el fondo está activo');

                $table->timestamp('creado_en')
                    ->useCurrent()
                    ->comment('Fecha de creación del fondo');

                $table->timestamp('actualizado_en')
                    ->useCurrent()
                    ->useCurrentOnUpdate()
                    ->comment('Fecha de la última actualización del fondo');
            }
        );
    }

    /**
     * Revierte la migración eliminando la tabla 'fondo'.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('fondo');
    }
};
