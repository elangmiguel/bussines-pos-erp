<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fondos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->enum('tipo', ['caja', 'digital', 'banco', 'reserva', 'otro']);
            $table->foreignId('medio_pago_id')->nullable()->constrained('medios_pago')->nullOnDelete();
            $table->decimal('saldo_actual', 15, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fondos');
    }
};
