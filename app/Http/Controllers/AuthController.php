<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ParentGuardian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    // ─── US-01: Login ─────────────────────────────────────────

    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        if (! Auth::user()->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Akun Anda tidak aktif. Hubungi administrator.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    // ─── US-02: Logout ────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ─── Register ─────────────────────────────────────────────

    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'role'     => ['required', 'in:admin,orang_tua,terapis,dokter'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required'     => 'Nama wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar.',
            'role.required'     => 'Role wajib dipilih.',
            'role.in'           => 'Role tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'      => 'Password minimal 8 karakter.',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role'      => $validated['role'],
            'password'  => Hash::make($validated['password']),
            'is_active' => true, // Auto active for now
        ]);

        // Jika role orang_tua, buat profil ParentGuardian
        if ($validated['role'] === 'orang_tua') {
            ParentGuardian::create([
                'user_id'      => $user->id,
                'name'         => $validated['name'],
                'relationship' => 'Wali', // Default
                'phone'        => '',
                'address'      => '',
            ]);
        }

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}