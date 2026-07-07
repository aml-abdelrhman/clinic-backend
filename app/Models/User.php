<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 'avatar',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function appointments()
{
    return $this->hasMany(Appointment::class, 'patient_id');
}

public function favorites()
{
    return $this->hasMany(Favorite::class, 'patient_id');
}

public function favoriteDoctors()
    {
        return $this->belongsToMany(Doctor::class, 'favorites', 'patient_id', 'doctor_id');
    }
}