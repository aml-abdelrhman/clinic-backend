<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorAvailability extends Model
{
    use HasFactory;

    protected $table = 'doctor_availabilities';

    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
        'day_name',
    ]; 
    
protected $casts = [
    'day_name' => 'array', // لجعل لارافيل يتعامل معه كـ Array تلقائياً
];
    // علاقة عكسية: هذا الوقت يخص طبيب واحد
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}