<?php

namespace App\Models\Sis_AnteriorModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anterior2 extends Model
{
    use HasFactory;
    protected $table = 'anterior_2';
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
