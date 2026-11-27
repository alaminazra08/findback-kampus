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
    .fbp-profile-screen {
        --fbp-wine: #850d35;
        --fbp-ink: #252326;
        --fbp-muted: #887b82;
        box-sizing: border-box;
        position: relative;
        width: min(100%, 920px);
        max-width: 920px;
        margin: 0 auto 40px;
        overflow: hidden;
        border: 1px solid #eee3e8;
        border-radius: 20px;
        background: #fff;
        color: var(--fbp-ink);
        box-shadow: 0 12px 34px rgba(48,20,31,.055);
    }
    .fbp-profile-screen *,
    .fbp-profile-screen *::before,
    .fbp-profile-screen *::after { box-sizing: border-box; }

    /* Header mengikuti kerangka header Edit Profil */
    .fbp-profile-topbar {
        min-height: 76px;
        display: flex;
        align-items: center;
        gap: 16px;
        margin: 0;
        padding: 14px 28px;
        border: 0;
        border-bottom: 1px solid #e9e7e8;
        background: #fff;
    }
    .fbp-profile-back {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: grid;
        place-items: center;
        border: 1px solid #d9d6d8;
        border-radius: 50%;
        color: #252326;
        background: #fff;
        text-decoration: none;
        font-size: 23px;
        line-height: 1;
        transition: background .18s, border-color .18s;
    }
    .fbp-profile-back:hover {
        background: #faf5f7;
        border-color: #c68ca1;
    }
    .fbp-profile-topbar h1 {
        margin: 0;
        color: #252326;
        font-size: 23px;
        font-weight: 750;
        line-height: 1.3;
        letter-spacing: -.5px;
    }
    .fbp-profile-topbar p {
        margin: 4px 0 0;
        color: #887b82;
        font-size: 13px;
        line-height: 1.45;
    }

    .fbp-profile-content { width: 100%; background: #fff; }

    .fbp-profile-hero {
        min-height: 172px;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 24px;
        padding: 26px 30px;
        border-bottom: 1px solid #f0e4e9;
        background: linear-gradient(115deg,#fff 0%,#fff7fa 100%);
        text-align: left;
    }
    .fbp-profile-avatar {
        width: 124px;
        height: 124px;
        flex: 0 0 124px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border-radius: 50%;
        background: #f3dce6;
        color: var(--fbp-wine);
        font-size: 42px;
        font-weight: 800;
    }
    .fbp-profile-avatar img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }
    .fbp-profile-summary { min-width: 0; flex: 1 1 auto; }
    .fbp-profile-summary h2 {
        margin: 0;
        color: #252326;
        font-size: 23px;
        font-weight: 800;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }
    .fbp-profile-institution {
        margin: 6px 0 0;
        color: #887b82;
        font-size: 14px;
        line-height: 1.55;
    }
    .fbp-profile-role {
        display: inline-flex;
        margin: 10px 0 0;
        padding: 7px 12px;
        border-radius: 999px;
        background: #f9eaf0;
        color: var(--fbp-wine);
        font-size: 11px;
        font-weight: 750;
    }

    .fbp-profile-body { padding: 28px 30px 32px; }
    .fbp-profile-body h3 {
        margin: 0 0 14px;
        color: #252326;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.4;
    }
    .fbp-profile-data {
        display: grid;
        grid-template-columns: repeat(3,minmax(0,1fr));
        gap: 0 24px;
        margin: 0;
    }
    .fbp-profile-row {
        min-width: 0;
        padding: 16px 0;
        border-bottom: 1px solid #eee7ea;
    }
    .fbp-profile-row dt {
        margin: 0 0 8px;
        color: #93858c;
        font-size: 12px;
        font-weight: 700;
    }
    .fbp-profile-row dd {
        margin: 0;
        color: #252326;
        font-size: 14px;
        font-weight: 650;
        line-height: 1.6;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .fbp-profile-row dd.is-empty { color: #a69ba0; font-weight: 500; }
    .fbp-profile-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 22px;
    }
    .fbp-profile-edit {
        min-width: 180px;
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 0 22px;
        border: 0;
        border-radius: 12px;
        background: linear-gradient(110deg,#650b2b,#a01649);
        color: #fff;
        text-decoration: none;
        font-size: 14px;
        font-weight: 750;
        box-shadow: 0 7px 16px rgba(133,13,53,.15);
        transition: transform .18s, box-shadow .18s;
    }
    .fbp-profile-edit:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(133,13,53,.2);
    }

    /* Samakan breakpoint dan lebar mobile dengan halaman Edit Profil */
    @media (max-width: 800px) {
        .fbp-profile-screen {
            width: min(100%,620px);
            max-width: 620px;
            margin: 0 auto 28px;
            border-radius: 0;
            border-left: 0;
            border-right: 0;
            box-shadow: none;
        }
        .fbp-profile-topbar {
            min-height: 68px;
            padding: 12px 20px;
            gap: 14px;
        }
        .fbp-profile-back {
            width: 42px;
            height: 42px;
            flex-basis: 42px;
        }
        .fbp-profile-topbar h1 { font-size: 21px; }
        .fbp-profile-topbar p { font-size: 12px; }
        .fbp-profile-hero {
            min-height: 0;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 24px 22px;
            text-align: center;
        }
        .fbp-profile-avatar {
            width: 128px;
            height: 128px;
            flex-basis: 128px;
        }
        .fbp-profile-summary { width: 100%; }
        .fbp-profile-summary h2 { font-size: 21px; }
        .fbp-profile-institution { font-size: 13px; }
        .fbp-profile-body { padding: 24px 22px 34px; }
        .fbp-profile-body h3 { font-size: 15px; }
        .fbp-profile-data { grid-template-columns: minmax(0,1fr); gap: 0; }
        .fbp-profile-row { padding: 14px 0; }
        .fbp-profile-actions { width: 100%; margin-top: 20px; }
        .fbp-profile-edit { width: 100%; min-height: 52px; }
    }
    @media (max-width: 380px) {
        .fbp-profile-topbar { padding: 12px 16px; }
        .fbp-profile-body { padding: 20px 16px 28px; }
        .fbp-profile-hero { padding: 22px 16px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .fbp-profile-screen *,
        .fbp-profile-screen *::before,
        .fbp-profile-screen *::after {
            transition: none !important;
            animation: none !important;
        }
    }
</style>

<div class="fbp-profile-screen">
    <header class="fbp-profile-topbar">
        <a class="fbp-profile-back" href="{{ route('user.account.index') }}" aria-label="Kembali ke halaman akun">‹</a>
        <div>
            <h1>Profil</h1>
            <p>Informasi pribadi akun FindBack Kampus.</p>
        </div>
    </header>

    <section class="fbp-profile-content">
        <div class="fbp-profile-hero">
            <div class="fbp-profile-avatar">
                @if($photoUrl)
                    <img id="fbp-profile-photo" src="{{ $photoUrl }}" alt="Foto profil {{ $user->name }}">
                    <span id="fbp-profile-initial" hidden>{{ $initial }}</span>
                @else
                    <span id="fbp-profile-initial">{{ $initial }}</span>
                    <img id="fbp-profile-photo" src="" alt="" hidden>
                @endif
            </div>
            <div class="fbp-profile-summary">
                <h2>{{ $user->name }}</h2>
                <p class="fbp-profile-institution">{{ $institution }}</p>
                <p class="fbp-profile-role">Pengguna FindBack Kampus</p>
            </div>
        </div>

        <div class="fbp-profile-body">
            <h3>Informasi Pribadi</h3>
            <dl class="fbp-profile-data">
                <div class="fbp-profile-row"><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                <div class="fbp-profile-row"><dt>Jenis Kelamin</dt><dd class="{{ $user->gender ? '' : 'is-empty' }}">{{ $user->gender ? ($user->gender === 'male' ? 'Laki-laki' : 'Perempuan') : 'Belum diisi' }}</dd></div>
                <div class="fbp-profile-row"><dt>Nomor Handphone</dt><dd class="{{ $user->phone ? '' : 'is-empty' }}">{{ $displayPhone ?: 'Belum diisi' }}</dd></div>
            </dl>
            <div class="fbp-profile-actions">
                <a class="fbp-profile-edit" href="{{ route('user.profile.edit') }}">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14 5 5 5M4 20l4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10z"/></svg>
                    Edit Profil
                </a>
            </div>
        </div>
    </section>
</div>

<script>
(() => {
    const photo = document.getElementById('fbp-profile-photo');
    const initial = document.getElementById('fbp-profile-initial');
    if (!photo || !initial || !photo.getAttribute('src')) return;
    photo.addEventListener('error', () => { photo.hidden = true; initial.hidden = false; });
    if (photo.complete && photo.naturalWidth === 0) { photo.hidden = true; initial.hidden = false; }
})();
</script>
@endsection
