<?php

/**
 * Migración para crear la tabla 'transaccion' que almacena información
 * sobre las transacciones realizadas, ya sean ventas o compras.
 *
 * Define campos con claves foráneas a cajero, cliente, proveedor y canal de pago,
 * además de estado y marcas de tiempo para control de registros.
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
     * Ejecuta la migración creando la tabla 'transaccion' con sus columnas,
     * claves foráneas, y comentarios descriptivos.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'transaccion', function (Blueprint $table) {
                $table->id()->comment('Identificador único de la transacción');

                $table->enum('tipo', ['venta', 'compra'])
                    ->comment('Tipo de transacción: venta o compra');

                $table->foreignId('cajero_id')
                    ->nullable()
                    ->constrained('cajero')
                    ->onDelete('no action')
                    ->comment('ID del cajero que realizó la transacción');

                $table->foreignId('cliente_id')
                    ->nullable()
                    ->constrained('cliente')
                    ->onDelete('no action')
                    ->comment('ID del cliente asociado a la transacción');

                $table->foreignId('proveedor_id')
                    ->nullable()
                    ->constrained('proveedor')
                    ->onDelete('set null')
                    ->comment('ID del proveedor asociado a la transacción');

                $table->foreignId('canal_id')
                    ->nullable()
                    ->constrained('canal')
                    ->onDelete('set null')
                    ->comment('ID del canal de pago utilizado en la transacción');

                $table->string('estado', 50)
                    ->nullable()
                    ->comment('Estado de la transacción (completada, pendiente...)');

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
     * Revierte la migración eliminando la tabla 'transaccion'.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('transaccion');
    }
};
