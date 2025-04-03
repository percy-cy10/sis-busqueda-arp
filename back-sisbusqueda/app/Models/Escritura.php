<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Escritura extends Model
{
    use HasFactory;
    protected $table = 'escrituras';
    protected $fillable = [
        'bien',
        'subserie_id',
        'anio',
        'mes',
        'dia',
        'fecha',
        'cod_escritura',
        'cod_folioInicial',
        'cod_folioFinal',
        'libro_id',
        'observaciones',
    ];
    public function subSerie()
    {
        return $this->belongsTo(SubSerie::class, 'subserie_id');
    }
    public function libro()
    {
        return $this->belongsTo(Libro::class, 'libro_id');
    }

    public function favorecidos()
    {
        return $this->belongsToMany(Favorecido::class);
    }
    public function otorgantes()
    {
        return $this->belongsToMany(Otorgante::class);
    }
}
