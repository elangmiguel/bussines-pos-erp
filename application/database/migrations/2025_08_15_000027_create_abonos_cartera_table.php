<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abonos_cartera', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_cobrar_id')->constrained('cuentas_por_cobrar')->cascadeOnDelete();
            $table->foreignId('medio_pago_id')->constrained('medios_pago');
            $table->decimal('monto', 15, 2);
            $table->timestamp('fecha');
            $table->foreignId('user_id')->constrained('users');
            $table->text('observaciones')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abonos_cartera');
    }
};
