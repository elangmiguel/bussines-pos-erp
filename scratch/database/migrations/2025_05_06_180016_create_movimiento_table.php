<?php

/**
 * Migración para crear la tabla 'movimiento', que registra los movimientos
 * financieros (ingreso o egreso) asociados a los fondos.
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
     * Ejecuta la migración creando la tabla 'movimiento' con sus columnas
     * y claves foráneas.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'movimiento', function (Blueprint $table) {
                $table->id()
                    ->comment('Identificador único del movimiento de fondo');

                $table->foreignId('fondo_id')
                    ->constrained('fondo')
                    ->onDelete('cascade')
                    ->comment('ID del fondo en el que se realiza el movimiento');

                $table->enum('tipo', ['ingreso', 'egreso'])
                    ->comment('Tipo de movimiento (ingreso, egreso)');

                $table->double('monto')
                    ->comment('Monto del movimiento');

                $table->text('descripcion')
                    ->nullable()
                    ->comment('Descripción del movimiento');

                $table->timestamp('creado_en')
                    ->useCurrent()
                    ->comment('Fecha y hora del movimiento');

                $table->timestamp('actualizado_en')
                    ->useCurrent()
                    ->useCurrentOnUpdate()
                    ->comment('Fecha de la última actualización del movimiento');
            }
        );
    }

    /**
     * Revierte la migración eliminando la tabla 'movimiento'.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento');
    }
};
