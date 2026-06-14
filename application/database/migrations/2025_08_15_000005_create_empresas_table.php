<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social', 200);
            $table->string('nit', 15)->unique();
            $table->char('digito_verificacion', 1);
            $table->enum('regimen_tributario', ['responsable_iva', 'no_responsable_iva']);
            $table->enum('tipo_empresa', ['propia', 'cliente', 'proveedor', 'mixta']);
            $table->foreignId('representante_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->string('email', 150)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->char('pais', 2)->default('CO');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
