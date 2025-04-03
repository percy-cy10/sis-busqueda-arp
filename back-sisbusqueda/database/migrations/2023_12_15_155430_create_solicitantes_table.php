<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('solicitantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombres')->nullable();
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->string('nombre_completo')->nullable();
            $table->string('asunto')->nullable();
            $table->string('tipo_documento');//DNI o RUC
            $table->string('num_documento');
            $table->string('direccion')->nullable();
            $table->string('correo')->nullable();
            $table->string('celular')->nullable();
            $table->char('ubigeo_cod', 6)->nullable();
            $table->foreign('ubigeo_cod')->references('codigo')->on('ubigeos')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitantes');
    }
};
