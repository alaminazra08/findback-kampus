<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#850c2d">
    <title>Atur ulang kata sandi | {{ $fbBrand['site_name'] }}</title>
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/auth/password-recovery.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/view-utilities.css') }}">
    <script src="{{ asset('js/shared/view-interactions.js') }}"></script>
</head>

<body class="fb-recovery-page">
    <main class="fb-recovery-card">
        <a class="fb-recovery-brand" href="{{ route('home') }}"><img src="{{ asset('images/logo-findback.png') }}"
                alt="" width="42" height="42"><span>FindBack <b>Kampus</b></span></a>
        <p class="fb-recovery-kicker">KEAMANAN AKUN</p>
        <h1>Atur ulang kata sandi</h1>
        <p class="fb-recovery-info">Buat kata sandi baru dengan minimal 8 karakter.</p>
        @if ($errors->any())
            <div class="fb-recovery-error" role="alert">Periksa kembali data pemulihan.</div>
        @endif
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label for="email">Alamat email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email"
                required>
            @error('email')
                <p class="fb-recovery-validation">{{ $message }}</p>
            @enderror
            <label for="password">Kata sandi baru</label>
            <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
            @error('password')
                <p class="fb-recovery-validation">{{ $message }}</p>
            @enderror
            <label for="password_confirmation">Konfirmasi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                minlength="8" required>
            <button type="submit">Simpan kata sandi baru <span aria-hidden="true">→</span></button>
        </form>
        <p class="fb-recovery-footer"><a href="{{ route('login') }}">← Kembali ke halaman Login</a></p>
    </main>
</body>

</html>
