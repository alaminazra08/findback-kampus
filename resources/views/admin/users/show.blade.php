@extends('admin.layouts.items')
@section('title', 'Detail Akun')
@section('breadcrumb', 'Manajemen Akun')
@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-users-detail-v40.css') }}?v=43">
@endsection

@section('content')
    <div class="fb-users-page fb-detail40">
        <nav class="fb41-breadcrumb" aria-label="Navigasi kembali">
            <a class="fb40-back fb41-back" href="{{ route('admin.users.index') }}"><svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="m15 18-6-6 6-6" />
                </svg><span>Kembali ke Manajemen Akun</span></a>
        </nav>
        <div class="fb40-top">
            <div class="fb40-title-row">
                <div>
                    <p class="fb40-eyebrow">ADMINISTRASI / DETAIL PENGGUNA</p>
                    <h1>Detail <span>Akun</span></h1>
                    <p class="fb40-subtitle">Informasi akun dan pengaturan akses dalam satu halaman.</p>
                </div>
                <div class="fb40-title-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 21v-2a8 8 0 0 1 16 0v2" />
                    </svg></div>
            </div>
        </div>
        @foreach (['success', 'error', 'info'] as $flash)
            @if (session($flash))
                <div class="fb-users-alert fb-users-alert-{{ $flash }}" role="status">{{ session($flash) }}</div>
            @endif
        @endforeach
        @if ($errors->any())
            <div class="fb-users-alert fb-users-alert-error" role="alert">{{ $errors->first() }}</div>
        @endif
        <div class="fb40-layout">
            <div class="fb40-main">
                <section class="fb40-card fb40-identity" aria-labelledby="fb40-profile-title">
                    <div class="fb40-profile">
                        <div class="fb40-avatar">{{ strtoupper(mb_substr($user->name ?? '?', 0, 1)) }}</div>
                        <div class="fb40-profile-text">
                            <div class="fb40-name-row">
                                <h2 id="fb40-profile-title">{{ $user->name }}</h2>
                                <span
                                    class="fb-users-pill {{ $user->role === 'admin' ? 'is-admin' : '' }}">{{ $user->role === 'admin' ? 'Administrator' : 'Pengguna' }}</span>
                            </div>
                            <p>{{ $user->email }}</p>
                            <span class="fb40-id">ID akun {{ $user->id }}</span>
                        </div>
                    </div>
                </section>
                <section class="fb40-card fb42-activity" aria-labelledby="fb42-activity-title">
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
                <section class="fb40-card" aria-labelledby="fb40-info-title">
                    <div class="fb40-section-heading">
                        <div class="fb40-mini-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 21v-2a8 8 0 0 1 16 0v2" />
                            </svg></div>
                        <div>
                            <h2 id="fb40-info-title">Informasi Akun</h2>
                            <p>Data identitas dan status yang tersimpan pada sistem.</p>
                        </div>
                    </div>
                    <dl class="fb40-facts">
                        <div>
                            <dt>Hak Akses</dt>
                            <dd><span
                                    class="fb-users-pill {{ $user->role === 'admin' ? 'is-admin' : '' }}">{{ $user->role === 'admin' ? 'Administrator' : 'User' }}</span>
                            </dd>
                        </div>
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
                </section>
            </div>
            <aside class="fb40-side">
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
                        <div class="fb40-notice">Ini adalah akun Anda sendiri. Demi keamanan, role akun ini tidak dapat
                            diubah.</div>
                    @else
                        <form method="POST" action="{{ route('admin.users.update-role', $user) }}"
                            class="fb40-role-form" data-confirm="Yakin ingin mengubah hak akses akun ini?"
                            data-confirm-event="submit">
                            @csrf
                            @method('PATCH')
                            <label for="fb40-role">Role Pengguna</label>
                            <select name="role" id="fb40-role" required>
                                <option value="user" @selected(old('role', $user->role) === 'user')>User - akses pengguna biasa</option>
                                <option value="admin" @selected(old('role', $user->role) === 'admin')>Administrator - akses panel admin
                                </option>
                            </select>
                            <p class="fb40-helper">Perubahan role memengaruhi hak akses akun. Pastikan pilihan sudah benar.
                            </p>
                            <button type="submit" class="fb40-save">Simpan Perubahan Role</button>
                        </form>
                    @endif
                    <div class="fb40-security">
                        <div class="fb40-security-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                                <path d="m9 12 2 2 4-4" />
                            </svg></div>
                        <div><strong>Perlindungan Akun</strong>
                            <p>Administrator tidak dapat mengubah role sendiri. Sistem juga mencegah hilangnya administrator
                                terakhir.</p>
                        </div>
                    </div>
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
                            <dd>{{ $user->created_at?->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') ?? '-' }}
                                WIB</dd>
                        </div>
                        <div>
                            <dt>Terakhir Diperbarui</dt>
                            <dd>{{ $user->updated_at?->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') ?? '-' }}
                                WIB</dd>
                        </div>
                    </dl>
                </section>
                <section class="fb40-card fb40-note">
                    <div class="fb40-mini-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 11v5M12 8h.01" />
                        </svg></div>
                    <div>
                        <h2>Privasi dan Keamanan</h2>
                        <p>Password, token sesi, dan identitas Google internal tidak ditampilkan pada halaman ini.</p>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
