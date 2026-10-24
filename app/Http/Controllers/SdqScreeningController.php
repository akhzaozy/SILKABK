<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\SdqScreening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SdqScreeningController extends Controller
{
    // ─── Orang Tua ────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        $guardian = Auth::user()->parentGuardian;
        abort_unless($guardian, 403, 'Profil orang tua tidak ditemukan.');

        $patientIds = Patient::where('guardian_id', $guardian->id)->pluck('id');

        $screenings = SdqScreening::with('patient')
            ->whereIn('patient_id', $patientIds)
            ->when($request->patient_id, fn ($q) => $q->where('patient_id', $request->patient_id))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $patients = Patient::whereIn('id', $patientIds)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Parent/SdqScreenings/Index', [
            'screenings' => $screenings,
            'patients'   => $patients,
            'filters'    => $request->only(['patient_id']),
        ]);
    }

    public function create(): Response
    {
        $guardian = Auth::user()->parentGuardian;
        abort_unless($guardian, 403, 'Profil orang tua tidak ditemukan.');

        $patients = Patient::where('guardian_id', $guardian->id)
            ->where('is_active', true)
            ->get(['id', 'name', 'birth_date']);

        abort_if($patients->isEmpty(), 403, 'Belum ada anak yang terdaftar.');

        return Inertia::render('Parent/SdqScreenings/Create', [
            'patients'  => $patients,
            'questions' => SdqScreening::QUESTIONS,
        ]);
    }

    public function store(Request $request)
    {
        $guardian = Auth::user()->parentGuardian;
        abort_unless($guardian, 403, 'Profil orang tua tidak ditemukan.');

        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'answers'    => ['required', 'array', 'size:25'],
            'answers.*'  => ['required', 'integer', 'min:0', 'max:2'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ], [
            'patient_id.required' => 'Anak harus dipilih.',
            'answers.required'    => 'Semua pertanyaan harus dijawab.',
            'answers.size'        => 'Semua 25 pertanyaan harus dijawab.',
            'answers.*.integer'   => 'Jawaban tidak valid.',
        ]);

        $patient = Patient::findOrFail($validated['patient_id']);
        abort_unless($patient->guardian_id === $guardian->id, 403, 'Anda tidak memiliki akses ke data anak ini.');

        $scores = SdqScreening::computeScores($validated['answers']);

        SdqScreening::create([
            'patient_id'          => $validated['patient_id'],
            'user_id'             => Auth::id(),
            'answers'             => $validated['answers'],
            'emotional_score'     => $scores['emotional'],
            'conduct_score'       => $scores['conduct'],
            'hyperactivity_score' => $scores['hyperactivity'],
            'peer_score'          => $scores['peer'],
            'prosocial_score'     => $scores['prosocial'],
            'total_score'         => $scores['total'],
            'interpretation'      => SdqScreening::interpret($scores['total']),
            'notes'               => $validated['notes'] ?? null,
        ]);

        return redirect()->route('parent.sdq.index')
            ->with('success', 'Kuesioner SDQ berhasil disimpan.');
    }

    public function show(SdqScreening $screening): Response
    {
        $guardian = Auth::user()->parentGuardian;
        abort_unless($guardian, 403);

        $patient = $screening->patient;
        abort_unless($patient && $patient->guardian_id === $guardian->id, 403, 'Anda tidak memiliki akses ke data ini.');

        return Inertia::render('Parent/SdqScreenings/Show', [
            'screening' => $screening->load('patient'),
            'questions' => SdqScreening::QUESTIONS,
            'subscales' => SdqScreening::SUBSCALES,
        ]);
    }

    // ─── Dokter & Psikolog: lihat SDQ pasien ─────────────────────

    public function showForProfessional(SdqScreening $screening): Response
    {
        return Inertia::render('Parent/SdqScreenings/Show', [
            'screening'   => $screening->load('patient'),
            'questions'   => SdqScreening::QUESTIONS,
            'subscales'   => SdqScreening::SUBSCALES,
            'readOnly'    => true,
        ]);
    }

    public function indexForPatient(Patient $patient): Response
    {
        $screenings = SdqScreening::where('patient_id', $patient->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'total_score', 'interpretation', 'created_at']);

        return Inertia::render('Professional/SdqScreenings/PatientIndex', [
            'patient'    => $patient->only(['id', 'name', 'medical_record_number']),
            'screenings' => $screenings,
        ]);
    }
}
