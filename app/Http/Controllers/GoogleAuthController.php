<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /** Redirect the user to Google's official OAuth consent screen. */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /** Complete OAuth sign-in, or create a new Google-only account. */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Login Google dibatalkan atau tidak diizinkan. Silakan coba lagi.',
                ]);
        }

        try {
            // Stateful Socialite: verifies OAuth state through the Laravel session.
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            report($exception);
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' =>
                        'Autentikasi Google gagal. Coba lagi dan pastikan sesi browser masih aktif.',
                ]);
        }

        $googleId = (string) $googleUser->getId();
        $email = Str::lower(trim((string) $googleUser->getEmail()));
        $raw = $googleUser->getRaw();

        // Google UserInfo exposes verified_email; OIDC profiles may expose email_verified.
        $verified = ($raw['verified_email'] ?? ($raw['email_verified'] ?? false)) === true;
        if ($googleId === '' || $email === '' || !$verified) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Akun Google harus memiliki alamat email terverifikasi.']);
        }

        $user = User::where('google_id', $googleId)->first();

        if (!$user) {
            // Never silently connect a Google identity to an existing password account.
            if (User::where('email', $email)->exists()) {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' =>
                            'Email ini sudah terdaftar. Masuk menggunakan kata sandi terlebih dahulu. Penautan akun Google perlu fitur terpisah.',
                    ]);
            }

            $name = trim((string) $googleUser->getName());
            $user = User::create([
                'name' => $name !== '' ? $name : 'Pengguna FindBack',
                'email' => $email,
                'google_id' => $googleId,
                // Random unknown password; Google-only users cannot log in with a known password.
                'password' => Hash::make(Str::random(64)),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route($user->dashboardRoute()));
    }
}
