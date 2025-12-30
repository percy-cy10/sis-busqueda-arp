<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->decimal('monto_pagado', 10, 2)->nullable();
            $table->decimal('vuelto', 10, 2)->nullable();
            $table->unsignedBigInteger('forma_pago_id')->nullable();
            $table->unsignedBigInteger('condicion_pago_id')->nullable();

            // Estas serán las claves foráneas (puedes agregarlas después)
            // $table->foreign('forma_pago_id')->references('id')->on('forma_pagos');
            // $table->foreign('condicion_pago_id')->references('id')->on('condicion_pagos');
        });
    }

    public function down()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn([
                'monto_pagado',
                'vuelto',
                'forma_pago_id',
                'condicion_pago_id'
            ]);
        });
    }
};
