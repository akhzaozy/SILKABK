<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user  = Auth::user();
        $stats = Cache::remember("dashboard_{$user->id}", 60, fn () => $this->computeStats($user));

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
        ]);
    }

    private function computeStats(object $user): array
    {
        return match ($user->role) {
            'super_admin', 'admin' => $this->adminStats(),
            'orang_tua'            => $this->parentStats($user),
            'terapis'              => $this->therapistStats($user),
            'dokter'               => $this->doctorStats($user),
            'psikolog'             => $this->psychologistStats($user),
            default                => [],
        };
    }

    private function adminStats(): array
    {
        return [
            'totalPatients'        => Patient::where('is_active', true)->count(),
            'todaySchedules'       => Schedule::whereDate('schedule_date', today())->where('status', 'scheduled')->count(),
            'pendingRegistrations' => Registration::where('status', 'pending')->count(),
            'monthlyRevenue'       => Payment::where('status', 'paid')
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
        ];
    }

    private function parentStats(object $user): array
    {
        $guardian = $user->parentGuardian;
        if (!$guardian) return [];

        $patientIds = Patient::where('guardian_id', $guardian->id)->pluck('id');

        return [
            'totalChildren'     => $patientIds->count(),
            'upcomingSchedules' => Schedule::whereIn('patient_id', $patientIds)
                ->where('status', 'scheduled')
                ->where('schedule_date', '>=', today())
                ->count(),
            'unpaidPayments'    => Payment::whereIn('patient_id', $patientIds)
                ->where('status', 'unpaid')
                ->count(),
        ];
    }

    private function therapistStats(object $user): array
    {
        $therapist = $user->therapist;
        if (!$therapist) return [];

        return [
            'todaySchedules' => Schedule::where('therapist_id', $therapist->id)
                ->whereDate('schedule_date', today())
                ->where('status', 'scheduled')
                ->count(),
            'weekSchedules'  => Schedule::where('therapist_id', $therapist->id)
                ->whereBetween('schedule_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('status', '!=', 'cancelled')
                ->count(),
        ];
    }

    private function doctorStats(object $user): array
    {
        $doctor = $user->doctor;
        if (!$doctor) return [];

        $row = MedicalRecord::where('doctor_id', $doctor->id)
            ->selectRaw('
                COUNT(*) AS total,
                COUNT(CASE WHEN kpsp_result IS NOT NULL
                    AND EXTRACT(MONTH FROM visit_date) = ?
                    AND EXTRACT(YEAR FROM visit_date)  = ?
                THEN 1 END) AS monthly
            ', [now()->month, now()->year])
            ->first();

        return [
            'totalRecords' => (int) $row->total,
            'monthlyKpsp'  => (int) $row->monthly,
        ];
    }

    private function psychologistStats(object $user): array
    {
        $psychologist = $user->psychologist;
        if (!$psychologist) return [];

        $row = MedicalRecord::where('psychologist_id', $psychologist->id)
            ->selectRaw('
                COUNT(*) AS total,
                COUNT(CASE WHEN kpsp_result IS NOT NULL
                    AND EXTRACT(MONTH FROM visit_date) = ?
                    AND EXTRACT(YEAR FROM visit_date)  = ?
                THEN 1 END) AS monthly
            ', [now()->month, now()->year])
            ->first();

        return [
            'totalRecords' => (int) $row->total,
            'monthlyKpsp'  => (int) $row->monthly,
        ];
    }
}
