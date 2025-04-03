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
        Schema::create('registro_verificacions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('RB_id_derivado')->nullable();
            $table->foreign('RB_id_derivado')->references('id')->on('registro_busquedas')->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->boolean('estado');
            $table->text('observaciones')->nullable();

            // pendiente los datos de cantidad de copias, costos, etc
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_verificacions');
    }
};
