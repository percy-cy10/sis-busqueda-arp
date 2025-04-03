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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicituds')->restrictOnDelete();
            $table->decimal('pago_busqueda',8,2);
            $table->decimal('pago_verificacion',8,2);
            $table->unsignedSmallInteger('cantidad_folio');
            $table->decimal('pago_folio',8,2);
            $table->unsignedSmallInteger('cantidad_fotocopia');
            $table->decimal('pago_fotocopia',8,2);
            $table->decimal('total',8,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
