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
        Schema::create('otorgantes', function (Blueprint $table) {
            // $table->id();
            // $table->string('nombre')->nullable();
            // $table->string('apellido_paterno')->nullable();
            // $table->string('apellido_materno')->nullable();
            // $table->string('nombre_completo');
            // $table->timestamps();
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->string('nombre_completo'); // Eliminar ->nullable() para hacerlo obligatorio
            $table->string('razon_social')->nullable();
            $table->enum('tipo', ['natural', 'juridica']); // Añadir campo "tipo" (obligatorio)
            $table->unsignedBigInteger('user_id'); // Correcto: no nullable
            $table->timestamps();

            // Clave foránea
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otorgantes');
    }
};
