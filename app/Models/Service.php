<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
protected $fillable = ['doctor_id', 'name', 'price', 'duration_minutes', 'image', 'is_active'];   
 protected $casts = [
        'name' => 'array', // لجعل البيانات تتعامل كمصفوفة تلقائياً
        'is_active' => 'boolean'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointments()
{
    return $this->hasMany(Appointment::class);
}
}