<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\TherapyResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TherapyResultController extends Controller
{
    // ─── Terapis: input data pasien per sesi (US-13) ──────────

    public function create(Schedule $schedule): Response
    {
        $therapist = Auth::user()->therapist;

        // Pastikan jadwal ini milik terapis yang login
        abort_unless(
            $therapist && $schedule->therapist_id === $therapist->id,
            403,
            'Anda tidak berwenang mengisi hasil terapi untuk jadwal ini.'
        );

        // Pastikan jadwal belum memiliki hasil terapi
        abort_if(
            $schedule->therapyResult()->exists(),
            422,
            'Hasil terapi untuk sesi ini sudah diisi.'
        );

        return Inertia::render('Therapist/Results/Create', [
            'schedule' => $schedule->load(['patient', 'therapyType']),
        ]);
    }

    public function store(Request $request)
    {
        $therapist = Auth::user()->therapist;
        abort_unless($therapist, 403);

        $validated = $request->validate([
            'schedule_id'      => ['required', 'exists:schedules,id'],
            'session_date'     => ['required', 'date'],
            'activities'       => ['required', 'string'],
            'patient_response' => ['required', 'string'],
            'goals_achieved'   => ['nullable', 'string'],
            'next_plan'        => ['nullable', 'string'],
        ], [
            'activities.required'       => 'Aktivitas terapi wajib diisi.',
            'patient_response.required' => 'Respons/perkembangan pasien wajib diisi.',
        ]);

        $schedule = Schedule::findOrFail($validated['schedule_id']);

        // Verifikasi kepemilikan jadwal
        abort_unless(
            $schedule->therapist_id === $therapist->id,
            403,
            'Anda tidak berwenang mengisi hasil terapi untuk jadwal ini.'
        );

        TherapyResult::create([
            ...$validated,
            'therapist_id' => $therapist->id,
            'patient_id'   => $schedule->patient_id,
        ]);

        // Update status jadwal menjadi selesai
        $schedule->update(['status' => 'done']);

        return redirect()->route('therapist.schedules')
            ->with('success', 'Hasil terapi berhasil disimpan dan sesi ditandai selesai.');
    }

    public function edit(TherapyResult $result): Response
    {
        $therapist = Auth::user()->therapist;

        abort_unless(
            $therapist && $result->therapist_id === $therapist->id,
            403,
            'Anda tidak berwenang mengedit hasil terapi ini.'
        );

        return Inertia::render('Therapist/Results/Edit', [
            'result'   => $result->load(['schedule', 'patient']),
        ]);
    }

    public function update(Request $request, TherapyResult $result)
    {
        $therapist = Auth::user()->therapist;

        abort_unless(
            $therapist && $result->therapist_id === $therapist->id,
            403
        );

        $validated = $request->validate([
            'session_date'     => ['required', 'date'],
            'activities'       => ['required', 'string'],
            'patient_response' => ['required', 'string'],
            'goals_achieved'   => ['nullable', 'string'],
            'next_plan'        => ['nullable', 'string'],
        ]);

        $result->update($validated);

        return redirect()->route('therapist.schedules')
            ->with('success', 'Catatan terapi berhasil diperbarui.');
    }
}