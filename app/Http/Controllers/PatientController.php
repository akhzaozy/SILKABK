<?php

namespace App\Http\Controllers;

use App\Models\ParentGuardian;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    //  Admin: Kelola Data Pasien (US-04, US-06) 

    public function index(Request $request): Response
    {
        $patients = Patient::with(['guardian'])
            ->when($request->search, fn ($q) => $q->search($request->search))
            ->when($request->status !== null, fn ($q) =>
                $q->where('is_active', $request->status === 'active')
            )
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Patients/Index', [
            'patients' => $patients,
            'filters'  => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Patients/Create', [
            'guardians' => ParentGuardian::with('user')->get(['id', 'name', 'user_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guardian_id'           => ['required', 'exists:parent_guardians,id'],
            'medical_record_number' => ['required', 'string', 'max:20', 'unique:patients,medical_record_number'],
            'name'                  => ['required', 'string', 'max:100'],
            'nik'                   => ['nullable', 'string', 'max:20', 'unique:patients,nik'],
            'gender'                => ['required', 'in:L,P'],
            'birth_date'            => ['required', 'date', 'before:today'],
            'birth_place'           => ['required', 'string', 'max:100'],
            'address'               => ['required', 'string'],
            'diagnosis'             => ['nullable', 'string'],
            'notes'                 => ['nullable', 'string'],
            'is_active'             => ['boolean'],
        ], [
            'guardian_id.required'           => 'Orang tua/wali harus dipilih.',
            'medical_record_number.required' => 'Nomor rekam medis wajib diisi.',
            'medical_record_number.unique'   => 'Nomor rekam medis sudah digunakan.',
            'nik.unique'                     => 'NIK sudah terdaftar di sistem.',
            'name.required'                  => 'Nama pasien wajib diisi.',
            'gender.required'                => 'Jenis kelamin wajib dipilih.',
            'birth_date.required'            => 'Tanggal lahir wajib diisi.',
            'birth_date.before'              => 'Tanggal lahir harus sebelum hari ini.',
            'birth_place.required'           => 'Tempat lahir wajib diisi.',
            'address.required'               => 'Alamat wajib diisi.',
        ]);

        Patient::create($validated);

        return redirect()->route('patients.index')
            ->with('success', 'Data pasien berhasil ditambahkan.');
    }

    public function show(Patient $patient): Response
    {
        return Inertia::render('Patients/Show', [
            'patient' => $patient->load(['guardian', 'registrations', 'schedules', 'payments']),
        ]);
    }

    public function edit(Patient $patient): Response
    {
        return Inertia::render('Patients/Edit', [
            'patient'   => $patient->load('guardian'),
            'guardians' => ParentGuardian::with('user')->get(['id', 'name', 'user_id']),
        ]);
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'guardian_id'           => ['required', 'exists:parent_guardians,id'],
            'medical_record_number' => ['required', 'string', 'max:20', 'unique:patients,medical_record_number,' . $patient->id],
            'name'                  => ['required', 'string', 'max:100'],
            'nik'                   => ['nullable', 'string', 'max:20', 'unique:patients,nik,' . $patient->id],
            'gender'                => ['required', 'in:L,P'],
            'birth_date'            => ['required', 'date', 'before:today'],
            'birth_place'           => ['required', 'string', 'max:100'],
            'address'               => ['required', 'string'],
            'diagnosis'             => ['nullable', 'string'],
            'notes'                 => ['nullable', 'string'],
            'is_active'             => ['boolean'],
        ]);

        $patient->update($validated);

        return redirect()->route('patients.index')
            ->with('success', 'Data pasien berhasil diperbarui.');
    }

    public function destroy(Patient $patient)
    {
        $patient->update(['is_active' => false]);
        return redirect()->route('patients.index')
            ->with('success', 'Pasien berhasil dinonaktifkan.');
    }

    // ─── Orang Tua: Tambah Anak ───────────────────────────────

    public function createByParent(): Response
    {
        return Inertia::render('Parent/AddChild');
    }

    public function storeByParent(Request $request)
    {
        $guardian = Auth::user()->parentGuardian;
        abort_unless($guardian, 403, 'Profil orang tua tidak ditemukan.');

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'nik'         => ['nullable', 'string', 'max:20', 'unique:patients,nik'],
            'gender'      => ['required', 'in:L,P'],
            'birth_date'  => ['required', 'date', 'before:today'],
            'birth_place' => ['required', 'string', 'max:100'],
            'address'     => ['required', 'string'],
        ], [
            'name.required'        => 'Nama anak wajib diisi.',
            'nik.unique'           => 'NIK sudah terdaftar di sistem.',
            'gender.required'      => 'Jenis kelamin wajib dipilih.',
            'birth_date.required'  => 'Tanggal lahir wajib diisi.',
            'birth_date.before'    => 'Tanggal lahir harus sebelum hari ini.',
            'birth_place.required' => 'Tempat lahir wajib diisi.',
            'address.required'     => 'Alamat wajib diisi.',
        ]);

        Patient::create([
            ...$validated,
            'guardian_id' => $guardian->id,
            'is_active'   => true,
        ]);

        return redirect()->route('parent.registrations')
            ->with('success', 'Data anak berhasil didaftarkan ke sistem.');
    }

}

