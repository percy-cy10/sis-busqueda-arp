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
        Schema::create('registro_busquedas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_id')->nullable();
            $table->foreign('solicitud_id')->references('id')->on('solicituds')->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->boolean('estado')->nullable();
            $table->string('cod_protocolo')->nullable();
            $table->string('cod_escritura')->nullable();
            $table->string('cod_folioInicial')->nullable();
            $table->string('cod_folioFinal')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_busquedas');
    }
};
