<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        $user = Auth::user()->load(['therapist.therapyType', 'doctor', 'psychologist', 'parentGuardian']);

        return Inertia::render('Profile/Edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi data akun
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'name.required'  => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique'   => 'Email sudah digunakan akun lain.',
        ]);

        $user->update($validated);

        // Ganti password jika diisi
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ], [
                'password.min'       => 'Password minimal 8 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
            ]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Update profil sesuai role
        match ($user->role) {
            'terapis'    => $this->updateTherapist($user, $request),
            'dokter'     => $this->updateDoctor($user, $request),
            'psikolog'   => $this->updatePsychologist($user, $request),
            'orang_tua'  => $this->updateParent($user, $request),
            default      => null,
        };

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    private function updateTherapist($user, Request $request): void
    {
        $data = $request->validate([
            'specialization' => ['nullable', 'string', 'max:100'],
            'phone'          => ['nullable', 'string', 'max:20'],
        ]);

        if ($user->therapist) {
            $user->therapist->update([
                'name'           => $user->name,
                'specialization' => $data['specialization'] ?? $user->therapist->specialization,
                'phone'          => $data['phone'] ?? $user->therapist->phone,
            ]);
        }
    }

    private function updateDoctor($user, Request $request): void
    {
        $data = $request->validate([
            'specialization' => ['nullable', 'string', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'phone'          => ['nullable', 'string', 'max:20'],
        ]);

        if ($user->doctor) {
            $user->doctor->update([
                'name'           => $user->name,
                'specialization' => $data['specialization'] ?? $user->doctor->specialization,
                'license_number' => $data['license_number'] ?? $user->doctor->license_number,
                'phone'          => $data['phone'] ?? $user->doctor->phone,
            ]);
        }
    }

    private function updatePsychologist($user, Request $request): void
    {
        $data = $request->validate([
            'spesialisasi' => ['nullable', 'string', 'max:100'],
            'nomor_sip'    => ['nullable', 'string', 'max:50'],
            'telepon'      => ['nullable', 'string', 'max:20'],
        ]);

        if ($user->psychologist) {
            $user->psychologist->update([
                'nama'         => $user->name,
                'spesialisasi' => $data['spesialisasi'] ?? $user->psychologist->spesialisasi,
                'nomor_sip'    => $data['nomor_sip']    ?? $user->psychologist->nomor_sip,
                'telepon'      => $data['telepon']      ?? $user->psychologist->telepon,
            ]);
        }
    }

    private function updateParent($user, Request $request): void
    {
        $data = $request->validate([
            'relationship' => ['nullable', 'string', 'max:50'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'address'      => ['nullable', 'string'],
        ]);

        if ($user->parentGuardian) {
            $user->parentGuardian->update([
                'name'         => $user->name,
                'relationship' => $data['relationship'] ?? $user->parentGuardian->relationship,
                'phone'        => $data['phone']        ?? $user->parentGuardian->phone,
                'address'      => $data['address']      ?? $user->parentGuardian->address,
            ]);
        }
    }
}
