<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre'
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function solicituds(){
        return $this->hasMany(Solicitud::class);
    }
}
