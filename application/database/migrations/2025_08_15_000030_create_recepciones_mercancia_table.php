<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recepciones_mercancia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('ordenes_compra');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('fecha');
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['completa', 'parcial', 'con_novedad']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recepciones_mercancia');
    }
};
