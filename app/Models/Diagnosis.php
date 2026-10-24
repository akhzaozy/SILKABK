<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnosis extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'diagnosis_date',
        'symptoms',
        'diagnosis_name',
        'icd_code',
        'treatment',
        'notes',
    ];

    protected $casts = [
        'diagnosis_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
