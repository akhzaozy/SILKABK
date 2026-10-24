<?php

namespace App\Http\Controllers;

use App\Models\TherapyType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TherapyTypeController extends Controller
{
    public function index(): Response
    {
        $types = TherapyType::withCount(['schedules', 'registrations'])
            ->orderBy('name')
            ->get();

        return Inertia::render('TherapyTypes/Index', [
            'types' => $types,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('TherapyTypes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100', Rule::unique('therapy_types', 'name')],
            'description'    => ['nullable', 'string'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
        ], [
            'name.required'           => 'Nama layanan wajib diisi.',
            'name.unique'             => 'Nama layanan sudah ada.',
            'price_per_hour.required' => 'Biaya per jam wajib diisi.',
            'price_per_hour.min'      => 'Biaya tidak boleh negatif.',
        ]);

        TherapyType::create($validated);

        return redirect()->route('therapy-types.index')
            ->with('success', "Layanan \"{$validated['name']}\" berhasil ditambahkan.");
    }

    public function edit(TherapyType $therapyType): Response
    {
        return Inertia::render('TherapyTypes/Edit', [
            'type' => $therapyType,
        ]);
    }

    public function update(Request $request, TherapyType $therapyType)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100', Rule::unique('therapy_types', 'name')->ignore($therapyType)],
            'description'    => ['nullable', 'string'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
        ], [
            'name.required'           => 'Nama layanan wajib diisi.',
            'name.unique'             => 'Nama layanan sudah ada.',
            'price_per_hour.required' => 'Biaya per jam wajib diisi.',
        ]);

        $therapyType->update($validated);

        return redirect()->route('therapy-types.index')
            ->with('success', "Layanan \"{$validated['name']}\" berhasil diperbarui.");
    }

    public function destroy(TherapyType $therapyType)
    {
        if ($therapyType->schedules()->exists()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus layanan yang sudah memiliki jadwal.']);
        }

        if ($therapyType->registrations()->exists()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus layanan yang sudah memiliki pendaftaran.']);
        }

        $name = $therapyType->name;
        $therapyType->delete();

        return back()->with('success', "Layanan \"{$name}\" berhasil dihapus.");
    }
}
