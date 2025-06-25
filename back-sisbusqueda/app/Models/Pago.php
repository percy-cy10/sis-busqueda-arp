<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo(\App\Models\User::class, 'user_id');
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
        return $this->estado ? 'Pendiente' : 'Pagado';
    }
}
