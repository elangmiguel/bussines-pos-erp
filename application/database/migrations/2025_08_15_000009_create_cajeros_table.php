<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajeros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->unique()->constrained('colaboradores')->cascadeOnDelete();
            $table->string('codigo', 20)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajeros');
    }
};
