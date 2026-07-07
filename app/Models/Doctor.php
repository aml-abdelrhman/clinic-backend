<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = ['user_id', 'specialty_id', 'name', 'bio', 'years_experience', 'rating', 'image', 'languages', 'price_from'];

    protected $casts = [
        'languages' => 'array',
        'name'      => 'array', // سيحول {"ar":"...","en":"..."} إلى مصفوفة
         'bio'       => 'array',
    ];
    

public function doctor()
{
    return $this->hasOne(Doctor::class, 'user_id'); 
}    
    // العلاقة مع التخصص
    public function specialty() {
        return $this->belongsTo(Specialty::class);
    }

    // العلاقة مع الخدمات
    public function services() {
        return $this->hasMany(Service::class);
 
     }
        
     // العلاقة مع الأوقات المتاحة
    public function availabilities() {
        return $this->hasMany(DoctorAvailability::class);
    }

    
    public function appointments()
{
    return $this->hasMany(Appointment::class);
}

public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}