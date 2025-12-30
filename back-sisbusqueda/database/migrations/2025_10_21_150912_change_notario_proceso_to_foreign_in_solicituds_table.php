<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('solicituds', function (Blueprint $table) {
            // Asegúrate de que la columna ya exista antes de agregar la foreign key
            // Si ya existe, solo definimos la relación
            $table->unsignedBigInteger('notario_proceso')->nullable()->change();

            // Agregamos la relación foránea
            $table->foreign('notario_proceso')
                  ->references('id')
                  ->on('notarios')
                  ->onDelete('set null'); // Si el notario se borra, este campo se pone en NULL
        });
    }

    public function down(): void
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->dropForeign(['notario_proceso']);
        });
    }
};
