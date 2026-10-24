<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Therapist extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'specialization',
        'therapy_type_id',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Relasi ───────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function therapyType(): BelongsTo
    {
        return $this->belongsTo(TherapyType::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function therapyResults(): HasMany
    {
        return $this->hasMany(TherapyResult::class);
    }
}
