@extends('user.layouts.campus')

@section('title', 'Profil Saya')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user/profile.css') }}?v=3">
@endpush

@section('content')
@php
    $user = auth()->user();
    $initial = mb_strtoupper(mb_substr($user->name, 0, 1));
@endphp

<div class="profile-page">

    {{-- PAGE HEADING --}}
    <header class="profile-heading">
        <div class="profile-eyebrow">
            <span></span>
            ACCOUNT CENTER
        </div>

        <h1>Profil Saya</h1>
        <p>Kelola informasi identitas dan keamanan akun kampus Anda.</p>
    </header>

    <div class="profile-layout">

        {{-- SIDEBAR PROFIL --}}
        <aside class="profile-sidebar">

            <div class="profile-cover">
                <div class="profile-cover-pattern"></div>
            </div>

            <div class="profile-user">
                <div class="profile-avatar">
                    {{ $initial }}
                </div>

                <h2>{{ $user->name }}</h2>
                <p>Civitas Akademika FST</p>

                <span class="profile-status">
                    <i></i>
                    Akun Terdaftar
                </span>
            </div>

            <nav class="profile-navigation">
                <span class="profile-nav-heading">PENGATURAN AKUN</span>

                <a href="#info" class="profile-nav-link active">
                    <span class="profile-nav-icon">♙</span>
                    <span>Informasi Profil</span>
                    <span class="profile-nav-arrow">›</span>
                </a>

                <a href="#password-section" class="profile-nav-link">
                    <span class="profile-nav-icon">♧</span>
                    <span>Keamanan Password</span>
                    <span class="profile-nav-arrow">›</span>
                </a>

                <a href="{{ route('user.notifications.index') }}" class="profile-nav-link">
                    <span class="profile-nav-icon">♧</span>
                    <span>Notifikasi</span>
                    <span class="profile-nav-arrow">›</span>
                </a>
            </nav>

            <div class="profile-sidebar-footer">
                <span class="profile-footer-mark">F</span>

                <div>
                    <strong>FindBack Kampus</strong>
                    <small>Campus Lost &amp; Found</small>
                </div>
            </div>

        </aside>

        {{-- KONTEN PROFIL --}}
        <div class="profile-content">

            {{-- INFORMASI PROFIL --}}
            <form
                id="info"
                class="profile-card"
                method="POST"
                action="{{ route('user.profile.update') }}"
            >
                @csrf

                <div class="profile-card-header">
                    <div class="profile-card-heading">
                        <div class="profile-card-icon">▤</div>

                        <div>
                            <h3>Informasi Profil</h3>
                            <p>Perbarui informasi dasar identitas akun Anda.</p>
                        </div>
                    </div>

                    <span class="profile-label">PERSONAL DATA</span>
                </div>

                <div class="profile-form">

                    <div class="profile-field full">
                        <label for="name">
                            Nama Lengkap <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            maxlength="255"
                            placeholder="Masukkan nama lengkap"
                            required
                        >
                    </div>

                    <div class="profile-field full">
                        <label for="email">Alamat Email</label>

                        <input
                            type="email"
                            id="email"
                            value="{{ $user->email }}"
                            readonly
                        >

                        <small>
                            Email tidak dapat diubah melalui halaman ini.
                        </small>
                    </div>

                </div>

                <div class="profile-information">
                    <span class="profile-information-icon">i</span>

                    <div>
                        <strong>Informasi Institusi</strong>
                        <p>
                            Universitas Islam Negeri Imam Bonjol Padang
                            · Fakultas Sains dan Teknologi.
                        </p>
                    </div>
                </div>

                <div class="profile-card-footer">
                    <span>Pastikan informasi profil Anda sudah benar.</span>

                    <button type="submit" class="profile-button">
                        <span>▣</span>
                        Simpan Perubahan
                    </button>
                </div>

            </form>

            {{-- KEAMANAN AKUN --}}
            <form
                id="password-section"
                class="profile-card"
                method="POST"
                action="{{ route('user.profile.password') }}"
            >
                @csrf

                <div class="profile-card-header">
                    <div class="profile-card-heading">
                        <div class="profile-card-icon security-icon">♙</div>

                        <div>
                            <h3>Keamanan Akun</h3>
                            <p>Perbarui password untuk melindungi akun Anda.</p>
                        </div>
                    </div>

                    <span class="profile-label">SECURITY</span>
                </div>

                <div class="profile-form">

                    <div class="profile-field full">
                        <label for="current_password">
                            Password Saat Ini <span>*</span>
                        </label>

                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            autocomplete="current-password"
                            placeholder="Masukkan password saat ini"
                            required
                        >
                    </div>

                    <div class="profile-field">
                        <label for="password">
                            Password Baru <span>*</span>
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                            required
                        >
                    </div>

                    <div class="profile-field">
                        <label for="password_confirmation">
                            Konfirmasi Password <span>*</span>
                        </label>

                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="Ulangi password baru"
                            required
                        >
                    </div>

                </div>

                <div class="profile-security">
                    <div class="profile-security-icon">✓</div>

                    <div>
                        <strong>Tips keamanan akun</strong>
                        <p>
                            Gunakan password minimal 8 karakter yang
                            menggabungkan huruf, angka, dan simbol.
                            Jangan membagikan password kepada siapa pun.
                        </p>
                    </div>
                </div>

                <div class="profile-card-footer">
                    <span>Jaga kerahasiaan akses akun Anda.</span>

                    <button type="submit" class="profile-button">
                        <span>♧</span>
                        Perbarui Password
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="{{ asset('js/user/profile.js') }}" defer></script>
@endsection
