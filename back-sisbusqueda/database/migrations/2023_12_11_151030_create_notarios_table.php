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
        Schema::create('notarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombres')->nullable();
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->string('nombre_completo');
            $table->smallInteger('año_inicio')->nullable();
            $table->smallInteger('año_final')->nullable();
            $table->char('ubigeo_cod', 6)->nullable();
            $table->foreign('ubigeo_cod')->references('codigo')->on('ubigeos')->nullOnDelete();
            // $table->foreignId('ubigeo_id')->constrained('ubigeos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notarios');
    }
};
