<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::table('pagos', function (Blueprint $table) {
    //         //
    //     });
    // }

    // /**
    //  * Reverse the migrations.
    //  */
    // public function down(): void
    // {
    //     Schema::table('pagos', function (Blueprint $table) {
    //         //
    //     });
    // }

    public function up()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->unsignedInteger('boleta_id')
                ->unique()
                ->nullable()
                ->after('id')
                ->comment('Número de boleta correlativo');
        });
    }

    public function down()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn('boleta_id');
        });
    }
};
