<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Registration;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationController extends Controller
{
    // ─── Orang Tua: Daftar Layanan (US-05) ───────────────────

    public function createByParent(): Response
    {
        $guardian = Auth::user()->parentGuardian;

        abort_unless($guardian, 403, 'Profil orang tua tidak ditemukan.');

        $patients = Patient::where('guardian_id', $guardian->id)
            ->where('is_active', true)
            ->with('guardian')
            ->get(['id', 'name', 'nik', 'medical_record_number', 'gender', 'birth_date', 'birth_place', 'address', 'guardian_id']);

        [$availableSlots] = $this->fetchAvailableSlots();

        return Inertia::render('Parent/RegisterService', [
            'patients'       => $patients,
            'availableSlots' => $availableSlots,
        ]);
    }

    public function storeByParent(Request $request)
    {
        $guardian = Auth::user()->parentGuardian;

        abort_unless($guardian, 403);

        $patientType = $request->input('patient_type', 'existing');

        $request->validate([
            'schedule_id' => ['required', 'exists:schedules,id'],
            'notes'       => ['nullable', 'string'],
        ], [
            'schedule_id.required' => 'Slot jadwal harus dipilih.',
        ]);

        if ($patientType === 'existing') {
            $request->validate([
                'patient_id' => ['required', 'exists:patients,id'],
            ], ['patient_id.required' => 'Pasien harus dipilih.']);

            $patient = Patient::findOrFail($request->patient_id);
            abort_unless($patient->guardian_id === $guardian->id, 403, 'Anda tidak berwenang mendaftarkan pasien ini.');
            $patientId = $patient->id;
        } else {
            $request->validate([
                'new_name'        => ['required', 'string', 'max:100'],
                'new_nik'         => ['nullable', 'string', 'max:20', 'unique:patients,nik'],
                'new_gender'      => ['required', 'in:L,P'],
                'new_birth_date'  => ['required', 'date', 'before:today'],
                'new_birth_place' => ['required', 'string', 'max:100'],
                'new_address'     => ['required', 'string'],
            ], [
                'new_name.required'        => 'Nama anak harus diisi.',
                'new_nik.unique'           => 'NIK sudah terdaftar di sistem.',
                'new_gender.required'      => 'Jenis kelamin harus dipilih.',
                'new_birth_date.required'  => 'Tanggal lahir harus diisi.',
                'new_birth_date.before'    => 'Tanggal lahir harus sebelum hari ini.',
                'new_birth_place.required' => 'Tempat lahir harus diisi.',
                'new_address.required'     => 'Alamat harus diisi.',
            ]);

            $newPatient = Patient::create([
                'guardian_id' => $guardian->id,
                'name'        => $request->new_name,
                'nik'         => $request->new_nik ?: null,
                'gender'      => $request->new_gender,
                'birth_date'  => $request->new_birth_date,
                'birth_place' => $request->new_birth_place,
                'address'     => $request->new_address,
                'is_active'   => true,
            ]);

            $patientId = $newPatient->id;
        }

        $schedule = Schedule::findOrFail($request->schedule_id);

        abort_unless($schedule->status === 'available', 422, 'Slot jadwal ini sudah tidak tersedia.');

        // Tolak booking jika sesi sudah berakhir (hari lampau, atau hari ini tapi jam sudah lewat end_time)
        $scheduleDate = $schedule->schedule_date;
        $sessionEnded = $scheduleDate->lt(today())
            || ($scheduleDate->isToday() && now()->format('H:i:s') >= $schedule->end_time);

        abort_if($sessionEnded, 422, 'Slot jadwal ini sudah berakhir dan tidak dapat dipesan.');

        $alreadyBooked = Registration::where('schedule_id', $schedule->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        abort_if($alreadyBooked, 422, 'Slot jadwal ini sudah dibooking oleh orang tua lain.');

        Registration::create([
            'patient_id'        => $patientId,
            'therapy_type_id'   => $schedule->therapy_type_id,
            'schedule_id'       => $schedule->id,
            'duration_hours'    => 1.0,
            'patient_type'      => $patientType,
            'registration_date' => now()->toDateString(),
            'status'            => 'pending',
            'notes'             => $request->notes,
        ]);

        if ($patientType === 'new') {
            MedicalRecord::create([
                'patient_id'      => $patientId,
                'visit_date'      => now()->toDateString(),
                'chief_complaint' => $request->notes ?: 'Pendaftaran layanan baru',
                'diagnosis'       => '-',
                'notes'           => 'Rekam medis dibuat otomatis saat pendaftaran pertama.',
            ]);
        }

        return redirect()->route('parent.registrations')
            ->with('success', 'Pendaftaran berhasil diajukan. Menunggu persetujuan admin.');
    }

    public function indexByParent(Request $request): Response
    {
        $guardian = Auth::user()->parentGuardian;

        abort_unless($guardian, 403);

        $registrations = Registration::with(['patient', 'therapyType', 'schedule.therapist', 'schedule.doctor', 'schedule.psychologist'])
            ->whereHas('patient', fn ($q) => $q->where('guardian_id', $guardian->id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('registration_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Parent/Registrations', [
            'registrations' => $registrations,
            'filters'       => $request->only(['status']),
        ]);
    }

    // ─── Admin: Kelola Pendaftaran (US-04) ────────────────────

    public function create(): Response
    {
        $today   = today()->toDateString();
        $nowTime = now()->format('H:i:s');

        $bookedSlotIds = Registration::whereIn('status', ['pending', 'approved'])
            ->whereNotNull('schedule_id')
            ->pluck('schedule_id');

        $availableSlots = Schedule::with(['therapist', 'doctor', 'psychologist', 'therapyType'])
            ->where('status', 'available')
            ->whereNull('patient_id')
            ->whereNotIn('id', $bookedSlotIds)
            ->where(function ($q) use ($today, $nowTime) {
                $q->where('schedule_date', '>', $today)
                  ->orWhere(function ($q2) use ($today, $nowTime) {
                      $q2->where('schedule_date', $today)
                         ->where('end_time', '>', $nowTime);
                  });
            })
            ->orderBy('schedule_date')
            ->orderBy('start_time')
            ->get();

        return Inertia::render('Registrations/Create', [
            'patients'       => Patient::where('is_active', true)
                ->with('guardian')
                ->get(['id', 'name', 'medical_record_number', 'guardian_id']),
            'availableSlots' => $availableSlots,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id'  => ['required', 'exists:patients,id'],
            'schedule_id' => ['required', 'exists:schedules,id'],
            'notes'       => ['nullable', 'string'],
        ], [
            'patient_id.required'  => 'Pasien harus dipilih.',
            'schedule_id.required' => 'Slot jadwal harus dipilih.',
        ]);

        $schedule = Schedule::findOrFail($request->schedule_id);
        abort_unless($schedule->status === 'available', 422, 'Slot jadwal sudah tidak tersedia.');

        $alreadyBooked = Registration::where('schedule_id', $schedule->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
        abort_if($alreadyBooked, 422, 'Slot jadwal sudah dibooking.');

        Registration::create([
            'patient_id'        => $request->patient_id,
            'therapy_type_id'   => $schedule->therapy_type_id,
            'schedule_id'       => $schedule->id,
            'duration_hours'    => 1.0,
            'patient_type'      => 'existing',
            'registration_date' => now()->toDateString(),
            'status'            => 'pending',
            'notes'             => $request->notes,
        ]);

        return redirect()->route('registrations.index')
            ->with('success', 'Pendaftaran berhasil dibuat.');
    }

    public function index(Request $request): Response
    {
        $registrations = Registration::with(['patient.guardian', 'therapyType', 'schedule.therapist', 'schedule.doctor', 'schedule.psychologist'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->whereHas('patient', fn ($sq) => $sq->search($request->search)))
            ->orderBy('registration_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Registrations/Index', [
            'registrations' => $registrations,
            'filters'       => $request->only(['search', 'status']),
        ]);
    }

    public function show(Registration $registration): Response
    {
        return Inertia::render('Registrations/Show', [
            'registration' => $registration->load([
                'patient.guardian',
                'therapyType',
                'schedule.therapist',
                'schedule.doctor',
                'schedule.psychologist',
            ]),
        ]);
    }

    public function approve(Registration $registration)
    {
        if ($registration->status !== 'pending') {
            return back()->with('error', 'Hanya pendaftaran pending yang bisa disetujui.');
        }

        DB::transaction(function () use ($registration) {
            $registration->update(['status' => 'approved']);

            if ($registration->schedule_id) {
                $registration->schedule->update([
                    'patient_id' => $registration->patient_id,
                    'status'     => 'scheduled',
                ]);
            }
        });

        return back()->with('success', 'Pendaftaran berhasil disetujui dan jadwal dikonfirmasi.');
    }

    public function reject(Request $request, Registration $registration)
    {
        if ($registration->status !== 'pending') {
            return back()->with('error', 'Hanya pendaftaran pending yang bisa ditolak.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $registration->update([
            'status' => 'rejected',
            'notes'  => $validated['notes'] ?? $registration->notes,
        ]);

        return back()->with('success', 'Pendaftaran berhasil ditolak. Slot jadwal kembali tersedia.');
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function fetchAvailableSlots(): array
    {
        $today   = today()->toDateString();
        $nowTime = now()->format('H:i:s');

        $bookedSlotIds = Registration::whereIn('status', ['pending', 'approved'])
            ->whereNotNull('schedule_id')
            ->pluck('schedule_id');

        $slots = Schedule::with(['therapist', 'doctor', 'psychologist', 'therapyType'])
            ->where('status', 'available')
            ->whereNull('patient_id')
            ->whereNotIn('id', $bookedSlotIds)
            ->where(function ($q) use ($today, $nowTime) {
                // Slot hari mendatang: selalu tampil
                // Slot hari ini: tampil selama sesi belum BERAKHIR (gunakan end_time, bukan start_time)
                $q->where('schedule_date', '>', $today)
                  ->orWhere(function ($q2) use ($today, $nowTime) {
                      $q2->where('schedule_date', $today)
                         ->where('end_time', '>', $nowTime);
                  });
            })
            ->orderBy('schedule_date')
            ->orderBy('start_time')
            ->get();

        return [$slots, $bookedSlotIds];
    }
}
