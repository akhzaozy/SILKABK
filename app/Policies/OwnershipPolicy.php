<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\MedicalRecord;

/**
 * OwnershipPolicy
 *
 * Memastikan Orang Tua hanya bisa mengakses data yang berkaitan
 * dengan anak mereka sendiri, bukan data pasien lain.
 *
 * Super Admin selalu diizinkan (via Gate::before di AuthServiceProvider).
 */
class OwnershipPolicy
{
    // ─── Helper ───────────────────────────────────────────────

    /**
     * Cari guardian_id milik user yang sedang login.
     * Return null jika user bukan orang tua atau belum punya profil guardian.
     */
    private function getGuardianId(User $user): ?int
    {
        return $user->parentGuardian?->id;
    }

    // ─── Patient ──────────────────────────────────────────────

    /**
     * Apakah pasien ini adalah anak dari user yang login?
     */
    public function viewPatient(User $user, Patient $patient): bool
    {
        $guardianId = $this->getGuardianId($user);
        return $guardianId && $patient->guardian_id === $guardianId;
    }

    // ─── Schedule ─────────────────────────────────────────────

    /**
     * Orang tua boleh MELIHAT jadwal anak sendiri.
     * Orang tua TIDAK BOLEH mengubah atau menghapus jadwal siapapun.
     */
    public function viewSchedule(User $user, Schedule $schedule): bool
    {
        $guardianId = $this->getGuardianId($user);
        if (! $guardianId) return false;

        return $schedule->patient?->guardian_id === $guardianId;
    }

    /**
     * Orang tua tidak bisa memodifikasi jadwal apapun.
     * Ini digunakan untuk menolak secara eksplisit di controller.
     */
    public function modifySchedule(User $user, Schedule $schedule): bool
    {
        return false; // Orang tua tidak pernah boleh memodifikasi jadwal
    }

    // ─── Payment ──────────────────────────────────────────────

    /**
     * Orang tua hanya boleh melihat pembayaran untuk anak sendiri.
     */
    public function viewPayment(User $user, Payment $payment): bool
    {
        $guardianId = $this->getGuardianId($user);
        if (! $guardianId) return false;

        return $payment->patient?->guardian_id === $guardianId;
    }

    // ─── Registration ─────────────────────────────────────────

    /**
     * Orang tua hanya bisa melihat/membuat pendaftaran untuk anak sendiri.
     */
    public function viewRegistration(User $user, Registration $registration): bool
    {
        $guardianId = $this->getGuardianId($user);
        if (! $guardianId) return false;

        return $registration->patient?->guardian_id === $guardianId;
    }

    // ─── Medical Record ───────────────────────────────────────

    /**
     * Orang tua hanya boleh melihat rekam medis anak sendiri (read-only).
     */
    public function viewMedicalRecord(User $user, MedicalRecord $record): bool
    {
        $guardianId = $this->getGuardianId($user);
        if (! $guardianId) return false;

        return $record->patient?->guardian_id === $guardianId;
    }
}