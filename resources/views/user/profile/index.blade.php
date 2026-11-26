@extends('user.layouts.campus')

@section('title', 'Profil')

@section('content')
@php
    $user = auth()->user();
    $photoPath = $user->profile_photo_path;
    $photoUrl = $photoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath)
        ? route('user.profile.photo', ['v' => basename($photoPath)])
        : null;
    $initial = mb_strtoupper(mb_substr(trim($user->name), 0, 1));
    $institution = $fbBrand['institution_name'] ?? 'Universitas Islam Negeri Imam Bonjol Padang';
    $displayPhone = $user->phone;
    if ($displayPhone) {
        $rawPhone = preg_replace('/[^\d+]/', '', (string) $displayPhone);
        $countryCodes = [
            '+62', '+1', '+60', '+65', '+61', '+44', '+81', '+82', '+86', '+63',
            '+66', '+84', '+91', '+971', '+966', '+974', '+49', '+33', '+31',
            '+39', '+34', '+7', '+55', '+52', '+27', '+64', '+41', '+46',
            '+47', '+45', '+48', '+351', '+353', '+20', '+90', '+234', '+254',
        ];

        $countryCode = '';
        $nationalNumber = '';
        if (str_starts_with($rawPhone, '+')) {
            // Match the longest supported calling code, avoiding incorrect parsing
            // such as treating +628 as a country code instead of +62.
            usort($countryCodes, fn ($a, $b) => strlen($b) <=> strlen($a));
            foreach ($countryCodes as $code) {
                if (str_starts_with($rawPhone, $code)) {
                    $countryCode = $code;
                    $nationalNumber = preg_replace('/\D+/', '', substr($rawPhone, strlen($code)));
                    break;
                }
            }
        } else {
            $digits = preg_replace('/\D+/', '', $rawPhone);
            if (str_starts_with($digits, '62')) {
                $countryCode = '+62';
                $nationalNumber = substr($digits, 2);
            } elseif (str_starts_with($digits, '0')) {
                $countryCode = '+62';
                $nationalNumber = substr($digits, 1);
            } else {
                $nationalNumber = $digits;
            }
        }

        if ($countryCode !== '') {
            if ($countryCode === '+62') {
                // Indonesian mobile numbers are displayed in readable 3-4-4 groups.
                $nationalNumber = preg_replace('/^(\d{3})(\d{4})(\d+)?$/', '$1 $2 $3', $nationalNumber);
                $nationalNumber = trim(preg_replace('/\s+/', ' ', $nationalNumber));
            } else {
                // Keep other international numbers readable without changing their value.
                $nationalNumber = trim(chunk_split($nationalNumber, 3, ' '));
            }
            $displayPhone = trim($countryCode . ' ' . $nationalNumber);
        } else {
            $displayPhone = $rawPhone;
        }
    }
@endphp

