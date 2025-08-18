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
        Schema::table('solicituds', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('solicituds', 'pago_busqueda')) {
            $table->foreignId('pago_busqueda')->nullable()->constrained('pagos')->nullOnDelete();
            }
            if (!Schema::hasColumn('solicituds', 'segundo_pago')) {
                $table->foreignId('segundo_pago')->nullable()->constrained('pagos')->nullOnDelete();
            }
            if (!Schema::hasColumn('solicituds', 'orden_pago')) {
                $table->foreignId('orden_pago')->nullable()->constrained('pagos')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicituds', function (Blueprint $table) {
            //
            if (Schema::hasColumn('solicituds', 'pago_busqueda')) {
                $table->dropForeign(['pago_busqueda']);
                $table->dropColumn('pago_busqueda');
            }
            if (Schema::hasColumn('solicituds', 'segundo_pago')) {
                $table->dropForeign(['segundo_pago']);
                $table->dropColumn('segundo_pago');
            }
            if (Schema::hasColumn('solicituds', 'orden_pago')) {
                $table->dropForeign(['orden_pago']);
                $table->dropColumn('orden_pago');
            }
        });
    }
};
