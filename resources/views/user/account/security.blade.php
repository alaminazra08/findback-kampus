@extends('user.layouts.campus')

@section('title', 'Keamanan Akun')

@section('content')
<style>
    .fb-security-screen {
        --fb-wine:#850d35;
        --fb-wine-dark:#650b2b;
        --fb-ink:#252326;
        --fb-muted:#887b82;
        box-sizing:border-box;
        width:min(100%,920px);
        max-width:920px;
        margin:0 auto 40px;
        overflow:hidden;
        border:1px solid #eee3e8;
        border-radius:20px;
        background:#fff;
        color:var(--fb-ink);
        box-shadow:0 12px 34px rgba(48,20,31,.055);
    }
    .fb-security-screen *,
    .fb-security-screen *::before,
    .fb-security-screen *::after { box-sizing:border-box; }

    .fbs-topbar {
        min-height:76px;
        display:flex;
        align-items:center;
        gap:16px;
        padding:14px 28px;
        border-bottom:1px solid #e9e7e8;
        background:#fff;
    }
    .fbs-back {
        width:44px;
        height:44px;
        flex:0 0 44px;
        display:grid;
        place-items:center;
        border:1px solid #d9d6d8;
        border-radius:50%;
        background:#fff;
        color:#252326;
        text-decoration:none;
        font-size:23px;
        line-height:1;
        transition:background .18s,border-color .18s;
    }
    .fbs-back:hover { background:#faf5f7; border-color:#c68ca1; }
    .fbs-topbar h1 {
        margin:0;
        color:#252326;
        font-size:23px;
        font-weight:750;
        line-height:1.3;
        letter-spacing:-.5px;
    }
    .fbs-topbar p {
        margin:4px 0 0;
        color:var(--fb-muted);
        font-size:13px;
        line-height:1.45;
    }

    .fbs-content { padding:26px 30px 30px; }
    .fbs-intro {
        display:flex;
        align-items:center;
        gap:14px;
        margin:0 0 22px;
        padding:15px 17px;
        border:1px solid #f0e3e9;
        border-radius:15px;
        background:linear-gradient(115deg,#fff 0%,#fff8fb 100%);
    }
    .fbs-icon {
        width:44px;
        height:44px;
        flex:0 0 44px;
        display:grid;
        place-items:center;
        border-radius:13px;
        background:#faedf2;
        color:var(--fb-wine);
    }
    .fbs-icon svg { width:22px; height:22px; }
    .fbs-intro-copy { min-width:0; }
    .fbs-intro-copy h2 {
        margin:0;
        color:var(--fb-ink);
        font-size:16px;
        font-weight:800;
        line-height:1.4;
    }
    .fbs-intro-copy p {
        margin:3px 0 0;
        color:var(--fb-muted);
        font-size:12px;
        line-height:1.5;
    }

    .fbs-status {
        margin:0 0 17px;
        padding:11px 13px;
        border:1px solid #cfe8d8;
        border-radius:11px;
        background:#f0faf3;
        color:#176b3a;
        font-size:13px;
        font-weight:650;
        line-height:1.5;
    }
    .fbs-form {
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:16px 18px;
        align-items:start;
    }
    .fbs-field { min-width:0; }
    .fbs-field-current { grid-column:1 / -1; }
    .fb-security-screen label {
        display:block;
        margin:0 0 7px;
        color:#242226;
        font-size:14px;
        font-weight:700;
    }
    .fbs-input-wrap { position:relative; }
    .fb-security-screen input {
        display:block;
        width:100%;
        min-height:52px;
        padding:0 48px 0 14px;
        border:1px solid #dedee4;
        border-radius:12px;
        outline:none;
        background:#fff;
        color:var(--fb-ink);
        font:inherit;
        font-size:15px;
        transition:border-color .18s,box-shadow .18s;
    }
    .fb-security-screen input:focus {
        border-color:#a63b60;
        box-shadow:0 0 0 3px rgba(133,13,53,.09);
    }
    .fbs-toggle {
        position:absolute;
        top:50%;
        right:9px;
        width:34px;
        height:34px;
        display:grid;
        place-items:center;
        transform:translateY(-50%);
        border:0;
        border-radius:9px;
        background:transparent;
        color:#887b82;
        cursor:pointer;
    }
    .fbs-toggle:hover { background:#faedf2; color:var(--fb-wine); }
    .fbs-toggle svg { width:19px; height:19px; }
    .fbs-hint,.fbs-error {
        margin:6px 0 0;
        font-size:11px;
        line-height:1.45;
    }
    .fbs-hint { color:var(--fb-muted); }
    .fbs-error { color:#b42318; }
    .fbs-bottom-row {
        grid-column:1 / -1;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:16px;
        margin-top:3px;
    }
    .fbs-actions {
        display:flex;
        flex:0 0 auto;
        justify-content:flex-end;
        margin:0;
    }
    .fbs-submit {
        min-width:190px;
        min-height:50px;
        padding:0 22px;
        border:0;
        border-radius:12px;
        background:linear-gradient(110deg,var(--fb-wine-dark),#a01649);
        color:#fff;
        font-size:14px;
        font-weight:750;
        cursor:pointer;
        box-shadow:0 6px 15px rgba(133,13,53,.13);
        transition:filter .18s,transform .18s,box-shadow .18s;
    }
    .fbs-submit:hover {
        filter:brightness(1.06);
        transform:translateY(-1px);
        box-shadow:0 9px 18px rgba(133,13,53,.18);
    }
    .fbs-guidance {
        display:flex;
        flex:1 1 auto;
        align-items:center;
        gap:8px;
        min-width:0;
        margin:0;
        padding:10px 12px;
        border:1px solid #eee2e8;
        border-radius:12px;
        background:#fff9fb;
        color:#715d66;
        font-size:11px;
        line-height:1.45;
    }
    .fbs-guidance svg {
        width:17px;
        height:17px;
        flex:0 0 17px;
        margin-top:1px;
        color:var(--fb-wine);
    }

    @media(max-width:700px) {
        .fb-security-screen {
            width:min(100%,760px);
            margin:0 auto;
            border:0;
            border-radius:0;
            box-shadow:none;
        }
        .fbs-topbar { min-height:72px; gap:14px; padding:13px 22px; }
        .fbs-back { width:44px; height:44px; flex-basis:44px; }
        .fbs-topbar h1 { font-size:21px; }
        .fbs-topbar p { font-size:12px; }
        .fbs-content { padding:20px 22px 30px; }
        .fbs-intro { gap:11px; margin-bottom:19px; padding:13px; }
        .fbs-icon { width:40px; height:40px; flex-basis:40px; }
        .fbs-intro-copy h2 { font-size:15px; }
        .fbs-intro-copy p { font-size:11px; }
        .fbs-form { grid-template-columns:1fr; gap:15px; }
        .fbs-field-current,.fbs-actions { grid-column:auto; }
        .fb-security-screen label { font-size:13px; }
        .fb-security-screen input { min-height:50px; font-size:15px; }
        .fbs-bottom-row {
            display:flex;
            flex-direction:column-reverse;
            align-items:stretch;
            gap:13px;
            margin-top:3px;
        }
        .fbs-actions { display:flex; width:100%; }
        .fbs-submit { width:100%; min-height:50px; }
        .fbs-guidance { width:100%; align-items:flex-start; }
    }
    @media(max-width:380px) {
        .fbs-topbar { padding-right:16px; padding-left:16px; }
        .fbs-content { padding:18px 16px 28px; }
    }
    @media(prefers-reduced-motion:reduce) {
        .fb-security-screen *,
        .fb-security-screen *::before,
        .fb-security-screen *::after { transition:none !important; animation:none !important; }
    }
</style>

<main class="fb-security-screen">
    <header class="fbs-topbar">
        <a class="fbs-back" href="{{ route('user.account.index') }}" aria-label="Kembali ke halaman akun">‹</a>
        <div>
            <h1>Keamanan Akun</h1>
            <p>Kelola kata sandi dan keamanan akun FindBack Kampus.</p>
        </div>
    </header>

    <div class="fbs-content">
        @if (session('profile_toast'))
            <div class="fbs-status" role="status">{{ session('profile_toast') }}</div>
        @endif

        <section class="fbs-intro" aria-labelledby="fbs-password-title">
            <span class="fbs-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="10" width="16" height="11" rx="2"/>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/>
                </svg>
            </span>
            <div class="fbs-intro-copy">
                <h2 id="fbs-password-title">Ubah Kata Sandi</h2>
                <p>Gunakan kata sandi yang kuat untuk menjaga akun tetap aman.</p>
            </div>
        </section>

        <form class="fbs-form" method="POST" action="{{ route('user.profile.password') }}">
            @csrf

            <div class="fbs-field fbs-field-current">
                <label for="fbs-current-password">Kata sandi saat ini</label>
                <div class="fbs-input-wrap">
                    <input id="fbs-current-password" name="current_password" type="password" autocomplete="current-password" placeholder="••••••••" required aria-describedby="fbs-current-password-note">
                    <button class="fbs-toggle" type="button" data-password-toggle="fbs-current-password" aria-label="Tampilkan kata sandi saat ini" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <p class="fbs-hint" id="fbs-current-password-note">Masukkan kata sandi lama. Titik-titik hanya penanda, bukan kata sandi yang tersimpan.</p>
                @error('current_password') <p class="fbs-error">{{ $message }}</p> @enderror
            </div>

            <div class="fbs-field">
                <label for="fbs-new-password">Kata sandi baru</label>
                <div class="fbs-input-wrap">
                    <input id="fbs-new-password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                    <button class="fbs-toggle" type="button" data-password-toggle="fbs-new-password" aria-label="Tampilkan kata sandi baru" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <p class="fbs-hint">Minimal 8 karakter.</p>
                @error('password') <p class="fbs-error">{{ $message }}</p> @enderror
            </div>

            <div class="fbs-field">
                <label for="fbs-confirm-password">Konfirmasi kata sandi baru</label>
                <div class="fbs-input-wrap">
                    <input id="fbs-confirm-password" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                    <button class="fbs-toggle" type="button" data-password-toggle="fbs-confirm-password" aria-label="Tampilkan konfirmasi kata sandi" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <div class="fbs-bottom-row">
                <aside class="fbs-guidance">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>
                    </svg>
                    <span>Jangan gunakan kata sandi yang sama dengan akun lain dan jangan membagikannya kepada siapa pun.</span>
                </aside>
                <div class="fbs-actions">
                    <button class="fbs-submit" type="submit">Perbarui Kata Sandi</button>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('click', function (event) {
    const toggle = event.target.closest('[data-password-toggle]');
    if (!toggle) return;

    const input = document.getElementById(toggle.dataset.passwordToggle);
    if (!input) return;

    const reveal = input.type === 'password';
    input.type = reveal ? 'text' : 'password';
    toggle.setAttribute('aria-pressed', reveal ? 'true' : 'false');
    toggle.setAttribute('aria-label', (reveal ? 'Sembunyikan ' : 'Tampilkan ') + 'kata sandi');
});
</script>
@endsection
