<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'role',
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password'  => 'hashed',
    ];

    // ─── Relasi ke Profil per Role ────────────────────────────

    /**
     * Profil Orang Tua.
     * Digunakan untuk ownership check: memastikan orang tua
     * hanya bisa mengakses data anak mereka sendiri.
     */
    public function parentGuardian(): HasOne
    {
        return $this->hasOne(ParentGuardian::class);
    }

    /**
     * Profil Terapis.
     * Digunakan untuk membatasi terapis agar hanya bisa melihat
     * jadwal miliknya dan mengisi hasil terapi sesi miliknya.
     */
    public function therapist(): HasOne
    {
        return $this->hasOne(Therapist::class);
    }

    /**
     * Profil Dokter.
     * Digunakan untuk mengaitkan rekam medis dengan dokter yang mengisi.
     */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    /**
     * Profil Psikolog.
     */
    public function psychologist(): HasOne
    {
        return $this->hasOne(Psychologist::class);
    }

    // ─── Helper ───────────────────────────────────────────────

    public function hasRole(string $roleSlug): bool
    {
        return $this->role === $roleSlug;
    }

    public function hasAnyRole(array $roleSlugs): bool
    {
        return in_array($this->role, $roleSlugs);
    }
}