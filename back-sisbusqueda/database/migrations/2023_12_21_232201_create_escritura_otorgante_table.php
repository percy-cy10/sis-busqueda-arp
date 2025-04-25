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
        Schema::create('escritura_otorgante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escritura_id')->constrained()->onDelete('cascade'); // Elimina registros relacionados
            $table->foreignId('otorgante_id')->constrained()->onDelete('cascade'); // Opcional, si deseas eliminar otorgantes relacionados
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escritura_otorgante');
    }
};
