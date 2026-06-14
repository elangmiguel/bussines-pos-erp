<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_fondo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fondo_id')->constrained('fondos');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->decimal('monto', 15, 2);
            $table->text('descripcion');
            $table->string('referencia_tipo', 50)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_fondo');
    }
};
