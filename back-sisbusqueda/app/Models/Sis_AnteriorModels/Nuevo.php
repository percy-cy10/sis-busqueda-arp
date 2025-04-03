<?php

namespace App\Models\Sis_AnteriorModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nuevo extends Model
{
    use HasFactory;
    protected $table = 'nuevo';
    protected $fillable = [
        'notario',
        'lugar',
        'subserie',
        'fecha',
        'bien',
        'protocolo',
        'nescritura',
        'folio',
        'cfolio',
        'observacion',
        'trabajador',
        'otorgantes',
        'favorecidos',
        'juriotorgantes',
        'jurifavorecidos',
    ];
}
