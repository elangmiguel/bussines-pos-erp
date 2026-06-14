<?php

/**
 * Migración para crear la tabla 'proveedor' en la base de datos.
 *
 * Define la estructura de la tabla que almacena información de proveedores,
 * incluyendo la asociación con persona y empresa (opcional).
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
     * Ejecuta la migración: crea la tabla 'proveedor' con sus columnas y relaciones.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'proveedor', function (Blueprint $table) {
                $table->id()
                    ->comment('Identificador único del proveedor');

                $table->foreignId('persona_id')
                    ->comment('ID de la persona asociada al proveedor')
                    ->constrained('persona')
                    ->cascadeOnDelete();

                $table->foreignId('empresa_id')
                    ->nullable()
                    ->comment('ID de la empresa asociada al proveedor (opcional)')
                    ->constrained('empresa')
                    ->nullOnDelete();

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
     * Revierte la migración: elimina la tabla 'proveedor' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedor');
    }
};
