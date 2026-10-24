<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Payment;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    // ─── US-07 & US-08: Admin — catat & riwayat pembayaran ───

    public function index(Request $request): Response
    {
        $payments = Payment::with(['patient', 'schedule.therapyType', 'createdBy'])
            ->when($request->search, fn ($q) =>
                $q->where('payment_code', 'like', "%{$request->search}%")
                  ->orWhereHas('patient', fn ($p) =>
                      $p->where('name', 'like', "%{$request->search}%")
                  )
            )
            ->when($request->status, fn ($q) =>
                $q->where('status', $request->status)
            )
            ->when($request->input('method'), fn ($q) =>
                $q->where('payment_method', $request->input('method'))
            )
            ->when($request->date_from, fn ($q) =>
                $q->whereDate('payment_date', '>=', $request->date_from)
            )
            ->when($request->date_to, fn ($q) =>
                $q->whereDate('payment_date', '<=', $request->date_to)
            )
            ->orderBy('payment_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Total untuk laporan ringkas
        $summary = [
            'total_paid'   => Payment::where('status', 'paid')->sum('amount'),
            'total_unpaid' => Payment::where('status', 'unpaid')->sum('amount'),
            'count_paid'   => Payment::where('status', 'paid')->count(),
            'count_unpaid' => Payment::where('status', 'unpaid')->count(),
        ];

        return Inertia::render('Payments/Index', [
            'payments' => $payments,
            'summary'  => $summary,
            'filters'  => $request->only(['search', 'status', 'method', 'date_from', 'date_to']),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Payments/Create', [
            'patients'       => Patient::where('is_active', true)
                ->get(['id', 'name', 'medical_record_number']),
            // Opsional: jika dibuat dari jadwal tertentu
            'selected_schedule' => $request->schedule_id
                ? Schedule::with(['patient', 'therapyType'])
                    ->find($request->schedule_id)
                : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id'     => ['required', 'exists:patients,id'],
            'schedule_id'    => ['nullable', 'exists:schedules,id'],
            'amount'         => ['required', 'numeric', 'min:0'],
            'payment_date'   => ['required', 'date'],
            'payment_method' => ['required', 'in:Cash,Transfer'],
            'status'         => ['required', 'in:unpaid,paid'],
            'notes'          => ['nullable', 'string'],
        ], [
            'patient_id.required'     => 'Pasien wajib dipilih.',
            'amount.required'         => 'Jumlah pembayaran wajib diisi.',
            'amount.min'              => 'Jumlah pembayaran tidak boleh negatif.',
            'payment_date.required'   => 'Tanggal pembayaran wajib diisi.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
        ]);

        Payment::create([
            ...$validated,
            'created_by' => Auth::id(),
            // payment_code di-generate otomatis via model boot
        ]);

        return redirect()->route('payments.index')
            ->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function show(Payment $payment): Response
    {
        return Inertia::render('Payments/Show', [
            'payment' => $payment->load(['patient', 'schedule.therapyType', 'createdBy']),
        ]);
    }

    public function edit(Payment $payment): Response
    {
        return Inertia::render('Payments/Edit', [
            'payment'  => $payment->load(['patient', 'schedule']),
            'patients' => Patient::where('is_active', true)
                ->get(['id', 'name', 'medical_record_number']),
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'patient_id'     => ['required', 'exists:patients,id'],
            'schedule_id'    => ['nullable', 'exists:schedules,id'],
            'amount'         => ['required', 'numeric', 'min:0'],
            'payment_date'   => ['required', 'date'],
            'payment_method' => ['required', 'in:Cash,Transfer'],
            'status'         => ['required', 'in:unpaid,paid'],
            'notes'          => ['nullable', 'string'],
        ]);

        $payment->update($validated);

        return redirect()->route('payments.index')
            ->with('success', 'Data pembayaran berhasil diperbarui.');
    }

    // ─── US-09: Orang Tua — riwayat & bukti pembayaran ───────

    public function indexByParent(Request $request): Response
    {
        $guardian = Auth::user()->parentGuardian;
        abort_unless($guardian, 403, 'Profil orang tua tidak ditemukan.');

        $payments = Payment::with(['patient', 'schedule.therapyType'])
            ->whereHas('patient', fn ($q) =>
                $q->where('guardian_id', $guardian->id)
            )
            ->when($request->status, fn ($q) =>
                $q->where('status', $request->status)
            )
            ->orderBy('payment_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Parent/Payments', [
            'payments' => $payments,
            'filters'  => $request->only(['status']),
        ]);
    }

    public function showByParent(Payment $payment): Response
    {
        // Ownership check: orang tua hanya bisa lihat bayaran anak sendiri
        Gate::authorize('view-payment', $payment);

        return Inertia::render('Parent/PaymentDetail', [
            'payment' => $payment->load(['patient', 'schedule.therapyType', 'createdBy']),
        ]);
    }
}