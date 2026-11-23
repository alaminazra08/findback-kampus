@extends('admin.layouts.items')
@section('title', 'Profil Admin')
@section('breadcrumb', 'Profil Admin')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-profile-processing.css') }}?v=8">
@endsection
@section('content')
    @php($account = auth()->user())

    <div class="fb-admin-profile">
        <a class="fb-admin-profile-back" href="{{ route('admin.users.show', $account) }}">← Detail Akun</a>

        <header class="fb-admin-profile-heading">
            <p class="fb-admin-profile-kicker">ADMINISTRASI · MANAJEMEN AKUN</p>
            <h1 id="fb-admin-profile-title">Profil <span>Admin</span></h1>
            <p>Kelola foto profil yang tampil di panel admin dan Daftar Pengguna.</p>
        </header>
        <section class="fb-admin-profile-card" aria-labelledby="fb-admin-profile-title">
            <div class="fb-admin-profile-body">
                <div class="fb-admin-profile-identity">
                    <span class="fb-admin-profile-avatar" data-account-photo="{{ $account->id }}" aria-hidden="true">
                        @include('admin.users.profile-photo', ['account' => $account])
                    </span>
                    <strong>{{ $account->name }}</strong>
                    <span>{{ $account->email }}</span>
                </div>

                <div>

                    <form id="fb-admin-profile-save-form" data-fb-async-decision method="POST" enctype="multipart/form-data"
                          action="{{ route('admin.users.profile-photo.update') }}">
                        @csrf
                        <label for="fb-admin-profile-photo">Foto Profil</label>
                        <input id="fb-admin-profile-photo" name="photo" type="file"
                               accept="image/jpeg,image/png,image/webp" required
                               aria-describedby="fb-admin-profile-hint">
                        <small id="fb-admin-profile-hint">JPG, PNG, atau WebP, maksimal 2 MB.</small>
                    </form>

                    @if ($account->profile_photo_path)
                        <form id="fb-admin-profile-remove-form" data-fb-async-decision method="POST" action="{{ route('admin.users.profile-photo.update') }}"
                              data-confirm="Hapus foto profil Anda?" data-confirm-event="submit">
                            @csrf
                            <input type="hidden" name="remove_photo" value="1">
                        </form>
                    @endif

                    <div class="fb-admin-profile-actions">
                        <button type="submit" form="fb-admin-profile-save-form">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h12l4 4v12a2 2 0 0 1-2 2Z"/>
                                <path d="M7 3v6h10V3M7 21v-8h10v8"/>
                            </svg>
                            <span>Simpan Foto</span>
                        </button>
                        @if ($account->profile_photo_path)
                            <button type="submit" form="fb-admin-profile-remove-form" class="fb-admin-profile-remove">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <path d="M4 7h16M9 7V4h6v3m3 0-1 13H7L6 7m4 4v5m4-5v5"/>
                                </svg>
                                <span>Hapus Foto</span>
                            </button>
                        @endif
                    </div>
                    <div class="fb-profile-photo-status">
                        @if ($errors->any())
                            <p class="fb-admin-profile-error" role="alert">{{ $errors->first() }}</p>
                        @else
                            <p role="status" class="{{ session('success') ? 'fb-admin-profile-success' : '' }}">
                                {{ session('success') ?? ($account->profile_photo_path
                                    ? 'Foto profil terpasang.'
                                    : 'Belum ada foto profil.') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
