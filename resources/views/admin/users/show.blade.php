@extends('admin.layouts.items')
@section('title', 'Detail Akun')
@section('breadcrumb', 'Manajemen Akun')
@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/users-detail.css') }}?v=43">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/detail-back.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/account-detail-layout.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/account-profile-photo.css') }}?v=1">
@endsection

@section('content')
    <div class="fb-users-page fb-detail40">
        <div class="fb-account-back">
            <a data-fb-detail-back class="fb-detail-return" href="{{ route('admin.users.index') }}">
                <span aria-hidden="true">←</span> Kembali ke daftar
            </a>
        </div>
        <header class="fb40-top fb-account-heading">
            <p class="fb40-eyebrow">ADMINISTRASI · MANAJEMEN AKUN</p>
            <div class="fb40-title-row"><h1>Detail <span>Akun</span></h1></div>
        </header>
        @foreach (['success', 'error', 'info'] as $flash)
            @if (session($flash))
                <div class="fb-users-alert fb-users-alert-{{ $flash }}" role="status">{{ session($flash) }}</div>
            @endif
        @endforeach
        @if ($errors->any())
            <div class="fb-users-alert fb-users-alert-error" role="alert">{{ $errors->first() }}</div>
        @endif
        <div class="fb40-layout fb-account-grid">
            <section class="fb40-card fb40-identity" aria-labelledby="fb40-profile-title">
                <h2 id="fb40-profile-title">Informasi Akun</h2>
                <div class="fb-account-summary">
                    <div class="fb40-profile">
                        <div class="fb40-avatar" data-account-photo="{{ $user->id }}">@include('admin.users.profile-photo', ['account' => $user])</div>
                        <div class="fb40-profile-text">
                            <div class="fb40-name-row">
                                <p class="fb-account-name">{{ $user->name }}</p>
                                <span
                                    class="fb-users-pill {{ $user->role === 'admin' ? 'is-admin' : '' }}">{{ $user->role === 'admin' ? 'Administrator' : 'Pengguna' }}</span>
                            </div>
                            <p>{{ $user->email }}</p>
                            <span class="fb40-id">ID akun {{ $user->id }}</span>
                        </div>
                    </div>
                    <dl class="fb40-facts fb-account-login-facts">
                        <div>
                            <dt>Metode Masuk</dt>
                            <dd>{{ $user->google_id ? 'Google OAuth' : 'Email / biasa' }}</dd>
                        </div>
                        <div>
                            <dt>Verifikasi Email</dt>
                            <dd><span
                                    class="fb40-status {{ $user->email_verified_at ? 'is-ok' : 'is-pending' }}">{{ $user->email_verified_at ? 'Terverifikasi' : 'Belum terverifikasi' }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>
                <section class="fb42-activity fb-account-activity" aria-labelledby="fb42-activity-title">
                    <div class="fb40-section-heading">
                        <div class="fb40-mini-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 3v18h18" />
                                <path d="m7 15 4-4 3 2 5-6" />
                            </svg></div>
                        <div>
                            <h2 id="fb42-activity-title">Aktivitas Pengguna</h2>
                            <p>Ringkasan aktivitas berdasarkan data sistem.</p>
                        </div>
                    </div>
                    <div class="fb42-stats">
                        <div class="fb42-stat"><span class="fb42-stat-icon"><svg viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="7" />
                                    <path d="m20 20-4-4" />
                                </svg></span><strong>{{ number_format($activity['lost']) }}</strong><span>Laporan
                                Hilang</span></div>
                        <div class="fb42-stat"><span class="fb42-stat-icon"><svg viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m3 7 9-4 9 4-9 4-9-4Z" />
                                    <path d="M3 7v10l9 4 9-4V7M12 11v10" />
                                </svg></span><strong>{{ number_format($activity['found']) }}</strong><span>Laporan
                                Temuan</span></div>
                        <div class="fb42-stat"><span class="fb42-stat-icon"><svg viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="5" y="4" width="14" height="17" rx="2" />
                                    <path d="M9 4.5h6M9 11h6M9 15h4" />
                                </svg></span><strong>{{ number_format($activity['claims']) }}</strong><span>Pengajuan
                                Klaim</span></div>
                    </div>
                </section>
            </section>
            <div class="fb-account-side">
                <section class="fb40-card fb40-access" aria-labelledby="fb40-access-title">
                    <div class="fb40-section-heading">
                        <div class="fb40-mini-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                                <path d="m9 12 2 2 4-4" />
                            </svg></div>
                        <div>
                            <h2 id="fb40-access-title">Pengaturan Hak Akses</h2>
                            <p>Kelola peran pengguna secara aman.</p>
                        </div>
                    </div>
                    @if ((int) $user->id === (int) auth()->id())
                        <div class="fb40-notice">Peran akun Anda sendiri tidak dapat diubah.</div>
                    @else
                        <form method="POST" action="{{ route('admin.users.update-role', $user) }}"
                            class="fb40-role-form" data-confirm="Yakin ingin mengubah hak akses akun ini?"
                            data-confirm-event="submit">
                            @csrf
                            @method('PATCH')
                            <label for="fb40-role">Peran Pengguna</label>
                            <select name="role" id="fb40-role" required>
                                <option value="user" @selected(old('role', $user->role) === 'user')>Pengguna - akses pengguna biasa</option>
                                <option value="admin" @selected(old('role', $user->role) === 'admin')>Administrator - akses panel admin
                                </option>
                            </select>
                            <p class="fb40-helper">Perubahan peran memengaruhi hak akses akun. Pastikan pilihan sudah benar.
                            </p>
                            <button type="submit" class="fb40-save">Simpan Perubahan Peran</button>
                        </form>
                    @endif
                </section>
                <section class="fb40-card fb43-history-side" aria-labelledby="fb40-time-title">
                    <div class="fb40-section-heading">
                        <div class="fb40-mini-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 7v5l3 2" />
                            </svg></div>
                        <div>
                            <h2 id="fb40-time-title">Riwayat Akun</h2>
                            <p>Informasi waktu pendaftaran dan pembaruan data.</p>
                        </div>
                    </div>
                    <dl class="fb40-facts">
                        <div>
                            <dt>Tanggal Daftar</dt>
                            <dd>{{ $user->created_at?->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i \W\I\B') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt>Terakhir Diperbarui</dt>
                            <dd>{{ $user->updated_at?->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i \W\I\B') ?? '—' }}</dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>
    </div>
@endsection
