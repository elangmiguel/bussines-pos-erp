<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('codigo_barras', 50)->nullable()->unique();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_producto')->nullOnDelete();
            $table->foreignId('unidad_medida_id')->constrained('unidades_medida');
            $table->foreignId('tarifa_iva_id')->constrained('tarifas_iva');
            $table->decimal('precio_compra', 15, 2)->default(0);
            $table->decimal('precio_venta', 15, 2);
            $table->decimal('precio_mayorista', 15, 2)->nullable();
            $table->decimal('stock_actual', 15, 3)->default(0);
            $table->decimal('stock_minimo', 15, 3)->default(0);
            $table->decimal('stock_maximo', 15, 3)->nullable();
            $table->string('imagen', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
