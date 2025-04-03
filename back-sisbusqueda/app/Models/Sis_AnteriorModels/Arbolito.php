<?php

namespace App\Models\Sis_AnteriorModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arbolito extends Model
{
    use HasFactory;
    protected $table = 'arbolito';
    protected $fillable = [
        'otorgante',
        'favorecido',
        'fecha',
        'protocolo',
        'escritura',
        'folio',
        'bien',
        'tmp',
    ];
}
