<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->enum('tipo', [
                'entrada_compra', 'salida_venta', 'ajuste_positivo',
                'ajuste_negativo', 'devolucion_venta', 'devolucion_compra', 'traslado',
            ]);
            $table->decimal('cantidad', 15, 3);
            $table->decimal('stock_anterior', 15, 3);
            $table->decimal('stock_nuevo', 15, 3);
            $table->decimal('costo_unitario', 15, 2)->nullable();
            $table->string('referencia_tipo', 50)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->text('motivo')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
