<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resoluciones_dian', function (Blueprint $table) {
            $table->id();
            $table->string('numero_resolucion', 50);
            $table->date('fecha_resolucion');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('prefijo', 10)->nullable();
            $table->unsignedInteger('rango_desde');
            $table->unsignedInteger('rango_hasta');
            $table->unsignedInteger('numero_actual')->default(0);
            $table->string('clave_tecnica', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resoluciones_dian');
    }
};
