<?php

/**
 * Migración para crear la tabla pivote 'proveedor_producto' que relaciona
 * proveedores y productos.
 *
 * Define las claves foráneas y la llave primaria compuesta para gestionar
 * la relación muchos a muchos.
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
     * Ejecuta la migración creando la tabla 'proveedor_producto' con claves foráneas
     * y llave primaria compuesta.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'proveedor_producto', function (Blueprint $table) {
                $table->foreignId('proveedor_id')
                    ->constrained('proveedor')
                    ->onDelete('cascade')
                    ->comment('ID del proveedor');

                $table->foreignId('producto_id')
                    ->constrained('producto')
                    ->onDelete('cascade')
                    ->comment('ID del producto');

                $table->primary(['proveedor_id', 'producto_id'])
                    ->comment('Llave primaria compuesta por proveedor y producto');
            }
        );
    }

    /**
     * Revierte la migración eliminando la tabla 'proveedor_producto'.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedor_producto');
    }
};
