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

        Schema::create('pagos_tupa', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pagos_id')->constrained()->onDelete('cascade'); // Elimina registros relacionados
            $table->foreignId('tupa_id')->constrained()->onDelete('cascade'); // Opcional, si deseas eliminar otorgantes relacionados
            $table->unsignedInteger("cantidad");
            $table->decimal('Subtotal',8,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('pagos_tupa');
    }
};
