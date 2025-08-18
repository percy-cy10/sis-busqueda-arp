<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('dni', 8)->nullable()->after('email');

            // Renombrar o eliminar columna 'nivel' si existiera como texto (manual)
            // ¡NO se puede hacer dentro del closure! Debes manejarlo por separado si es necesario

            // Agregar campo 'nivel_id' como foráneo
            $table->foreignId('nivel_id')->nullable()->after('dni')->constrained('niveles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['nivel_id']);
            $table->dropColumn('nivel_id');
            $table->dropColumn('dni');
        });
    }
};

