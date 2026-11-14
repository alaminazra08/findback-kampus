@extends('admin.layouts.items')

@section('title', 'Manajemen Akun')
@section('breadcrumb', 'Manajemen Akun')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/header-consistency.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-users.css') }}?v=36">
    <link rel="stylesheet" href="{{ asset('css/admin/users-list.css') }}?v=39">
    <link rel="stylesheet" href="{{ asset('css/admin/users-filter-base.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/users-filter-claims-alignment.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/users-filter-compact.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/users-filter-final.css') }}?v=4">
    {{-- FINDBACK_USERS_MOBILE_COMPACT_V1 --}}
    <link rel="stylesheet" href="{{ asset('css/admin/users-mobile-compact.css') }}?v=1">
@endsection

@section('content')

    <div class="fb-users-page">
        <div class="fb-users-heading fb-work-heading">
            <div>
                <p class="fb-users-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
                <h1>Manajemen <span>Akun</span></h1>
                <p>Kelola pengguna dan hak akses FindBack Kampus dengan aman.</p>
            </div>
            <span class="fb-users-heading-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8" />
                </svg></span>
        </div>

        @foreach (['success', 'error', 'info'] as $flash)
            @if (session($flash))
                <div class="fb-users-alert fb-users-alert-{{ $flash }}" role="status">{{ session($flash) }}</div>
            @endif
        @endforeach

        <div class="fb-users-stats">
            <article><span>Total Akun</span><strong>{{ number_format($stats['total'], 0, ',', '.') }}</strong><small>Seluruh
                    pengguna terdaftar</small></article>
            <article><span>Administrator</span><strong>{{ number_format($stats['admin'], 0, ',', '.') }}</strong><small>Akun
                    pengelola sistem</small></article>
            <article><span>Pengguna Biasa</span><strong>{{ number_format($stats['user'], 0, ',', '.') }}</strong><small>Akun
                    dengan role user</small></article>
            <article><span>Akun
                    Google</span><strong>{{ number_format($stats['google'], 0, ',', '.') }}</strong><small>Terhubung dengan
                    Google OAuth</small></article>
        </div>

        <section class="fb-users-panel" aria-labelledby="fb-users-list-heading">
            <div class="fb-users-panel-head">
                <div>
                    <h2 id="fb-users-list-heading">Daftar Pengguna</h2>
                    <p>Informasi akun berasal dari tabel <code>users</code>.</p>
                </div>
                <span class="fb-users-count">{{ $users->total() }} hasil</span>
            </div>

            <form method="GET" action="{{ route('admin.users.index') }}" class="fb-users-filters">
                <label class="fb-users-search">
                    <span class="sr-only">Cari pengguna</span>
                    <input type="search" name="q" value="{{ $q }}" maxlength="100"
                        placeholder="Cari nama atau email...">
                </label>
                <label>
                    <span class="sr-only">Filter role</span>
                    <select name="role">
                        <option value="all" @selected($role === 'all')>Semua role</option>
                        <option value="admin" @selected($role === 'admin')>Admin</option>
                        <option value="user" @selected($role === 'user')>User</option>
                    </select>
                </label>
                <label>
                    <span class="sr-only">Filter pendaftaran</span>
                    <select name="provider">
                        <option value="all" @selected($provider === 'all')>Semua metode</option>
                        <option value="google" @selected($provider === 'google')>Akun Google</option>
                        <option value="email" @selected($provider === 'email')>Email / biasa</option>
                    </select>
                </label>
                <button type="submit" class="fb-users-button"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5L4 5z"/></svg><span>Terapkan</span></button>
                <a class="fb-users-reset" href="{{ route('admin.users.index') }}" aria-label="Atur ulang filter pencarian"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true"><path d="M4 8.5H13.5C17.5 8.5 20 11 20 14.5C20 18 17.5 20 14 20H10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M4.2 8.5L10 3.5V13.5L4.2 8.5Z" fill="currentColor"/></svg><span>Atur Ulang</span></a>
            </form>

            <div class="fb-users-table-scroll">
                <table class="fb-users-table">
                    <thead>
                        <tr>
                            <th>Pengguna</th>
                            <th>Role</th>
                            <th>Metode</th>
                            <th>Verifikasi Email</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $account)
                            <tr>
                                <td>
                                    <div class="fb-users-person"><span
                                            class="fb-users-avatar">{{ strtoupper(mb_substr($account->name ?? '?', 0, 1)) }}</span>
                                        <div><strong>{{ $account->name }}</strong><small>{{ $account->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span
                                        class="fb-users-pill {{ $account->role === 'admin' ? 'is-admin' : '' }}">{{ $account->role === 'admin' ? 'Administrator' : 'User' }}</span>
                                </td>
                                <td>{{ $account->google_id ? 'Google' : 'Email / biasa' }}</td>
                                <td><span
                                        class="fb-users-verified {{ $account->email_verified_at ? 'yes' : '' }}">{{ $account->email_verified_at ? 'Terverifikasi' : 'Belum verifikasi' }}</span>
                                </td>
                                <td>{{ $account->created_at?->timezone('Asia/Jakarta')->format('d/m/Y') ?? 'â€”' }}</td>
                                <td><a class="fb-users-detail-link" href="{{ route('admin.users.show', $account) }}"><span
                                            class="fb-users-detail-icon"><svg xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M2.1 12a10.8 10.8 0 0 1 19.8 0 10.8 10.8 0 0 1-19.8 0Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg></span><span>Lihat Detail</span></a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="fb-users-empty">Tidak ada akun yang sesuai dengan pencarian atau
                                    filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="fb-users-pagination">
                    <span>Menampilkan {{ $users->firstItem() }}â€“{{ $users->lastItem() }} dari {{ $users->total() }}
                        akun</span>
                    <div>
                        @if ($users->onFirstPage())
                            <span class="is-disabled">â† Sebelumnya</span>
                        @else
                            <a href="{{ $users->previousPageUrl() }}">â† Sebelumnya</a>
                        @endif
                        <span>Hal. {{ $users->currentPage() }} / {{ $users->lastPage() }}</span>
                        @if ($users->hasMorePages())
                            <a href="{{ $users->nextPageUrl() }}">Berikutnya â†’</a>
                        @else
                            <span class="is-disabled">Berikutnya â†’</span>
                        @endif
                    </div>
                </div>
            @endif
        </section>
        <p class="fb-users-footnote">Untuk keamanan, password, token sesi, dan identitas Google internal tidak ditampilkan.
        </p>
    </div>
@endsection
