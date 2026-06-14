<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('logo', 255)->nullable();
            $table->char('moneda', 3)->default('COP');
            $table->string('zona_horaria', 50)->default('America/Bogota');
            $table->string('impresora_defecto', 100)->nullable();
            $table->smallInteger('dias_vencimiento_cred')->default(30);
            $table->string('prefijo_nota_credito', 10)->default('NC');
            $table->string('prefijo_nota_debito', 10)->default('ND');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
