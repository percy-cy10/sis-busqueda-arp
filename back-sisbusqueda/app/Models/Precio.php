<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Precio extends Model
{
    use HasFactory;
    // protected $table = 'precios';
    protected $fillable = [
        'monto',
        'vigente',
    ];
    // public function solicitud()
    // {
    //     return $this->hasMany(Solicitud::class, 'precio_id');
    // }
}
