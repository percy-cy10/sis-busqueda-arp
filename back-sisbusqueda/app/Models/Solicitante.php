<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitante extends Model
{
    use HasFactory;
    protected $table = 'solicitantes';
    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'nombre_completo',
        'asunto',
        'tipo_documento',
        'num_documento',
        'direccion',
        'correo',
        'celular',
        'ubigeo_cod',
    ];
    protected $with = ['ubigeo'];
    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'solicitante_id');
    }
    public function ubigeo()
    {
        return $this->belongsTo(Ubigeo::class, 'ubigeo_cod','codigo');
    }
}
