<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->string('solicitud_code', 20)->nullable()->unique();
        });
    }

    public function down()
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->dropColumn('solicitud_code');
        });
    }

};
