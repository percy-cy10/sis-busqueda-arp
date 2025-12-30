<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('solicituds', function (Blueprint $table) {
            // Hacer otorgantes y favorecidos NULLABLE
            $table->string('otorgantes')->nullable()->change();
            $table->string('favorecidos')->nullable()->change();

            // También hacer nullable otros campos que pueden causar problemas
            $table->string('bien')->nullable()->change();
            $table->string('tipo_copia')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('solicituds', function (Blueprint $table) {
            // Revertir los cambios si es necesario
            $table->string('otorgantes')->nullable(false)->change();
            $table->string('favorecidos')->nullable(false)->change();
            $table->string('bien')->nullable(false)->change();
            $table->string('tipo_copia')->nullable(false)->change();
        });
    }
};
