<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 01. REGISTRATION
    |--------------------------------------------------------------------------
    */

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = new User();

        $user->name = $validated['name'];
        $user->email = Str::lower($validated['email']);
        $user->password = $validated['password'];
        $user->role = 'user';

        $user->save();

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Akun berhasil dibuat. Selamat datang di FindBack Kampus!');
    }

    /*
    |--------------------------------------------------------------------------
    | 02. LOGIN
    |--------------------------------------------------------------------------
    */

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email'] = Str::lower($credentials['email']);

        $throttleKey = 'login:' . $credentials['email'] . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('dashboard');
    }

    /*
    |--------------------------------------------------------------------------
    | 03. USER DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function dashboard(): View
    {
        $userId = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | STATISTIK LAPORAN
        |--------------------------------------------------------------------------
        */

        $reports = DB::table('lost_found_items')->where('user_id', $userId);

        $totalReports = (clone $reports)->count();

        $lostReports = (clone $reports)->where('type', 'lost')->count();

        $foundReports = (clone $reports)->where('type', 'found')->count();

        $pendingReports = (clone $reports)->where('status', 'pending')->count();

        /*
        |--------------------------------------------------------------------------
        | RIWAYAT LAPORAN TERBARU
        |--------------------------------------------------------------------------
        */

        $recentReports = (clone $reports)
            ->select([
                'id',
                'title',
                'category',
                'type',
                'location',
                'event_date',
                'status',
                'photo_path',
                'created_at',
            ])
            ->latest('created_at')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | STATISTIK KLAIM
        |--------------------------------------------------------------------------
        */

        $claims = DB::table('item_claims')->where('user_id', $userId);

        $totalClaims = (clone $claims)->count();

        $pendingClaims = (clone $claims)->where('status', 'pending')->count();

        $approvedClaims = (clone $claims)->where('status', 'approved')->count();

        $rejectedClaims = (clone $claims)->where('status', 'rejected')->count();

        $returnedClaims = (clone $claims)->where('status', 'returned')->count();

        /*
        |--------------------------------------------------------------------------
        | RIWAYAT KLAIM TERBARU
        |--------------------------------------------------------------------------
        */

        $recentClaims = DB::table('item_claims as claims')
            ->leftJoin('lost_found_items as items', 'items.id', '=', 'claims.lost_found_item_id')
            ->where('claims.user_id', $userId)
            ->select([
                'claims.id',
                'claims.status',
                'claims.created_at',
                'claims.returned_at',
                'items.title as item_title',
            ])
            ->latest('claims.created_at')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | KIRIM DATA KE DASHBOARD
        |--------------------------------------------------------------------------
        */

        $latestItems = \App\Models\LostFoundItem::where('status', 'verified')
            ->latest()
            ->limit(4)
            ->get();
        return view(
            'user.dashboard',
            compact(
                'latestItems',
                'totalReports',
                'lostReports',
                'foundReports',
                'pendingReports',
                'recentReports',
                'totalClaims',
                'pendingClaims',
                'approvedClaims',
                'rejectedClaims',
                'returnedClaims',
                'recentClaims',
            ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 04. LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('success', 'Anda berhasil keluar dari FindBack Kampus.');
    }
}
