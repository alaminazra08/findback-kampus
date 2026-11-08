<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))

    /*
    |--------------------------------------------------------------------------
    | Routing Configuration
    |--------------------------------------------------------------------------
    | Mengatur route web, console, dan health check.
    */

    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    | Mengatur middleware role serta redirect
    | pengguna yang sudah login.
    */

    ->withMiddleware(function (Middleware $middleware): void {
        // ====================================================
        // ROLE MIDDLEWARE
        // ====================================================

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);

        // ====================================================
        // REDIRECT AUTHENTICATED USERS
        // ====================================================
        // Jika pengguna sudah login dan membuka halaman
        // guest seperti /login atau /register,
        // arahkan ke dashboard sesuai role.

        $middleware->redirectUsersTo(function ($request) {
            $user = $request->user();

            if ($user && $user->role === 'admin') {
                return route('admin.dashboard');
            }

            if ($user && $user->role === 'user') {
                return route('dashboard');
            }

            return route('home');
        });
    })

    /*
    |--------------------------------------------------------------------------
    | Exception Configuration
    |--------------------------------------------------------------------------
    | Menggunakan penanganan exception bawaan Laravel.
    */

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();
