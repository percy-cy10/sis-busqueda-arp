<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'area_id',
        'estado',
        'dni',        // ✅ Nuevo campo agregado
        'nivel_id',      // ✅ Nuevo campo agregado
    ];

    protected $guard_name = 'api';
    protected $with = ['area'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function nivel()
{
    return $this->belongsTo(Nivel::class);
}


    // Para Passport (solo usuarios activos)
    public function findForPassport($username)
    {
        return $this->where('email', $username)
                    ->where('estado', 1)
                    ->first();
    }
}
