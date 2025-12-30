<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;
    protected $table = 'solicituds';

    protected $with = ['solicitante','ubigeo','notarioProceso','notario','SubSerie','tupa','user'];

    public $timestamps = false;

    protected $fillable = [
        'solicitud_code',
        'notario_id',
        'subserie_id',
        'solicitante_id',
        'otorgantes',
        'favorecidos',
        'anio',
        'mes',
        'dia',
        'fecha',
        'ubigeo_cod',
        'bien',
        'mas_datos',
        'tipo_copia',
        'estado',
        'pago_busqueda',
        'segundo_pago',
        'orden_pago',
        'area_id',
        'nivel_id',
        'user_id',
        'created_at',
        'updated_at',

        // Nuevos campos
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
    ];
    public function notario()
    {
        return $this->belongsTo(Notario::class, 'notario_id');
    }
    public function subSerie()
    {
        return $this->belongsTo(SubSerie::class, 'subserie_id');

    }

    public function solicitante()
    {
        return $this->belongsTo(Solicitante::class, 'solicitante_id');
    }
    public function registroBusqueda()
    {
        return $this->hasOne(RegistroBusqueda::class, 'solicitud_id');
    }

    public function ubigeo()
    {
        return $this->belongsTo(Ubigeo::class, 'ubigeo_cod', 'codigo');
    }


    public function tupa()
    {
        return $this->belongsTo(Tupa::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    public function pagos()
    {
        return $this->hasOne(Pago::class, 'solicitud_id');
    }
    public function nivel()
    {
        return $this->belongsTo(Nivel::class);
    }

    public function notarioProceso()
    {
        return $this->belongsTo(Notario::class, 'notario_proceso');
    }


}
