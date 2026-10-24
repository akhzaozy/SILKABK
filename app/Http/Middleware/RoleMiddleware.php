<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Batasi akses berdasarkan role.
     *
     * Aturan:
     * - super_admin SELALU diizinkan masuk ke semua route yang dijaga middleware ini.
     * - Role lain harus ada dalam daftar $roles yang diperbolehkan.
     *
     * Penggunaan di route:
     *   ->middleware('role:admin,super_admin')
     *   ->middleware('role:dokter')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda tidak aktif atau belum login.']);
        }

        $userRole = $user->role;

        // Super admin selalu bisa akses semua route
        if ($userRole === 'super_admin') {
            return $next($request);
        }

        if (! in_array($userRole, $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}