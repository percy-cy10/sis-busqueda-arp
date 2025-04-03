<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Libro extends Model
{
    use HasFactory;
    protected $table = 'libros';
    public $timestamps = false;
    protected $fillable = [
        'protocolo',
        'notario_id',
        'estado',
        'user_id',
        'created_at',
        'updated_at'
    ];
    public function notario()
    {
        return $this->belongsTo(Notario::class, 'notario_id');
    }
    public function escrituras()
    {
        return $this->hasMany(Escritura::class, 'libro_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
