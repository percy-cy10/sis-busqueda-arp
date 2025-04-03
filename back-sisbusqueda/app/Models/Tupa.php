<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tupa extends Model
{
    use HasFactory;

     public function solicituds()
    {
        return $this->hasMany(Solicitud::class);
    }
}
