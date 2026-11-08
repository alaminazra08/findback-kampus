@extends('user.layouts.campus')
@section('title', 'Profil Saya')
@section('content')
    <div class="cu-title cu-profile-v11-heading">
        <h1>Profil saya</h1>
        <p>Kelola identitas akun dan keamanan aksesmu.</p>
    </div>
    <div class="cu-profile-layout cu-profile-v11">
        <aside class="cu-panel"><span
                class="cu-avatar large">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <h2>{{ auth()->user()->name }}</h2>
            <p class="cu-muted">Civitas Akademika FST</p><a href="#info">Informasi profil</a><a href="#password">Ubah
                password</a><a href="{{ route('user.notifications.index') }}">Notifikasi</a>
        </aside>
        <div>
            <form id="info" class="cu-panel" method="POST" action="{{ route('user.profile.update') }}">@csrf<h2>
                    Informasi profil</h2><label>Nama *<input name="name" required maxlength="255"
                        value="{{ old('name', auth()->user()->name) }}"></label><label>Email<input
                        value="{{ auth()->user()->email }}" readonly></label>
                <p class="cu-muted">Fakultas Sains dan Teknologi · UIN Imam Bonjol Padang</p><button class="cu-btn">Simpan
                    perubahan</button>
            </form>
            <form id="password" class="cu-panel" method="POST" action="{{ route('user.profile.password') }}">@csrf<h2>Ubah
                    password</h2><label>Password saat ini<input type="password" name="current_password"
                        autocomplete="current-password" required></label><label>Password baru<input type="password"
                        name="password" autocomplete="new-password" minlength="8" required></label><label>Konfirmasi
                    password baru<input type="password" name="password_confirmation" autocomplete="new-password"
                        required></label><button class="cu-btn">Perbarui password</button></form>
        </div>
    </div>
@endsection
