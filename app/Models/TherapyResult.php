<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TherapyResult extends Model
{
    protected $fillable = [
        'schedule_id',
        'therapist_id',
        'patient_id',
        'session_date',
        'activities',
        'patient_response',
        'goals_achieved',
        'next_plan',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    // ─── Relasi ───────────────────────────────────────────────

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(Therapist::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
