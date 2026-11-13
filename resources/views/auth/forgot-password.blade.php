<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#850c2d">
    <title>FindBack Kampus</title>
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/auth/password-recovery.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/view-utilities.css') }}">
    <script src="{{ asset('js/shared/view-interactions.js') }}"></script>
<!-- FINDBACK_TYPOGRAPHY_V1 -->
<link rel="stylesheet" href="{{ asset('css/shared/typography.css') }}">
</head>

<body class="fb-responsive fb-recovery-page">
    <main class="fb-recovery-card">
        <a class="fb-recovery-brand" href="{{ route('home') }}"><img src="{{ asset('images/logo-findback.png') }}"
                alt="" width="42" height="42"><span>FindBack <b>Kampus</b></span></a>
        <p class="fb-recovery-kicker">KEAMANAN AKUN</p>
        <h1>Lupa kata sandi?</h1>
        <p class="fb-recovery-info">Masukkan email yang terdaftar. Kami akan mengirimkan tautan untuk membuat kata sandi
            baru.</p>
        @if (session('status'))
            <div class="fb-recovery-status" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="fb-recovery-error" role="alert">Periksa kembali alamat email.</div>
        @endif
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label for="email">Alamat email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email"
                placeholder="nama@email.com" maxlength="255" required autofocus>
            @error('email')
                <p class="fb-recovery-validation">{{ $message }}</p>
            @enderror
            <button type="submit">Kirim tautan pemulihan <span aria-hidden="true">→</span></button>
        </form>
        <p class="fb-recovery-footer"><a href="{{ route('login') }}">← Kembali ke halaman Login</a></p>
    </main>
    <link rel="stylesheet" href="{{ asset('css/shared/responsive-all-devices.css') }}?v=20261111">
    <script src="{{ asset('js/shared/responsive-viewport.js') }}?v=20261111" defer></script>
</body>

</html>
