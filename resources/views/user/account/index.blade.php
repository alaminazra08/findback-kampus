@extends('user.layouts.campus')

@section('title', 'Akun')

@section('content')
@php
    $accountUser = auth()->user();
@endphp

<style>
    /* Account hub: kanvas putih dan ukuran menu yang seimbang di semua layar. */
    body.cu-account-hub-page,
    body.cu-account-hub-page .cu-main,
    body.cu-account-hub-page .cu-footer {
        background:#fff !important;
    }
    body.cu-account-hub-page .cu-main {
        width:100%;
        max-width:none;
        min-height:calc(100vh - 170px);
        margin:0;
        padding:26px 0 34px;
    }
    .fb-account-hub {
        width:100%;
        max-width:none;
        margin:0 auto;
        color:#24151b;
        background:#fff;
    }
    .fb-account-hub * { box-sizing:border-box; }
    .fb-account-hub .fba-heading {
        margin:0;
        padding:0 clamp(28px,5vw,72px) 7px;
        font-size:clamp(23px,2.1vw,28px);
        font-weight:750;
        letter-spacing:-.025em;
    }
    .fb-account-hub .fba-subtitle {
        margin:0;
        padding:0 clamp(28px,5vw,72px) 20px;
        color:#81747a;
        font-size:13px;
        line-height:1.55;
    }
    .fb-account-hub .fba-menu {
        overflow:hidden;
        border:0;
        background:#fff;
    }
    .fb-account-hub .fba-item,
    .fb-account-hub .fba-logout button {
        width:100%;
        display:flex;
        align-items:center;
        gap:17px;
        min-height:88px;
        padding:15px clamp(28px,5vw,72px);
        border:0;
        border-bottom:1px solid #e9e5e7;
        border-radius:0;
        background:#fff;
        color:#292326;
        text-align:left;
        text-decoration:none;
        cursor:pointer;
        transition:background .18s ease;
    }
    .fb-account-hub .fba-item:first-child { border-top:1px solid #e9e5e7; }
    .fb-account-hub .fba-item:hover,
    .fb-account-hub .fba-logout button:hover { background:#fff9fb; }
    .fb-account-hub .fba-icon {
        flex:0 0 42px;
        width:42px;
        height:42px;
        display:grid;
        place-items:center;
        color:#393237;
    }
    .fb-account-hub .fba-icon svg { width:27px; height:27px; }
    .fb-account-hub .fba-copy { flex:1; min-width:0; }
    .fb-account-hub .fba-copy strong {
        display:block;
        font-size:16px;
        font-weight:650;
    }
    .fb-account-hub .fba-copy small {
        display:block;
        margin-top:4px;
        color:#898186;
        font-size:12px;
        line-height:1.4;
        overflow-wrap:anywhere;
    }
    .fb-account-hub .fba-chevron {
        flex:0 0 auto;
        color:#858087;
        font-size:29px;
        line-height:1;
        font-weight:400;
    }
    .fb-account-hub .fba-logout { margin:0; border:0; }
    .fb-account-hub .fba-logout button {
        border-bottom:1px solid #e9e5e7;
        color:#8e1238;
        font:inherit;
    }
    .fb-account-hub .fba-logout .fba-icon { color:#8e1238; }
    .fb-account-hub .fba-version {
        margin:clamp(70px,13vh,145px) 0 8px;
        color:#898186;
        text-align:center;
        font-size:12px;
    }

    /* Desktop: isi tetap lapang tetapi tidak memakai baris yang terlalu tinggi. */
    @media(min-width:1000px) {
        body.cu-account-hub-page .cu-main { padding-top:24px; }
        .fb-account-hub .fba-item,
        .fb-account-hub .fba-logout button { min-height:82px; }
    }

    /* Mobile mengikuti pola EdLink: header marun atas disembunyikan,
       navigasi bawah tetap tersedia untuk berpindah halaman. */
    @media(max-width:600px) {
        body.cu-account-hub-page .cu-header { display:none !important; }
        body.cu-account-hub-page .cu-main {
            min-height:calc(100svh - 72px);
            padding:20px 0 16px;
        }
        .fb-account-hub { width:100%; margin:0; }
        .fb-account-hub .fba-heading {
            padding:0 22px 5px;
            font-size:22px;
        }
        .fb-account-hub .fba-subtitle {
            padding:0 22px 15px;
            font-size:12px;
        }
        .fb-account-hub .fba-item,
        .fb-account-hub .fba-logout button {
            min-height:76px;
            gap:13px;
            padding:13px 22px;
        }
        .fb-account-hub .fba-icon {
            flex-basis:38px;
            width:38px;
            height:38px;
        }
        .fb-account-hub .fba-icon svg { width:26px; height:26px; }
        .fb-account-hub .fba-copy strong { font-size:15px; }
        .fb-account-hub .fba-copy small { font-size:11px; }
        .fb-account-hub .fba-chevron { font-size:27px; }
        .fb-account-hub .fba-version { margin:clamp(65px,14vh,125px) 0 4px; font-size:12px; }
        body.cu-account-hub-page .cu-footer { display:none !important; }
    }
    @media(max-width:360px) {
        .fb-account-hub .fba-item,
        .fb-account-hub .fba-logout button { padding-left:18px; padding-right:18px; gap:10px; }
        .fb-account-hub .fba-heading,
        .fb-account-hub .fba-subtitle { padding-left:18px; padding-right:18px; }
    }
    @media(prefers-reduced-motion:reduce) {
        .fb-account-hub .fba-item,
        .fb-account-hub .fba-logout button { transition:none; }
    }
@media(min-width:801px) {
 body.cu-account-hub-page, body.cu-account-hub-page .cu-main, body.cu-account-hub-page .cu-footer { background:#f7f7f4 !important; }
 body.cu-account-hub-page .cu-main { width:calc(100% - 60px); max-width:1280px; margin:0 auto; padding:30px 0 40px; min-height:calc(100vh - 170px); }
 .fb-account-hub { background:transparent; }
 .fb-account-hub .fba-heading { padding:0 0 7px; }
 .fb-account-hub .fba-subtitle { padding:0 0 22px; }
 .fb-account-hub .fba-menu { border:1px solid #e9e5e7; border-radius:18px; }
 .fb-account-hub .fba-item,.fb-account-hub .fba-logout button { min-height:76px; padding:14px 24px; }
 .fb-account-hub .fba-item:first-child { border-top:0; }
 .fb-account-hub .fba-logout button { border-bottom:0; }
 .fb-account-hub .fba-version { display:none; }
}
</style>

<div class="fb-account-hub">
    <h1 class="fba-heading">Akun</h1>
    <p class="fba-subtitle">Pengaturan profil dan akses akun FindBack Kampus.</p>

    <nav class="fba-menu" aria-label="Menu akun">
        <a class="fba-item" href="{{ route('user.profile.index') }}">
            <span class="fba-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="3.5"/><path d="M2.5 20c.8-3.2 3-5 6.5-5 2.1 0 3.8.6 5 1.7"/><path d="m15 17 4.8-4.8a1.8 1.8 0 0 1 2.5 2.5L17.5 19.5 14 20z"/></svg>
            </span>
            <span class="fba-copy"><strong>Kelola Profil</strong><small>{{ $accountUser->name }} · Informasi pribadi dan foto profil</small></span>
            <span class="fba-chevron" aria-hidden="true">›</span>
        </a>

        <a class="fba-item" href="{{ route('user.account.security') }}">
            <span class="fba-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 10V7a7 7 0 0 1 14 0v3"/><rect x="3" y="10" width="18" height="11" rx="2"/><path d="M12 14v3"/></svg>
            </span>
            <span class="fba-copy"><strong>Kelola Akun</strong><small>Kata sandi dan keamanan akun</small></span>
            <span class="fba-chevron" aria-hidden="true">›</span>
        </a>
    </nav>

    <form class="fba-logout" method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Yakin ingin keluar dari akun FindBack Kampus?');">
        @csrf
        <button type="submit">
            <span class="fba-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M12 3h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-6"/></svg>
            </span>
            <span class="fba-copy"><strong>Keluar</strong><small>Keluar dari akun FindBack Kampus</small></span>
            <span class="fba-chevron" aria-hidden="true">›</span>
        </button>
    </form>
    <p class="fba-version">FindBack Kampus · © {{ date('Y') }}</p>
</div>
@endsection
