<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TherapyType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price_per_hour',
    ];

    protected $casts = [
        'price_per_hour' => 'decimal:2',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