<style>
    .fbp-profile-screen { --fbp-wine:#850d35; --fbp-wine-dark:#650b2b; --fbp-text:#252326; --fbp-muted:#929296; width:min(100%,680px); margin:0 auto; color:var(--fbp-text); background:#fff; }
    .fbp-profile-screen * { box-sizing:border-box; }
    .fbp-profile-topbar { min-height:78px; display:flex; align-items:center; gap:18px; padding:16px 30px; border-bottom:1px solid #e9e7e8; }
    .fbp-profile-back { width:54px; height:54px; flex:0 0 54px; display:grid; place-items:center; border:1px solid #d9d6d8; border-radius:50%; color:#252326; background:#fff; text-decoration:none; transition:background .18s,border-color .18s; }
    .fbp-profile-back:hover { background:#faf5f7; border-color:#c68ca1; }
    .fbp-profile-back svg { width:25px; height:25px; }
    .fbp-profile-topbar h1 { margin:0; font-size:23px; font-weight:750; letter-spacing:-.5px; }
    .fbp-profile-body { padding:34px 34px 44px; }
    .fbp-profile-identity { text-align:center; margin:8px auto 34px; }
    .fbp-profile-avatar { width:168px; height:168px; margin:0 auto 20px; overflow:hidden; display:grid; place-items:center; border-radius:50%; background:linear-gradient(135deg,#f7e7ed,#e8cbd6); color:var(--fbp-wine); font-size:54px; font-weight:800; }
    .fbp-profile-avatar img { display:block; width:100%; height:100%; object-fit:cover; object-position:center; }
    .fbp-profile-avatar [hidden] { display:none !important; }
    .fbp-profile-name { margin:0 0 8px; color:#29272a; font-size:29px; line-height:1.2; font-weight:800; letter-spacing:-.6px; overflow-wrap:anywhere; }
    .fbp-profile-institution { margin:0 0 7px; color:#85858a; font-size:20px; line-height:1.4; font-weight:400; }
    .fbp-profile-role { margin:0; color:var(--fbp-wine); font-size:19px; font-weight:650; }
    .fbp-profile-data { margin:0; }
    .fbp-profile-row { padding:21px 5px 17px; border-bottom:1px solid #d8d6d7; }
    .fbp-profile-row dt { margin:0 0 3px; color:#a2a1a3; font-size:17px; font-weight:650; }
    .fbp-profile-row dd { margin:0; color:#252326; font-size:21px; line-height:1.35; font-weight:700; overflow-wrap:anywhere; }
    .fbp-profile-row dd.is-empty { color:#9b999c; font-weight:500; }
    .fbp-profile-edit { display:flex; align-items:center; justify-content:center; width:100%; min-height:58px; margin-top:30px; border:1px solid var(--fbp-wine); border-radius:12px; background:linear-gradient(110deg,var(--fbp-wine-dark),#a1164b); color:#fff; font-size:18px; font-weight:750; text-decoration:none; transition:filter .18s,transform .18s; }
    .fbp-profile-edit:hover { color:#fff; filter:brightness(1.06); transform:translateY(-1px); }
    .fbp-profile-toast { margin:16px 30px 0; padding:12px 15px; border:1px solid #cce7d4; border-radius:10px; background:#f0faf3; color:#24633a; font-size:14px; }
    @media(max-width:600px) {
        .fbp-profile-topbar { min-height:78px; padding:13px 20px; gap:15px; }
        .fbp-profile-back { width:48px; height:48px; flex-basis:48px; }
        .fbp-profile-topbar h1 { font-size:21px; }
        .fbp-profile-body { padding:27px 22px 34px; }
        .fbp-profile-identity { margin:18px auto 27px; }
        .fbp-profile-avatar { width:142px; height:142px; margin-bottom:18px; font-size:46px; }
        .fbp-profile-name { font-size:25px; }
        .fbp-profile-institution { font-size:17px; }
        .fbp-profile-role { font-size:17px; }
        .fbp-profile-row { padding:18px 2px 14px; }
        .fbp-profile-row dt { font-size:15px; }
        .fbp-profile-row dd { font-size:19px; }
        .fbp-profile-edit { min-height:54px; margin-top:25px; font-size:17px; }
    }
    @media(max-width:360px) {
        .fbp-profile-body { padding-inline:18px; }
        .fbp-profile-avatar { width:128px; height:128px; }
        .fbp-profile-name { font-size:23px; }
        .fbp-profile-institution { font-size:15px; }
    }
    @media(prefers-reduced-motion:reduce) { .fbp-profile-screen * { transition:none !important; } }
@media(min-width:801px) {
 .fbp-profile-screen { width:100%; max-width:1000px; border:1px solid #e9e5e7; border-radius:20px; overflow:hidden; }
 .fbp-profile-topbar { min-height:64px; padding:12px 26px; gap:14px; }
 .fbp-profile-back { width:40px; height:40px; flex-basis:40px; }
 .fbp-profile-topbar h1 { font-size:23px; }
 .fbp-profile-body { display:grid; grid-template-columns:minmax(230px,1fr) minmax(0,1.45fr); gap:0 36px; padding:30px; align-items:start; }
 .fbp-profile-identity { grid-row:1 / 3; margin:0; padding:18px 18px 24px; border-right:1px solid #eee7ea; }
 .fbp-profile-avatar { width:120px; height:120px; margin-bottom:16px; font-size:40px; }
 .fbp-profile-name { font-size:24px; }
 .fbp-profile-institution { font-size:15px; }
 .fbp-profile-role { font-size:14px; }
 .fbp-profile-row { padding:13px 0; }
 .fbp-profile-row dt { font-size:13px; color:#746970; }
 .fbp-profile-row dd { font-size:16px; }
 .fbp-profile-screen a.fbp-profile-edit { grid-column:2; width:100%; min-height:44px; margin-top:20px; font-size:15px; color:#fff !important; }
}

    /* Final layout alignment: Profil follows the same card width and spacing system as Edit Profil. */
    @media (min-width: 801px) {
        .fbp-profile-screen {
            width: 100%;
            max-width: 1040px;
            border: 1px solid #e9e5e7;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 12px 34px rgba(48,20,31,.045);
        }
        .fbp-profile-topbar {
            min-height: 64px;
            padding: 12px 26px;
            gap: 14px;
        }
        .fbp-profile-back { width: 40px; height: 40px; flex-basis: 40px; }
        .fbp-profile-topbar h1 { font-size: 23px; }
        .fbp-profile-body {
            display: grid;
            grid-template-columns: minmax(190px, .72fr) minmax(0, 1.45fr);
            column-gap: 28px;
            row-gap: 0;
            align-items: start;
            padding: 24px 30px 27px;
        }
        .fbp-profile-identity {
            grid-row: 1 / 3;
            margin: 0;
            padding: 14px 20px 18px 8px;
            border-right: 1px solid #eee7ea;
        }
        .fbp-profile-avatar { width: 124px; height: 124px; margin-bottom: 16px; font-size: 42px; }
        .fbp-profile-name { font-size: 24px; }
        .fbp-profile-institution { font-size: 15px; }
        .fbp-profile-role { font-size: 14px; }
        .fbp-profile-data { min-width: 0; }
        .fbp-profile-row { padding: 11px 0; }
        .fbp-profile-row dt { font-size: 13px; color: #746970; }
        .fbp-profile-row dd { font-size: 16px; }
        .fbp-profile-screen a.fbp-profile-edit {
            grid-column: 2;
            width: 100%;
            min-height: 46px;
            margin-top: 10px;
            border-radius: 11px;
            font-size: 14px;
        }
    }
    @media (min-width: 601px) and (max-width: 800px) {
        .fbp-profile-screen { width: min(100%, 680px); }
        .fbp-profile-body { padding: 28px 30px 34px; }
    }
    @media (max-width: 600px) {
        .fbp-profile-screen { width: 100%; max-width: 680px; }
        .fbp-profile-topbar { min-height: 78px; padding: 13px 20px; gap: 15px; }
        .fbp-profile-back { width: 48px; height: 48px; flex-basis: 48px; }
        .fbp-profile-topbar h1 { font-size: 21px; }
        .fbp-profile-body { display: block; padding: 26px 22px 35px; }
        .fbp-profile-identity {
            margin: 18px auto 25px;
            padding: 0;
            border-right: 0;
        }
        .fbp-profile-avatar { width: 138px; height: 138px; margin-bottom: 18px; font-size: 46px; }
        .fbp-profile-name { font-size: 25px; }
        .fbp-profile-institution { font-size: 17px; }
        .fbp-profile-role { font-size: 17px; }
        .fbp-profile-row { padding: 15px 2px 13px; }
        .fbp-profile-row dt { font-size: 15px; }
        .fbp-profile-row dd { font-size: 17px; }
        .fbp-profile-screen a.fbp-profile-edit { min-height: 54px; margin-top: 22px; font-size: 15px; }
    }
    @media (max-width: 360px) {
        .fbp-profile-body { padding-inline: 17px; }
        .fbp-profile-avatar { width: 128px; height: 128px; }
        .fbp-profile-name { font-size: 23px; }
        .fbp-profile-institution { font-size: 15px; }
    }

</style>

<div class="fbp-profile-screen">
    <header class="fbp-profile-topbar">
        <a class="fbp-profile-back" href="{{ route('user.account.index') }}" aria-label="Kembali ke Akun">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <h1>Profil</h1>
    </header>

    @if (session('profile_toast'))
        <div class="fbp-profile-toast" role="status">{{ session('profile_toast') }}</div>
    @endif
    @if (session('success'))
        <div class="fbp-profile-toast" role="status">{{ session('success') }}</div>
    @endif

    <div class="fbp-profile-body">
        <section class="fbp-profile-identity" aria-label="Identitas pengguna">
            <div class="fbp-profile-avatar">
                <img
                    id="fbp-profile-photo"
                    src="{{ $photoUrl ?: '' }}"
                    alt="Foto profil {{ $user->name }}"
                    @unless($photoUrl) hidden @endunless
                >
                <span id="fbp-profile-initial" @if($photoUrl) hidden @endif>{{ $initial }}</span>
            </div>
            <h2 class="fbp-profile-name">{{ $user->name }}</h2>
            <p class="fbp-profile-institution">{{ $institution }}</p>
            <p class="fbp-profile-role">Pengguna FindBack Kampus</p>
        </section>

        <dl class="fbp-profile-data">
            <div class="fbp-profile-row">
                <dt>Email</dt>
                <dd>{{ $user->email }}</dd>
            </div>
            <div class="fbp-profile-row">
                <dt>Jenis Kelamin</dt>
                <dd class="{{ $user->gender ? '' : 'is-empty' }}">{{ $user->gender ? ($user->gender === 'male' ? 'Laki-laki' : 'Perempuan') : 'Belum diisi' }}</dd>
            </div>
            <div class="fbp-profile-row">
                <dt>Nomor Handphone</dt>
                <dd class="{{ $user->phone ? '' : 'is-empty' }}">{{ $displayPhone ?: 'Belum diisi' }}</dd>
            </div>
        </dl>

        <a class="fbp-profile-edit" href="{{ route('user.profile.edit') }}">Edit Profil</a>
    </div>
</div>

<script>
(() => {
    const photo = document.getElementById('fbp-profile-photo');
    const initial = document.getElementById('fbp-profile-initial');
    if (!photo || !initial) return;

    photo.addEventListener('error', () => {
        photo.hidden = true;
        initial.hidden = false;
    });

    if (photo.complete && photo.naturalWidth === 0 && photo.getAttribute('src')) {
        photo.hidden = true;
        initial.hidden = false;
    }
})();
</script>
@endsection
