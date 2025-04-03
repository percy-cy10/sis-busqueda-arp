<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notario extends Model
{
    use HasFactory;

    protected $with = ['ubigeo'];
    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'nombre_completo',
        'año_inicio',
        'año_final',
        'ubigeo_cod',
    ];


    public function ubigeo()
    {
        return $this->belongsTo(Ubigeo::class, 'ubigeo_cod','codigo');
    }
    public function solicitud()
    {
        return $this->hasMany(Solicitud::class, 'notario_id');
    }
    public function libros()
    {
        return $this->hasMany(Libro::class, 'notario_id');
    }
}
