<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_identificacion', ['CC', 'CE', 'TI', 'PAS', 'NIT', 'RC']);
            $table->string('numero_identificacion', 20);
            $table->char('digito_verificacion', 1)->nullable();
            $table->string('nombres', 100);
            $table->string('apellidos', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->char('pais', 2)->default('CO');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tipo_identificacion', 'numero_identificacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
