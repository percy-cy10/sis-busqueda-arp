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

    public function pagos()
    {
        return $this->belongsToMany(Pago::class, 'pagos_tupa', 'tupa_id', 'pagos_id')
            ->withPivot('cantidad', 'Subtotal')
            ->withTimestamps();
    }
}
