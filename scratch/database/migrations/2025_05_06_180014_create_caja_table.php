<?php

/**
 * Migración para crear la tabla 'caja' que registra las aperturas y cierres
 * de cajas, asociadas a cajeros y métodos de pago, incluyendo saldos iniciales
 * y finales.
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
     * Ejecuta la migración creando la tabla 'caja' con sus columnas
     * y claves foráneas.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'caja', function (Blueprint $table) {
                $table->id()
                    ->comment('Identificador único de la caja');

                $table->foreignId('cajero_id')
                    ->constrained('cajero')
                    ->onDelete('cascade')
                    ->comment('ID del cajero asociado a esta caja');

                $table->foreignId('canal_id')
                    ->constrained('canal')
                    ->onDelete('cascade')
                    ->comment('ID del método de pago de esta caja');

                $table->double('saldo_inicial')
                    ->comment('Monto inicial en la caja');

                $table->double('saldo_final')
                    ->nullable()
                    ->comment('Monto final en la caja después de transacciones');

                $table->timestamp('apertura')
                    ->useCurrent()
                    ->comment('Fecha y hora de apertura de la caja');

                $table->timestamp('cierre')
                    ->nullable()
                    ->comment('Fecha y hora de cierre de la caja');
            }
        );
    }

    /**
     * Revierte la migración eliminando la tabla 'caja'.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('caja');
    }
};
