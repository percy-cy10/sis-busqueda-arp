<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;
    protected $table = 'solicituds';
    protected $with = ['solicitante','ubigeo','notario','SubSerie','tupa','user'];

    public $timestamps = false;
    protected $fillable = [
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
        'area_id',
        'user_id',
        'created_at',
        'updated_at'
    ];
    public function notario()
    {
        return $this->belongsTo(Notario::class, 'notario_id');
    }
    public function SubSerie()
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
        return $this->belongsTo(Ubigeo::class, 'ubigeo_cod','codigo');
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
}
