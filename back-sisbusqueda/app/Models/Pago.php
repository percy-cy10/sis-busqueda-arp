<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    // protected $table = 'pagos';

    protected $fillable = [
        'solicitud_id',
        'boleta_id',
        'tipo_documento',
        'num_documento',
        'nombre_completo',
        'total',
        'user_id',
        'estado', // <-- agregado
        'created_by', // Agregar
        'updated_by', // Agregar
        'monto_pagado',      // nuevo
        'vuelto',           // nuevo
        'forma_pago_id',    // nuevo
        'condicion_pago_id', // nuevo
        'created_at',
        'updated_at',
    ];

    public $timestamps = true; // <-- corregido

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tupas()
    {
        return $this->belongsToMany(Tupa::class, 'pagos_tupa', 'pagos_id', 'tupa_id')
            ->withPivot('cantidad', 'Subtotal')
            ->withTimestamps();
    }

    // Accesor para mostrar el estado en texto
    public function getEstadoTextoAttribute()
    {
        if ($this->estado === null) return 'Anulado';
        return $this->estado ? 'Pendiente' : 'Pagado';
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizador()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
