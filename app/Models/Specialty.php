<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Specialty extends Model
{
    protected $fillable = ['name', 'slug', 'image', 'description'];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($specialty) {
            $specialty->slug = Str::slug($specialty->name['ar'] ?? $specialty->name['en']);
        });

        static::updating(function ($specialty) {
            if ($specialty->isDirty('name')) {
                $specialty->slug = Str::slug($specialty->name['ar'] ?? $specialty->name['en']);
            }
        });
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }
}