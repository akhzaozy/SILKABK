<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    protected $fillable = [
        'patient_id',
        'therapist_id',
        'doctor_id',
        'psychologist_id',
        'professional_type',
        'therapy_type_id',
        'schedule_date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'schedule_date' => 'date:Y-m-d',
    ];

    // ─── Relasi ───────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(Therapist::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function psychologist(): BelongsTo
    {
        return $this->belongsTo(Psychologist::class);
    }

    public function therapyType(): BelongsTo
    {
        return $this->belongsTo(TherapyType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function therapyResult(): HasOne
    {
        return $this->hasOne(TherapyResult::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function registration(): HasOne
    {
        return $this->hasOne(Registration::class);
    }

    // Helper: nama tenaga profesional
    public function getProfessionalNameAttribute(): string
    {
        return match ($this->professional_type) {
            'dokter'   => 'Dr. ' . ($this->doctor?->name ?? '—'),
            'psikolog' => 'Psi. ' . ($this->psychologist?->nama ?? '—'),
            default    => $this->therapist?->name ?? '—',
        };
    }
}
