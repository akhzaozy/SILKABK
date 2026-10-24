<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    protected $fillable = [
        'patient_id',
        'therapy_type_id',
        'schedule_id',
        'duration_hours',
        'patient_type',
        'registration_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'duration_hours'    => 'decimal:1',
    ];

    // ─── Relasi ───────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function therapyType(): BelongsTo
    {
        return $this->belongsTo(TherapyType::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
