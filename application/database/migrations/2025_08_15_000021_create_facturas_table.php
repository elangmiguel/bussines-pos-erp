<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero');
            $table->string('prefijo', 10)->nullable();
            $table->string('numero_completo', 30)->unique();
            $table->foreignId('resolucion_id')->constrained('resoluciones_dian');
            $table->foreignId('turno_caja_id')->constrained('turnos_caja');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('fecha');
            $table->date('fecha_vencimiento')->nullable();
            $table->enum('tipo_pago', ['contado', 'credito'])->default('contado');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('descuento_global', 15, 2)->default(0);
            $table->decimal('base_iva_0', 15, 2)->default(0);
            $table->decimal('base_iva_5', 15, 2)->default(0);
            $table->decimal('base_iva_19', 15, 2)->default(0);
            $table->decimal('iva_5', 15, 2)->default(0);
            $table->decimal('iva_19', 15, 2)->default(0);
            $table->decimal('inc', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->enum('estado', ['borrador', 'emitida', 'anulada'])->default('emitida');
            $table->string('cufe', 255)->nullable();
            $table->text('qr_data')->nullable();
            $table->longText('xml_dian')->nullable();
            $table->enum('estado_dian', ['pendiente', 'aceptada', 'rechazada'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
