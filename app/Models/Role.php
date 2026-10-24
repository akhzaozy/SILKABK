<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'slug'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Helper: cek apakah role ini adalah slug tertentu
    public function hasSlug(string $slug): bool
    {
        return $this->slug === $slug;
    }
}