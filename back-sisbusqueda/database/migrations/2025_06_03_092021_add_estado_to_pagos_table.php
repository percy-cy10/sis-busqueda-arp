<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Añade la columna como nullable con default=1
        Schema::table('pagos', function (Blueprint $table) {
            $table->boolean('estado')
                  ->default(1)
                  ->nullable();               // por ahora permitimos NULL
        });

        // // 2) Limpia los registros existentes: asigna 1 donde esté NULL
        // DB::table('pagos')
        //     ->whereNull('estado')
        //     ->update(['estado' => 1]);

        // // 3) Ahora que no hay NULLs, modifica la columna para que sea NOT NULL
        // Schema::table('pagos', function (Blueprint $table) {
        //     $table->boolean('estado')
        //           ->default(1)
        //           ->nullable(false)
        //           ->change();               // require doctrine/dbal
        // });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
