<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);

        // Don't reveal whether a specific email is registered.
        Password::sendResetLink($request->only('email'));

        return back()->with(
            'status',
            'Jika email terdaftar, tautan pemulihan akan dikirim. Silakan periksa kotak masuk atau spam.',
        );
    }
}
