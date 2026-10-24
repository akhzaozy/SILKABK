<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Psychologist extends Model
{
    protected $fillable = [
        'user_id',
        'nama',
        'spesialisasi',
        'nomor_sip',
        'telepon',
    ];

    // ─── Relasi ───────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'psychologist_id');
    }
}
