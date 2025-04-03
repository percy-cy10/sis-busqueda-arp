<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;
    protected $table = 'pagos';
    protected $fillable = [
        'solicitud_id',
        'pago_busqueda',
        'pago_verificacion',
        'cantidad_folio',
        'pago_folio',
        'cantidad_fotocopia',
        'pago_fotocopia',
        'total',
        'created_at',
        'updated_at',
    ];
    public $timestamps = false;
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }
}
