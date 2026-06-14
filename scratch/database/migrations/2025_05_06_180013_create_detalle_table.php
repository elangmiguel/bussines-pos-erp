<?php

/**
 * Migración para crear la tabla 'detalle' que almacena los detalles
 * específicos de cada transacción, vinculando productos con cantidades
 * y precios unitarios.
 *
 * Define claves foráneas a transacción y producto, además de marcas de tiempo.
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
     * Ejecuta la migración creando la tabla 'detalle' con sus columnas,
     * claves foráneas, y comentarios descriptivos.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'detalle', function (Blueprint $table) {
                $table->id()
                    ->comment('Identificador único del detalle de transacción');

                $table->foreignId('transaccion_id')
                    ->constrained('transaccion')
                    ->onDelete('cascade')
                    ->comment('ID de la transacción a la que pertenece este detalle');

                $table->foreignId('producto_id')
                    ->constrained('producto')
                    ->onDelete('cascade')
                    ->comment('ID del producto involucrado en este detalle');

                $table->integer('cantidad')
                    ->comment('Cantidad de producto en este detalle');

                $table->double('precio_unitario')
                    ->comment('Precio unitario del producto en este detalle');

                $table->timestamp('creado_en')
                    ->nullable()
                    ->useCurrent()
                    ->comment('Fecha de creación del registro');

                $table->timestamp('actualizado_en')
                    ->nullable()
                    ->useCurrentOnUpdate()
                    ->comment('Fecha de la última actualización del registro');
            }
        );
    }

    /**
     * Revierte la migración eliminando la tabla 'detalle'.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle');
    }
};
