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
        Schema::create('escrituras', function (Blueprint $table) {
            $table->id();
            $table->string('bien');
            $table->unsignedBigInteger('subserie_id')->nullable();
            $table->foreign('subserie_id')->references('id')->on('sub_series')->nullOnDelete();
            $table->year('anio')->nullable();
            $table->unsignedTinyInteger('mes')->nullable();
            $table->unsignedTinyInteger('dia')->nullable();
            $table->date('fecha')->nullable();
            $table->string('cod_escritura');
            $table->string('cod_folioInicial');
            $table->string('cod_folioFinal');
            $table->unsignedBigInteger('libro_id')->nullable();
            $table->foreign('libro_id')->references('id')->on('libros')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escrituras');
    }
};
