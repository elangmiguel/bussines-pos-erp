<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_credito', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero');
            $table->string('numero_completo', 30)->unique();
            $table->foreignId('factura_id')->constrained('facturas');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('fecha');
            $table->enum('motivo', ['devolucion', 'descuento', 'anulacion', 'error']);
            $table->text('descripcion')->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('iva', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->enum('estado', ['emitida', 'aplicada', 'anulada'])->default('emitida');
            $table->string('cufe', 255)->nullable();
            $table->longText('xml_dian')->nullable();
            $table->enum('estado_dian', ['pendiente', 'aceptada', 'rechazada'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_credito');
    }
};
