<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHasArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'area_id',
    ];

    // Relación con el modelo Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'id');
    }
}
