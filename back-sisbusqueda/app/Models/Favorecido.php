<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorecido extends Model
{
    use HasFactory;
    protected $table = 'favorecidos';
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'nombre_completo',
        'escritura_id',
    ];
    public function escrituras()
    {
        return $this->belongsToMany(Escritura::class);
    }
}
