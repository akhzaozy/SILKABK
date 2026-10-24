<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Psychologist;
use App\Models\Schedule;
use App\Models\Therapist;
use App\Models\TherapyType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    // Template jadwal praktik per hari (offset dari Senin, slot [start, end])
    private const WEEK_TEMPLATE = [
        0 => [['08:00', '09:00'], ['09:00', '10:00'], ['10:00', '11:00'], ['11:00', '12:00']], // Senin
        1 => [['08:00', '09:00'], ['09:00', '10:00'], ['10:00', '11:00'], ['11:00', '12:00']], // Selasa
        2 => [['08:00', '09:00'], ['09:00', '10:00'], ['10:00', '11:00'], ['11:00', '12:00']], // Rabu
        3 => [['08:00', '09:00'], ['09:00', '10:00'], ['10:00', '11:00'], ['11:00', '12:00']], // Kamis
        4 => [['08:00', '09:00'], ['09:00', '10:00']],                                         // Jumat (08:00-10:30)
        5 => [['08:00', '09:00'], ['09:00', '10:00'], ['10:00', '11:00'], ['11:00', '12:00']], // Sabtu
    ];

    private const PROFESSIONAL_FIELD = [
        'terapis'  => 'therapist_id',
        'dokter'   => 'doctor_id',
        'psikolog' => 'psychologist_id',
    ];

    // ─── Admin: Kelola jadwal & slot tersedia (US-10) ─────────

    public function index(Request $request): Response
    {
        $schedules = Schedule::with(['patient', 'therapist', 'doctor', 'psychologist', 'therapyType'])
            ->when($request->date,              fn ($q) => $q->whereDate('schedule_date', $request->date))
            ->when($request->status,            fn ($q) => $q->where('status', $request->status))
            ->when($request->professional_type, fn ($q) => $q->where('professional_type', $request->professional_type))
            ->orderBy('schedule_date', 'desc')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Schedules/Index', [
            'schedules'  => $schedules,
            'therapists' => Therapist::where('is_active', true)->get(['id', 'name']),
            'filters'    => $request->only(['date', 'status', 'professional_type']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Schedules/Create', [
            'patients'      => Patient::where('is_active', true)->get(['id', 'name', 'medical_record_number']),
            'therapists'    => Therapist::where('is_active', true)->get(['id', 'name', 'specialization']),
            'doctors'       => Doctor::all(['id', 'name', 'specialization']),
            'psychologists' => Psychologist::all(['id', 'nama', 'spesialisasi']),
            'therapyTypes'  => TherapyType::orderBy('name')->get(['id', 'name', 'price_per_hour']),
        ]);
    }

    // Generate otomatis slot 1 jam per minggu sesuai template praktik
    public function generateWeekSlots(Request $request)
    {
        $validated = $request->validate([
            'professional_type' => ['required', 'in:terapis,dokter,psikolog'],
            'therapist_id'      => ['nullable', 'exists:therapists,id'],
            'doctor_id'         => ['nullable', 'exists:doctors,id'],
            'psychologist_id'   => ['nullable', 'exists:psychologists,id'],
            'therapy_type_id'   => ['required', 'exists:therapy_types,id'],
            'week_start'        => ['required', 'date'],
        ]);

        $this->assertProfessionalSelected($validated);

        $monday = Carbon::parse($validated['week_start'])->startOfWeek(Carbon::MONDAY);
        [$created, $skipped] = $this->createSlotsForWeek($monday, $validated);

        return redirect()->route('schedules.index')
            ->with('success', $this->slotSummaryMessage($created, $skipped, $monday));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'professional_type' => ['required', 'in:terapis,dokter,psikolog'],
            'therapist_id'      => ['nullable', 'exists:therapists,id'],
            'doctor_id'         => ['nullable', 'exists:doctors,id'],
            'psychologist_id'   => ['nullable', 'exists:psychologists,id'],
            'patient_id'        => ['nullable', 'exists:patients,id'],
            'therapy_type_id'   => ['required', 'exists:therapy_types,id'],
            'schedule_date'     => ['required', 'date', 'after_or_equal:today'],
            'start_time'        => ['required', 'date_format:H:i'],
            'notes'             => ['nullable', 'string'],
        ], [
            'professional_type.required'   => 'Tipe tenaga profesional harus dipilih.',
            'therapy_type_id.required'     => 'Jenis layanan harus dipilih.',
            'schedule_date.after_or_equal' => 'Tanggal tidak boleh sebelum hari ini.',
        ]);

        $this->assertProfessionalSelected($validated);

        // Setiap slot selalu 1 jam
        $endTime = Carbon::createFromFormat('H:i', $validated['start_time'])->addHour()->format('H:i');
        $validated['end_time'] = $endTime;

        if ($this->hasScheduleConflict($validated['professional_type'], $validated, $validated['schedule_date'], $validated['start_time'], $endTime)) {
            return back()->withErrors(['start_time' => 'Tenaga profesional sudah memiliki jadwal pada waktu tersebut.']);
        }

        $status = $validated['patient_id'] ? 'scheduled' : 'available';

        Schedule::create([
            ...$validated,
            'status'     => $status,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('schedules.index')
            ->with('success', $status === 'available'
                ? 'Slot tersedia berhasil dibuat.'
                : 'Jadwal terapi berhasil dibuat.'
            );
    }

    public function show(Schedule $schedule): Response
    {
        return Inertia::render('Schedules/Show', [
            'schedule' => $schedule->load(['patient', 'therapist', 'doctor', 'psychologist', 'therapyType', 'therapyResult', 'registration']),
        ]);
    }

    public function edit(Schedule $schedule): Response
    {
        return Inertia::render('Schedules/Edit', [
            'schedule'      => $schedule->load(['patient', 'therapist', 'doctor', 'psychologist', 'therapyType']),
            'patients'      => Patient::where('is_active', true)->get(['id', 'name', 'medical_record_number']),
            'therapists'    => Therapist::where('is_active', true)->get(['id', 'name', 'specialization']),
            'doctors'       => Doctor::all(['id', 'name', 'specialization']),
            'psychologists' => Psychologist::all(['id', 'nama', 'spesialisasi']),
            'therapyTypes'  => TherapyType::orderBy('name')->get(['id', 'name', 'price_per_hour']),
        ]);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'professional_type' => ['required', 'in:terapis,dokter,psikolog'],
            'therapist_id'      => ['nullable', 'exists:therapists,id'],
            'doctor_id'         => ['nullable', 'exists:doctors,id'],
            'psychologist_id'   => ['nullable', 'exists:psychologists,id'],
            'patient_id'        => ['nullable', 'exists:patients,id'],
            'therapy_type_id'   => ['required', 'exists:therapy_types,id'],
            'schedule_date'     => ['required', 'date'],
            'start_time'        => ['required', 'date_format:H:i'],
            'notes'             => ['nullable', 'string'],
        ]);

        $this->assertProfessionalSelected($validated);

        $endTime = Carbon::createFromFormat('H:i', $validated['start_time'])->addHour()->format('H:i');
        $validated['end_time'] = $endTime;

        if ($this->hasScheduleConflict($validated['professional_type'], $validated, $validated['schedule_date'], $validated['start_time'], $endTime, $schedule->id)) {
            return back()->withErrors(['start_time' => 'Tenaga profesional sudah memiliki jadwal pada waktu tersebut.']);
        }

        $schedule->update($validated);

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function cancel(Schedule $schedule)
    {
        if ($schedule->status === 'done') {
            return back()->withErrors(['error' => 'Jadwal yang sudah selesai tidak dapat dibatalkan.']);
        }

        $schedule->update(['status' => 'cancelled']);

        return back()->with('success', 'Jadwal berhasil dibatalkan.');
    }

    // ─── Orang Tua: lihat jadwal anak sendiri (US-11) ─────────

    public function indexByParent(Request $request): Response
    {
        $guardian = Auth::user()->parentGuardian;

        abort_unless($guardian, 403, 'Profil orang tua tidak ditemukan.');

        // Ambil semua ID pasien milik guardian ini secara eksplisit
        $patientIds = Patient::where('guardian_id', $guardian->id)->pluck('id');

        $schedules = Schedule::with(['therapist', 'doctor', 'psychologist', 'therapyType', 'patient'])
            ->when($patientIds->isNotEmpty(),
                fn ($q) => $q->whereIn('patient_id', $patientIds),
                fn ($q) => $q->whereRaw('1 = 0') // guardian belum punya pasien terdaftar
            )
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->period === 'upcoming', fn ($q) => $q->where('schedule_date', '>=', today()->toDateString()))
            ->when($request->period === 'past',     fn ($q) => $q->where('schedule_date', '<',  today()->toDateString()))
            ->orderBy('schedule_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Parent/Schedules', [
            'schedules'   => $schedules,
            'patients'    => $guardian->patients()->get(['id', 'name']),
            'filters'     => $request->only(['status', 'period']),
        ]);
    }

    public function showByParent(Schedule $schedule): Response
    {
        Gate::authorize('view-schedule', $schedule);

        return Inertia::render('Parent/ScheduleDetail', [
            'schedule' => $schedule->load(['therapist', 'doctor', 'psychologist', 'therapyType', 'patient', 'therapyResult']),
        ]);
    }

    // ─── Terapis: lihat SEMUA jadwal sendiri termasuk slot kosong ─

    public function indexByTherapist(Request $request): Response
    {
        $therapist = Auth::user()->therapist;

        abort_unless($therapist, 403, 'Profil terapis tidak ditemukan.');

        $schedules = Schedule::with(['patient', 'therapyType', 'therapyResult'])
            ->where('therapist_id', $therapist->id)
            ->when($request->date,   fn ($q) => $q->whereDate('schedule_date', $request->date))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('schedule_date', 'desc')
            ->orderBy('start_time')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Therapist/Schedules', [
            'schedules' => $schedules,
            'filters'   => $request->only(['date', 'status']),
        ]);
    }

    public function showByTherapist(Schedule $schedule): Response
    {
        $therapist = Auth::user()->therapist;

        abort_unless(
            $therapist && $schedule->therapist_id === $therapist->id,
            403,
            'Anda tidak memiliki akses ke jadwal ini.'
        );

        return Inertia::render('Therapist/ScheduleDetail', [
            'schedule' => $schedule->load(['patient', 'therapyType', 'therapyResult']),
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function assertProfessionalSelected(array $validated): void
    {
        $field = self::PROFESSIONAL_FIELD[$validated['professional_type']] ?? null;
        if ($field && empty($validated[$field])) {
            $label = ucfirst($validated['professional_type']);
            abort(back()->withErrors([$field => "{$label} harus dipilih."]));
        }
    }

    private function hasScheduleConflict(
        string $type,
        array  $validated,
        string $date,
        string $start,
        string $end,
        ?int   $excludeId = null,
    ): bool {
        $field = self::PROFESSIONAL_FIELD[$type] ?? null;
        if (!$field) return false;

        return Schedule::where('schedule_date', $date)
            ->where('status', '!=', 'cancelled')
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(fn ($q) => $q->where('start_time', '<', $end)->where('end_time', '>', $start))
            ->where($field, $validated[$field])
            ->exists();
    }

    private function createSlotsForWeek(Carbon $monday, array $validated): array
    {
        $created = $skipped = 0;

        foreach (self::WEEK_TEMPLATE as $offset => $slots) {
            $date = $monday->copy()->addDays($offset);

            foreach ($slots as [$start, $end]) {
                if ($this->hasScheduleConflict($validated['professional_type'], $validated, $date->toDateString(), $start, $end)) {
                    $skipped++;
                    continue;
                }

                Schedule::create([
                    'professional_type' => $validated['professional_type'],
                    'therapist_id'      => $validated['therapist_id']   ?? null,
                    'doctor_id'         => $validated['doctor_id']       ?? null,
                    'psychologist_id'   => $validated['psychologist_id'] ?? null,
                    'therapy_type_id'   => $validated['therapy_type_id'],
                    'schedule_date'     => $date->toDateString(),
                    'start_time'        => $start,
                    'end_time'          => $end,
                    'status'            => 'available',
                    'created_by'        => Auth::id(),
                ]);
                $created++;
            }
        }

        return [$created, $skipped];
    }

    private function slotSummaryMessage(int $created, int $skipped, Carbon $monday): string
    {
        $msg = "Berhasil membuat {$created} slot jadwal untuk minggu " . $monday->format('d/m/Y');
        return $skipped > 0
            ? $msg . " ({$skipped} slot dilewati karena konflik)."
            : $msg . '.';
    }
}
