<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->string('tipo_tramite')->nullable();

            // Campos para expedientes
            $table->string('tipo_expediente')->nullable();
            $table->string('materia_proceso')->nullable();
            $table->string('demandante')->nullable();
            $table->string('demandado')->nullable();
            $table->string('causante')->nullable();
            $table->string('juzgado')->nullable();
            $table->string('secretario')->nullable();

            // Campos para partidas
            $table->string('tipo_partida')->nullable();
            $table->string('nombre_fallecido')->nullable();
            $table->string('nombre_nacido')->nullable();
            $table->string('nombre_esposo')->nullable();
            $table->string('nombre_esposa')->nullable();

            // Campos para ENACE
            $table->string('contrato_privado')->nullable();
            $table->string('otorgante_enace')->nullable();
            $table->string('favorecido_enace')->nullable();
            $table->string('institucion_enace')->nullable();

            // Campos para IMPUESTO
            $table->string('causante_impuesto')->nullable();
            $table->string('direccion_impuesto')->nullable();

            // Campos para PROCESOS
            $table->string('proceso_de')->nullable();
            $table->string('en_contra_de')->nullable();
            $table->string('causante_proceso')->nullable();
            $table->string('notario_proceso')->nullable();

            // Campos para MINISTERIO PÚBLICO
            $table->string('tipo_expediente_mp')->nullable();
            $table->string('caso_mp')->nullable();
            $table->string('area_mp')->nullable();
            $table->string('materia_mp')->nullable();
            $table->string('agraviado_mp')->nullable();
            $table->string('imputado_mp')->nullable();
            $table->string('fiscalia_mp')->nullable();
            $table->string('numero_caso_mp')->nullable();
            $table->string('numero_paquete_mp')->nullable();

            // Campos para escrituras (adicionales)
            $table->string('sescritura')->nullable();
            $table->string('sprotocolo')->nullable();
            $table->string('sfolio')->nullable();
        });
    }

    public function down()
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_tramite',
                'tipo_expediente',
                'materia_proceso',
                'demandante',
                'demandado',
                'causante',
                'juzgado',
                'secretario',
                'tipo_partida',
                'nombre_fallecido',
                'nombre_nacido',
                'nombre_esposo',
                'nombre_esposa',
                'contrato_privado',
                'otorgante_enace',
                'favorecido_enace',
                'institucion_enace',
                'causante_impuesto',
                'direccion_impuesto',
                'proceso_de',
                'en_contra_de',
                'causante_proceso',
                'notario_proceso',
                'tipo_expediente_mp',
                'caso_mp',
                'area_mp',
                'materia_mp',
                'agraviado_mp',
                'imputado_mp',
                'fiscalia_mp',
                'numero_caso_mp',
                'numero_paquete_mp',
                'sescritura',
                'sprotocolo',
                'sfolio'
            ]);
        });
    }
};
