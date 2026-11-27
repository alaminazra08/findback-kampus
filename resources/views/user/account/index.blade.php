@extends('user.layouts.campus')

@section('title', 'Akun')

@section('content')
@php
    $accountUser = auth()->user();
    $accountPhoto = $accountUser->profile_photo_path;
    $accountPhotoUrl = $accountPhoto && \Illuminate\Support\Facades\Storage::disk('public')->exists($accountPhoto)
        ? route('user.profile.photo', ['v' => basename($accountPhoto)])
        : null;
    $accountInitial = mb_strtoupper(mb_substr(trim($accountUser->name), 0, 1));
@endphp

<style>
    body.cu-account-hub-page, body.cu-account-hub-page .cu-main, body.cu-account-hub-page .cu-footer { background:#f8f5f7 !important; }
    body.cu-account-hub-page .cu-main { width:100%; max-width:none; margin:0; padding:clamp(20px,3vw,38px) 0 42px; min-height:calc(100vh - 150px); }
    .fb-account-hub { --wine:#850d35; --wine-dark:#650b2b; --ink:#2c2026; --muted:#887b82; width:min(100% - 40px,1180px); margin:0 auto; color:var(--ink); }
    .fb-account-hub * { box-sizing:border-box; }
    .fba-heading { margin:0; font-size:clamp(25px,2.4vw,32px); line-height:1.2; font-weight:800; letter-spacing:-.035em; }
    .fba-subtitle { margin:8px 0 24px; color:var(--muted); font-size:14px; line-height:1.6; }
    .fba-identity { display:flex; align-items:center; gap:18px; padding:22px 24px; margin-bottom:22px; border:1px solid #eedde5; border-radius:20px; background:linear-gradient(115deg,#fff 0%,#fff8fb 100%); box-shadow:0 8px 26px rgba(74,18,42,.045); }
    .fba-avatar { width:76px; height:76px; flex:0 0 76px; display:grid; place-items:center; overflow:hidden; border-radius:50%; background:#f3dce6; color:var(--wine); font-size:30px; font-weight:800; }
    .fba-avatar img { width:100%; height:100%; object-fit:cover; }
    .fba-identity-copy { min-width:0; flex:1; }
    .fba-identity-copy strong { display:block; overflow-wrap:anywhere; font-size:19px; font-weight:800; }
    .fba-identity-copy span { display:block; margin-top:5px; color:var(--muted); font-size:13px; overflow-wrap:anywhere; }
    .fba-identity-tag { display:inline-flex !important; width:max-content; max-width:100%; margin-top:9px !important; padding:5px 10px; border-radius:999px; background:#f9eaf0; color:var(--wine) !important; font-size:11px !important; font-weight:750; }
    .fba-section-title { margin:0 0 12px; font-size:15px; font-weight:800; }
    .fba-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
    .fba-card { min-width:0; display:flex; align-items:center; gap:15px; min-height:104px; padding:19px; border:1px solid #eee0e6; border-radius:17px; background:#fff; color:var(--ink); text-decoration:none; box-shadow:0 6px 20px rgba(74,18,42,.035); transition:transform .18s,border-color .18s,box-shadow .18s; }
    .fba-card:hover { transform:translateY(-2px); border-color:#dfb9c9; box-shadow:0 10px 24px rgba(74,18,42,.075); }
    .fba-icon { width:48px; height:48px; flex:0 0 48px; display:grid; place-items:center; border-radius:14px; background:#faedf2; color:var(--wine); }
    .fba-icon svg { width:24px; height:24px; }
    .fba-copy { min-width:0; flex:1; }
    .fba-copy strong { display:block; font-size:15px; font-weight:800; }
    .fba-copy small { display:block; margin-top:5px; color:var(--muted); font-size:12px; line-height:1.45; overflow-wrap:anywhere; }
    .fba-chevron { color:#a18e97; font-size:25px; line-height:1; }
    .fba-logout { margin:18px 0 0; }
    .fba-logout button { width:100%; display:flex; align-items:center; gap:14px; min-height:66px; padding:14px 19px; border:1px solid #f0dce4; border-radius:16px; background:#fff; color:#a01845; text-align:left; font:inherit; cursor:pointer; transition:background .18s,border-color .18s; }
    .fba-logout button:hover { background:#fff5f7; border-color:#e5bdcc; }
    .fba-logout .fba-copy small { color:#9a7e89; }
    @media(min-width:1000px) {
        body.cu-account-hub-page .cu-main { padding-top:34px; }
        .fba-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media(max-width:700px) {
        body.cu-account-hub-page .cu-header { display:none !important; }
        body.cu-account-hub-page .cu-main { min-height:calc(100svh - 72px); padding:20px 0 22px; }
        .fb-account-hub { width:calc(100% - 28px); }
        .fba-subtitle { margin-bottom:17px; font-size:13px; }
        .fba-identity { gap:13px; padding:16px; border-radius:17px; }
        .fba-avatar { width:58px; height:58px; flex-basis:58px; font-size:24px; }
        .fba-identity-copy strong { font-size:16px; }
        .fba-identity-copy span { font-size:11px; }
        .fba-grid { grid-template-columns:1fr; gap:10px; }
        .fba-card { min-height:78px; padding:14px; gap:12px; border-radius:14px; }
        .fba-icon { width:42px; height:42px; flex-basis:42px; border-radius:12px; }
        .fba-copy strong { font-size:14px; }
        .fba-copy small { font-size:11px; }
        .fba-logout button { min-height:62px; }
        body.cu-account-hub-page .cu-footer { display:none !important; }
    }
    @media(max-width:360px) { .fb-account-hub { width:calc(100% - 22px); } .fba-identity { padding:13px; } }
    @media(prefers-reduced-motion:reduce) { .fba-card,.fba-logout button { transition:none; } }
</style>

<main class="fb-account-hub">
    <h1 class="fba-heading">Akun</h1>
    <p class="fba-subtitle">Kelola profil, keamanan, dan pengaturan akun FindBack Kampus.</p>

    <section class="fba-identity" aria-label="Identitas akun">
        <div class="fba-avatar">
            @if($accountPhotoUrl)
                <img src="{{ $accountPhotoUrl }}" alt="Foto profil {{ $accountUser->name }}">
            @else
                {{ $accountInitial }}
            @endif
        </div>
        <div class="fba-identity-copy">
            <strong>{{ $accountUser->name }}</strong>
            <span>{{ $accountUser->email }}</span>
            <span class="fba-identity-tag">Pengguna FindBack Kampus</span>
        </div>
    </section>

    <h2 class="fba-section-title">Pengaturan akun</h2>
    <nav class="fba-grid" aria-label="Pengaturan akun">
        <a class="fba-card" href="{{ route('user.profile.index') }}">
            <span class="fba-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5"/><path d="M4 20c.8-3.7 3.4-5.6 8-5.6s7.2 1.9 8 5.6"/></svg></span>
            <span class="fba-copy"><strong>Kelola Profil</strong><small>Lihat dan perbarui informasi pribadi serta foto profil.</small></span>
            <span class="fba-chevron" aria-hidden="true">›</span>
        </a>
        <a class="fba-card" href="{{ route('user.account.security') }}">
            <span class="fba-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg></span>
            <span class="fba-copy"><strong>Keamanan Akun</strong><small>Ubah kata sandi dan jaga keamanan akun.</small></span>
            <span class="fba-chevron" aria-hidden="true">›</span>
        </a>
    </nav>

    <form class="fba-logout" method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Yakin ingin keluar dari akun FindBack Kampus?');">
        @csrf
        <button type="submit">
            <span class="fba-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M12 3h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-6"/></svg></span>
            <span class="fba-copy"><strong>Keluar</strong><small>Keluar dari akun FindBack Kampus.</small></span>
            <span class="fba-chevron" aria-hidden="true">›</span>
        </button>
    </form>
</main>
@endsection
