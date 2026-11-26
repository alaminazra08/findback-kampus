@extends('user.layouts.campus')

@section('title', 'Kelola akun')

@section('content')
<style>
    .fb-account-security { width:min(100% - 32px,680px); margin:0 auto 48px; color:#24151b; }
    .fb-account-security * { box-sizing:border-box; }
    .fb-account-security .fas-heading { display:flex; align-items:center; gap:13px; margin:0 0 20px; }
    .fb-account-security .fas-back { flex:0 0 44px; width:44px; height:44px; display:grid; place-items:center; border:1px solid #ead5de; border-radius:50%; background:#fff; color:#861239; text-decoration:none; font-size:24px; }
    .fb-account-security h1 { margin:0; font-size:clamp(24px,3vw,30px); font-weight:800; letter-spacing:-.03em; }
    .fb-account-security .fas-card { padding:clamp(20px,4vw,28px); border:1px solid #eee4e8; border-radius:20px; background:#fff; box-shadow:0 8px 24px rgba(54,10,27,.035); }
    .fb-account-security .fas-intro { margin:0 0 22px; color:#81747a; font-size:14px; line-height:1.6; }
    .fb-account-security .fas-field { margin-top:18px; }
    .fb-account-security label { display:block; margin:0 0 8px; font-size:14px; font-weight:750; }
    .fb-account-security input { width:100%; min-height:52px; padding:11px 14px; border:1px solid #e1dce0; border-radius:12px; background:#fff; color:#24151b; font:inherit; font-size:16px; }
    .fb-account-security input:focus { outline:0; border-color:#9b3155; box-shadow:0 0 0 3px rgba(137,18,57,.10); }
    .fb-account-security .fas-hint { margin:7px 0 0; color:#81747a; font-size:12px; }
    .fb-account-security .fas-error { margin:6px 0 0; color:#b42318; font-size:12px; }
    .fb-account-security .fas-submit { width:100%; min-height:52px; margin-top:24px; border:0; border-radius:13px; background:linear-gradient(110deg,#650b2b,#a01649); color:#fff; font-size:15px; font-weight:750; cursor:pointer; }
    @media(max-width:600px) { .fb-account-security { width:min(100% - 28px,480px); margin-bottom:34px; } .fb-account-security .fas-card { padding:19px 16px; border-radius:18px; } }
</style>

<div class="fb-account-security">
    <div class="fas-heading">
        <a class="fas-back" href="{{ route('user.account.index') }}" aria-label="Kembali ke menu akun">‹</a>
        <h1>Kelola Akun</h1>
    </div>

    @if (session('profile_toast'))
        <div role="status" style="margin:0 0 15px;padding:12px 14px;border:1px solid #cfe8d8;border-radius:12px;background:#f0faf3;color:#176b3a;font-weight:650;">{{ session('profile_toast') }}</div>
    @endif

    <section class="fas-card">
        <h2 style="margin:0 0 7px;font-size:19px;font-weight:800;">Keamanan akun</h2>
        <p class="fas-intro">Perbarui kata sandi secara berkala untuk menjaga keamanan akun FindBack Kampus.</p>

        <form method="POST" action="{{ route('user.profile.password') }}">
            @csrf
            <div class="fas-field">
                <label for="fas-current-password">Kata sandi saat ini</label>
                <input id="fas-current-password" name="current_password" type="password" autocomplete="current-password" required>
                @error('current_password') <p class="fas-error">{{ $message }}</p> @enderror
            </div>
            <div class="fas-field">
                <label for="fas-new-password">Kata sandi baru</label>
                <input id="fas-new-password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                <p class="fas-hint">Gunakan minimal 8 karakter.</p>
                @error('password') <p class="fas-error">{{ $message }}</p> @enderror
            </div>
            <div class="fas-field">
                <label for="fas-confirm-password">Konfirmasi kata sandi baru</label>
                <input id="fas-confirm-password" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
            </div>
            <button class="fas-submit" type="submit">Perbarui kata sandi</button>
        </form>
    </section>
</div>
@endsection
