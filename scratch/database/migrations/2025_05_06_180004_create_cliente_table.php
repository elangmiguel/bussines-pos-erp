<?php

/**
 * Migración para crear la tabla 'cliente' en la base de datos.
 *
 * Define la estructura de la tabla que almacena los clientes del sistema,
 * vinculados a registros en la tabla 'persona'.
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
     * Ejecuta la migración: crea la tabla 'cliente' con sus columnas
     * y claves foráneas.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'cliente', function (Blueprint $table) {
                $table->id()
                    ->comment('Identificador único del cliente');

                $table->foreignId('persona_id')
                    ->constrained('persona')
                    ->cascadeOnDelete()
                    ->comment('ID de la persona asociada al cliente');

                $table->string('tipo', 50)
                    ->default('regular')
                    ->comment('Tipo de cliente (Ej: regular, VIP)');

                $table->boolean('activo')
                    ->default(true)
                    ->comment('Indica si el cliente está activo');

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
     * Revierte la migración: elimina la tabla 'cliente' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente');
    }
};
