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
            $table->foreignId('solicitud_id')->nullable()->constrained('solicituds')->nullOnDelete();
            $table->string('tipo_documento'); // DNI o RUC
            $table->string('num_documento');
            $table->string('nombre_completo')->nullable(); // sin espacios
            $table->decimal('total', 8, 2);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
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
