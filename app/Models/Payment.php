<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'patient_id',
        'schedule_id',
        'payment_code',
        'amount',
        'payment_date',
        'payment_method',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    // ─── Auto-generate payment_code ───────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (empty($payment->payment_code)) {
                $payment->payment_code = self::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        // Format: PAY-YYYYMMDD-XXXX (contoh: PAY-20240115-A3F2)
        $date   = now()->format('Ymd');
        $suffix = strtoupper(Str::random(4));
        $code   = "PAY-{$date}-{$suffix}";

        // Pastikan unik
        while (self::where('payment_code', $code)->exists()) {
            $suffix = strtoupper(Str::random(4));
            $code   = "PAY-{$date}-{$suffix}";
        }

        return $code;
    }

    // ─── Relasi ───────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Helper ───────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}