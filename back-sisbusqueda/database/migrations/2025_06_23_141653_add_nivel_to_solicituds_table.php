<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna 'nivel_id' con clave foránea a la tabla 'solicituds'
     */
    public function up(): void
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->foreignId('nivel')
                ->nullable()
                ->after('area_id') // Insertar después de 'area_id'
                ->constrained('niveles') // Relación con tabla 'niveles'
                ->nullOnDelete(); // Si se elimina el nivel, se pone en null
        });
    }

    /**
     * Reversión: elimina la columna y la clave foránea
     */
    public function down(): void
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->dropForeign(['nivel']);
            $table->dropColumn('nivel');
        });
    }
};
