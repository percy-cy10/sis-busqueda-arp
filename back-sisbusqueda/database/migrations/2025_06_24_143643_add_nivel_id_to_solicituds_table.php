<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->unsignedBigInteger('nivel_id')->nullable()->after('area_id');
            $table->foreign('nivel_id')->references('id')->on('niveles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->dropForeign(['nivel_id']);
            $table->dropColumn('nivel_id');
        });
    }
};
