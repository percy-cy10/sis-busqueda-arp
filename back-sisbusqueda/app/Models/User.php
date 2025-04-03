<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
    ];
    protected $guard_name = 'api';
    protected $with = ['area'];

    // public function validateForPassportPasswordGrant($password)
    // {
    //     return Hash::check($password, $this->password);
    // }
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

    //
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    // metodo para obtener los registros de tabla user_has_areas
    public function userHasAreas()
    {
        // Relación uno a muchos con UserHasArea
        return $this->hasMany(UserHasArea::class, 'user_id', 'id');
    }

    public function areas()
    {
        return $this->hasMany(Area::class); // Relación con el modelo Area
    }
}
