<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pindah ke sistem SPK Skrining Anak dengan login otomatis (SSO).
 *
 * Membuat token sekali pakai berumur 60 detik:
 * base64url(json{uid, exp, nonce}) . "." . base64url(HMAC-SHA256)
 * yang diverifikasi SPK memakai secret yang sama (SPK_SKRINING_SSO_SECRET).
 */
class SpkSkriningController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $baseUrl = rtrim((string) config('services.spk_skrining.url'), '/');
        $secret  = (string) config('services.spk_skrining.sso_secret');

        // Tanpa secret: arahkan saja ke halaman SPK (login manual).
        if ($secret === '') {
            return Inertia::location($baseUrl);
        }

        $body = $this->base64UrlEncode(json_encode([
            'uid'   => $request->user()->id,
            'exp'   => time() + 60,
            'nonce' => Str::random(32),
        ]));

        $signature = $this->base64UrlEncode(hash_hmac('sha256', $body, $secret, true));

        return Inertia::location($baseUrl.'/sso/silkabk?token='.$body.'.'.$signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
