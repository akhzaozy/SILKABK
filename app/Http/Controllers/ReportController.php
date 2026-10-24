<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->endOfMonth()->toDateString();

        // Ringkasan periode
        $summary = [
            'totalPatients'       => Patient::where('is_active', true)->count(),
            'totalRegistrations'  => Registration::whereBetween('registration_date', [$from, $to])->count(),
            'pendingRegistrations' => Registration::where('status', 'pending')->count(),
            'totalRevenue'        => (float) Payment::where('status', 'paid')
                ->whereBetween('payment_date', [$from, $to])
                ->sum('amount'),
            'completedSchedules'  => Schedule::where('status', 'done')
                ->whereBetween('schedule_date', [$from, $to])
                ->count(),
            'availableSlots'      => Schedule::where('status', 'available')
                ->where('schedule_date', '>=', today())
                ->count(),
        ];

        // Pendaftaran per status (periode)
        $registrationsByStatus = Registration::whereBetween('registration_date', [$from, $to])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Pendapatan 6 bulan terakhir (PostgreSQL: TO_CHAR)
        $revenueByMonth = Payment::where('status', 'paid')
            ->selectRaw("TO_CHAR(payment_date, 'YYYY-MM') as month, SUM(amount) as total, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        // Jadwal per tipe profesional (periode)
        $schedulesByType = Schedule::whereBetween('schedule_date', [$from, $to])
            ->select('professional_type', 'status', DB::raw('count(*) as total'))
            ->groupBy('professional_type', 'status')
            ->get()
            ->groupBy('professional_type');

        // Pendaftaran per bulan (6 bulan terakhir)
        $registrationsByMonth = Registration::selectRaw("TO_CHAR(registration_date, 'YYYY-MM') as month, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        return Inertia::render('Reports/Index', [
            'summary'              => $summary,
            'registrationsByStatus' => $registrationsByStatus,
            'revenueByMonth'       => $revenueByMonth,
            'schedulesByType'      => $schedulesByType,
            'registrationsByMonth' => $registrationsByMonth,
            'filters'              => ['from' => $from, 'to' => $to],
        ]);
    }
}
