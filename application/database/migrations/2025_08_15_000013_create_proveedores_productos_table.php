<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->decimal('precio_compra', 15, 2)->nullable();
            $table->smallInteger('tiempo_entrega')->nullable()->comment('días');
            $table->boolean('es_principal')->default(false);
            $table->timestamps();

            $table->unique(['proveedor_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores_productos');
    }
};
