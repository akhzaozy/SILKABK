<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentGuardian extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'relationship',
        'phone',
        'address',
    ];

    // ─── Relasi ───────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'guardian_id');
    }
}
