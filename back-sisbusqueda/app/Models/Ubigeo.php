<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ubigeo extends Model
{
    use HasFactory;
    protected $table = 'ubigeos';
    protected $fillable = [
        'codigo',
        'tipo',
        'cod_dep',
        'cod_prov',
        'cod_dist',
        'nombre',
    ];

    protected $primaryKey = 'codigo';

    public function notarios()
    {
        return $this->hasMany(Notario::class, 'ubigeo_cod');
    }
    public function solicitud()
    {
        return $this->hasMany(Solicitud::class, 'ubigeo_cod');
    }
    public function solicitante()
    {
        return $this->hasMany(Solicitante::class, 'ubigeo_cod');
    }
}
