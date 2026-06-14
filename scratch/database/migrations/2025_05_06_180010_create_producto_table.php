<?php

/**
 * Migración para crear la tabla 'producto' en la base de datos.
 *
 * Define la estructura de la tabla que almacena información sobre productos,
 * incluyendo nombre, precio, stock, categoría y código de barras.
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
     * Ejecuta la migración: crea la tabla 'producto' con sus columnas y comentarios.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'producto', function (Blueprint $table) {
                $table->id()
                    ->comment('Identificador único del producto');

                $table->string('nombre', 100)
                    ->comment('Nombre del producto');

                $table->double('precio')
                    ->comment('Precio del producto');

                $table->integer('stock')
                    ->comment('Cantidad de unidades disponibles en stock');

                $table->string('categoria', 50)
                    ->comment('Categoría del producto');

                $table->string('codigo_barras', 50)
                    ->unique()
                    ->comment('Código de barras único del producto');

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
     * Revierte la migración: elimina la tabla 'producto' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('producto');
    }
};
