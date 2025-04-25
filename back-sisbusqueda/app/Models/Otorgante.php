<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otorgante extends Model
{
    use HasFactory;
    protected $table = 'otorgantes';
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'nombre_completo',
        'razon_social',
        'tipo',
        'user_id'
    ];
    protected $casts = [
        'user_id' => 'integer',
        'tipo' => 'string',
    ];
    public function escrituras()
    {
        return $this->belongsToMany(Escritura::class, 'escritura_otorgante');
    }
}
