<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias_gasto');
            $table->string('descripcion', 300);
            $table->decimal('monto', 15, 2);
            $table->decimal('iva', 15, 2)->default(0);
            $table->date('fecha');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->foreignId('medio_pago_id')->constrained('medios_pago');
            $table->foreignId('user_id')->constrained('users');
            $table->string('numero_documento', 50)->nullable();
            $table->string('comprobante', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
