<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DiagnosisController extends Controller
{
    // ─── Dokter: CRUD Diagnosa Penyakit ──────────────────────

    public function index(Request $request): Response
    {
        $user   = Auth::user();
        $doctor = $user->doctor;

        if (!$doctor && $user->role !== 'super_admin') {
            abort(403, 'Profil dokter tidak ditemukan.');
        }

        $diagnoses = Diagnosis::with(['patient', 'doctor'])
            ->when($doctor, fn ($q) => $q->where('doctor_id', $doctor->id))
            ->when($request->search, fn ($q) =>
                $q->whereHas('patient', fn ($sq) =>
                    $sq->where('name', 'ilike', "%{$request->search}%")
                       ->orWhere('medical_record_number', 'ilike', "%{$request->search}%")
                )
            )
            ->when($request->patient_id, fn ($q) => $q->where('patient_id', $request->patient_id))
            ->orderBy('diagnosis_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Doctor/Diagnoses/Index', [
            'diagnoses' => $diagnoses,
            'patients'  => $this->patientOptions($doctor),
            'filters'   => $request->only(['search', 'patient_id']),
        ]);
    }

    public function create(): Response
    {
        $doctor = Auth::user()->doctor;

        return Inertia::render('Doctor/Diagnoses/Create', [
            'patients' => $this->patientOptions($doctor),
            'today'    => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $doctor = Auth::user()->doctor;

        abort_unless($doctor, 403, 'Profil dokter tidak ditemukan.');

        $validated = $request->validate([
            'patient_id'     => ['required', 'exists:patients,id'],
            'diagnosis_date' => ['required', 'date'],
            'symptoms'       => ['required', 'string'],
            'diagnosis_name' => ['required', 'string', 'max:255'],
            'icd_code'       => ['nullable', 'string', 'max:20'],
            'treatment'      => ['nullable', 'string'],
            'notes'          => ['nullable', 'string'],
        ], [
            'patient_id.required'     => 'Pasien harus dipilih.',
            'diagnosis_date.required' => 'Tanggal diagnosa harus diisi.',
            'symptoms.required'       => 'Gejala harus diisi.',
            'diagnosis_name.required' => 'Nama diagnosa penyakit harus diisi.',
        ]);

        $validated['doctor_id'] = $doctor->id;

        Diagnosis::create($validated);

        return redirect()->route('doctor.diagnoses.index')
            ->with('success', 'Diagnosa berhasil disimpan.');
    }

    public function show(Diagnosis $diagnosis): Response
    {
        $this->authorizeDoctor($diagnosis);

        return Inertia::render('Doctor/Diagnoses/Show', [
            'diagnosis' => $diagnosis->load(['patient.guardian', 'doctor']),
        ]);
    }

    public function edit(Diagnosis $diagnosis): Response
    {
        $this->authorizeDoctor($diagnosis);

        $doctor = Auth::user()->doctor;

        return Inertia::render('Doctor/Diagnoses/Edit', [
            'diagnosis' => $diagnosis->load('patient'),
            'patients'  => $this->patientOptions($doctor),
        ]);
    }

    public function update(Request $request, Diagnosis $diagnosis)
    {
        $this->authorizeDoctor($diagnosis);

        $validated = $request->validate([
            'patient_id'     => ['required', 'exists:patients,id'],
            'diagnosis_date' => ['required', 'date'],
            'symptoms'       => ['required', 'string'],
            'diagnosis_name' => ['required', 'string', 'max:255'],
            'icd_code'       => ['nullable', 'string', 'max:20'],
            'treatment'      => ['nullable', 'string'],
            'notes'          => ['nullable', 'string'],
        ], [
            'patient_id.required'     => 'Pasien harus dipilih.',
            'diagnosis_date.required' => 'Tanggal diagnosa harus diisi.',
            'symptoms.required'       => 'Gejala harus diisi.',
            'diagnosis_name.required' => 'Nama diagnosa penyakit harus diisi.',
        ]);

        $diagnosis->update($validated);

        return redirect()->route('doctor.diagnoses.index')
            ->with('success', 'Diagnosa berhasil diperbarui.');
    }

    public function destroy(Diagnosis $diagnosis)
    {
        $this->authorizeDoctor($diagnosis);

        $diagnosis->delete();

        return back()->with('success', 'Diagnosa berhasil dihapus.');
    }

    // ─── Orang Tua: Lihat Diagnosa Anak (read-only) ──────────

    public function indexByParent(Request $request): Response
    {
        $guardian = Auth::user()->parentGuardian;

        abort_unless($guardian, 403);

        $patientIds = Patient::where('guardian_id', $guardian->id)->pluck('id');

        $diagnoses = Diagnosis::with(['patient', 'doctor'])
            ->whereIn('patient_id', $patientIds)
            ->when($request->patient_id, fn ($q) => $q->where('patient_id', $request->patient_id))
            ->orderBy('diagnosis_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        $patients = Patient::whereIn('id', $patientIds)
            ->get(['id', 'name', 'medical_record_number']);

        return Inertia::render('Parent/Diagnoses/Index', [
            'diagnoses' => $diagnoses,
            'patients'  => $patients,
            'filters'   => $request->only(['patient_id']),
        ]);
    }

    public function showByParent(Diagnosis $diagnosis): Response
    {
        $guardian = Auth::user()->parentGuardian;

        abort_unless($guardian, 403);

        $patientIds = Patient::where('guardian_id', $guardian->id)->pluck('id');

        abort_unless($patientIds->contains($diagnosis->patient_id), 403);

        return Inertia::render('Parent/Diagnoses/Show', [
            'diagnosis' => $diagnosis->load(['patient', 'doctor']),
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function authorizeDoctor(Diagnosis $diagnosis): void
    {
        $user   = Auth::user();
        $doctor = $user->doctor;

        if ($user->role === 'super_admin') return;

        abort_unless($doctor, 403);
        abort_unless($diagnosis->doctor_id === $doctor->id, 403, 'Anda tidak berwenang mengakses diagnosa ini.');
    }

    private function patientOptions($doctor): \Illuminate\Database\Eloquent\Collection
    {
        return Patient::where('is_active', true)
            ->get(['id', 'name', 'medical_record_number', 'birth_date', 'gender']);
    }
}
