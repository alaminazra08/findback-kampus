@extends('user.layouts.campus')

@section('title', 'Edit Profil')

@section('content')
@php
    $user = auth()->user();
    $photoPath = $user->profile_photo_path;
    $photoUrl = $photoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath)
        ? route('user.profile.photo', ['v' => basename($photoPath)])
        : null;
    $initial = mb_strtoupper(mb_substr(trim(old('name', $user->name)), 0, 1));
    $phoneCountries = [
        ['+62', 'Indonesia'], ['+1', 'Amerika Serikat / Kanada'], ['+60', 'Malaysia'], ['+65', 'Singapura'],
        ['+61', 'Australia'], ['+44', 'Britania Raya'], ['+81', 'Jepang'], ['+82', 'Korea Selatan'],
        ['+86', 'Tiongkok'], ['+63', 'Filipina'], ['+66', 'Thailand'], ['+84', 'Vietnam'],
        ['+91', 'India'], ['+971', 'Uni Emirat Arab'], ['+966', 'Arab Saudi'], ['+974', 'Qatar'],
        ['+49', 'Jerman'], ['+33', 'Prancis'], ['+31', 'Belanda'], ['+39', 'Italia'], ['+34', 'Spanyol'],
        ['+7', 'Rusia / Kazakhstan'], ['+55', 'Brasil'], ['+52', 'Meksiko'], ['+27', 'Afrika Selatan'],
        ['+64', 'Selandia Baru'], ['+41', 'Swiss'], ['+46', 'Swedia'], ['+47', 'Norwegia'],
        ['+45', 'Denmark'], ['+48', 'Polandia'], ['+351', 'Portugal'], ['+353', 'Irlandia'],
        ['+20', 'Mesir'], ['+90', 'Turki'], ['+234', 'Nigeria'], ['+254', 'Kenya'],
    ];
    $selectedPhoneCode = '+62';
    $phoneNational = (string) old('phone_number', '');
    if ($phoneNational === '' && $user->phone) {
        $storedPhone = preg_replace('/\s+/', '', (string) $user->phone);
        if (str_starts_with($storedPhone, '+')) {
            $matches = array_filter($phoneCountries, fn ($country) => str_starts_with($storedPhone, $country[0]));
            usort($matches, fn ($a, $b) => strlen($b[0]) <=> strlen($a[0]));
            if ($matches) {
                $selectedPhoneCode = $matches[0][0];
                $phoneNational = substr($storedPhone, strlen($selectedPhoneCode));
            } else {
                $phoneNational = ltrim($storedPhone, '+');
            }
        } else {
            $phoneNational = preg_replace('/\D+/', '', $storedPhone);
            if (str_starts_with($phoneNational, '0')) $phoneNational = substr($phoneNational, 1);
            elseif (str_starts_with($phoneNational, '62')) $phoneNational = substr($phoneNational, 2);
        }
    }
    $selectedPhoneCode = old('phone_country_code', $selectedPhoneCode);
    $selectedPhoneCountry = 'Negara';
    foreach ($phoneCountries as [$countryCode, $countryName]) {
        if ($countryCode === $selectedPhoneCode) {
            $selectedPhoneCountry = $countryName;
            break;
        }
    }
@endphp

<style>
    .fbp-edit-screen { --fbp-wine:#850d35; --fbp-wine-dark:#650b2b; --fbp-text:#252326; width:min(100%,680px); margin:0 auto; color:var(--fbp-text); background:#fff; }
    .fbp-edit-screen * { box-sizing:border-box; }
    .fbp-edit-topbar { min-height:78px; display:flex; align-items:center; gap:18px; padding:16px 30px; border-bottom:1px solid #e9e7e8; }
    .fbp-edit-back { width:54px; height:54px; flex:0 0 54px; display:grid; place-items:center; border:1px solid #d9d6d8; border-radius:50%; color:#252326; background:#fff; text-decoration:none; }
    .fbp-edit-back:hover { background:#faf5f7; border-color:#c68ca1; }
    .fbp-edit-back svg { width:25px; height:25px; }
    .fbp-edit-topbar h1 { margin:0; font-size:23px; font-weight:750; letter-spacing:-.5px; }
    .fbp-edit-body { padding:30px 30px 45px; }
    .fbp-edit-photo-area { display:flex; justify-content:center; margin:8px auto 38px; }
    .fbp-edit-photo-control { position:relative; display:block; width:150px; height:150px; padding:0; border:0; border-radius:50%; background:#f5e6ec; color:var(--fbp-wine); font:inherit; line-height:1; cursor:pointer; }
    .fbp-edit-photo-image { position:relative; width:100%; height:100%; display:flex; align-items:center; justify-content:center; overflow:hidden; border:1px solid #e5d5dc; border-radius:50%; color:var(--fbp-wine); font-size:48px; line-height:1; font-weight:800; letter-spacing:0; white-space:nowrap; }
    .fbp-edit-photo-image img { position:absolute; inset:0; display:block; width:100%; height:100%; object-fit:cover; object-position:center; }
    .fbp-edit-photo-image [hidden] { display:none !important; }
    .fbp-edit-photo-image > span { display:flex; width:100%; height:100%; align-items:center; justify-content:center; overflow:hidden; font-size:clamp(38px, 5vw, 54px); line-height:1; font-weight:800; text-overflow:clip; }
    .fbp-edit-camera { position:absolute; right:-2px; bottom:-2px; width:54px; height:54px; display:grid; place-items:center; border:1px solid #dedadd; border-radius:50%; background:#fff; color:#262326; box-shadow:0 3px 10px rgba(0,0,0,.08); }
    .fbp-edit-camera svg { width:25px; height:25px; }
    .fbp-edit-form { display:flex; flex-direction:column; gap:19px; }
    .fbp-edit-field { min-width:0; }
    .fbp-edit-field label { display:block; margin:0 0 9px; color:#242226; font-size:17px; font-weight:650; }
    .fbp-edit-required { color:#b4233c; }
    .fbp-edit-field input,.fbp-edit-field select { display:block; width:100%; min-height:62px; padding:0 18px; border:1px solid #dedee4; border-radius:13px; outline:none; background:#fff; color:#252326; font:inherit; font-size:19px; }
    .fbp-edit-field input:focus,.fbp-edit-field select:focus { border-color:#a63b60; box-shadow:0 0 0 3px rgba(133,13,53,.10); }
    .fbp-edit-field input[readonly] { background:#f0f2f6; color:#555a63; }
    .fbp-edit-email { position:relative; }
    .fbp-edit-email .fbp-email-icon { position:absolute; top:50%; left:17px; width:22px; height:22px; transform:translateY(-50%); color:#737780; pointer-events:none; }
    .fbp-edit-email input { min-width:0; padding-left:52px !important; padding-inline-start:52px !important; }
    .fbp-edit-email .fbp-email-icon { z-index:1; }
    .fbp-edit-hint { display:block; margin-top:7px; color:#88868b; font-size:12px; line-height:1.5; }
    .fbp-edit-phone-row { display:flex; align-items:center; gap:0; width:100%; min-width:0; min-height:62px; overflow:visible; border:1px solid #dedee4; border-radius:13px; background:#fff; transition:border-color .18s ease, box-shadow .18s ease; }
    .fbp-edit-phone-row:focus-within { border-color:#a63b60; box-shadow:0 0 0 3px rgba(133,13,53,.10); }
    .fbp-edit-phone-row input { flex:1 1 auto; width:1%; min-width:0; min-height:60px; align-self:stretch; padding:0 14px 0 4px !important; border:0 !important; border-radius:0 !important; outline:0 !important; background:transparent !important; box-shadow:none !important; letter-spacing:.02em; font-family:inherit; font-size:19px; font-weight:400; line-height:1.2; font-variant-numeric:tabular-nums; }
    .fbp-edit-select-wrap { position:relative; }
    .fbp-edit-select-wrap:after { content:none !important; display:none !important; }
    .fbp-edit-select-wrap select { appearance:none; -webkit-appearance:none; padding-right:34px !important; cursor:pointer; }
    .fbp-edit-email input[readonly] { background:#eef1f5 !important; color:#555a63 !important; border-color:#e1e5eb !important; cursor:default; user-select:text; }
    .fbp-edit-email input[readonly]:focus { border-color:#e1e5eb !important; box-shadow:none !important; outline:none !important; }
    .fbp-country-picker { position:relative; display:flex; align-items:stretch; flex:0 0 auto; min-width:0; }
    .fbp-country-trigger { display:inline-flex; align-items:center; justify-content:center; width:auto; min-width:64px; min-height:60px; height:100%; align-self:stretch; padding:0 12px 0 16px; border:0; border-radius:12px 0 0 12px; background:transparent; color:#85838a; font-family:inherit; font-size:19px; font-weight:400; line-height:1.2; cursor:pointer; white-space:nowrap; font-variant-numeric:tabular-nums; }
    .fbp-country-trigger:focus-visible { outline:2px solid rgba(133,13,53,.22); outline-offset:-3px; }
    .fbp-country-trigger:hover { color:#850d35; background:#fffafb; }
    .fbp-country-trigger .fbp-country-code { font-variant-numeric:tabular-nums; }
    .fbp-country-menu { position:absolute; z-index:30; top:calc(100% + 7px); left:0; width:min(280px, calc(100vw - 44px)); max-height:260px; overflow:auto; padding:6px; border:1px solid #e6e0e3; border-radius:13px; background:#fff; box-shadow:0 12px 30px rgba(35,18,25,.16); }
    .fbp-country-menu[hidden] { display:none !important; }
    .fbp-country-option { display:flex; align-items:center; gap:10px; width:100%; padding:10px 11px; border:0; border-radius:8px; background:transparent; color:#252326; text-align:left; font:inherit; font-size:14px; cursor:pointer; }
    .fbp-country-option:hover,.fbp-country-option[aria-selected="true"] { background:#f8edf1; color:#850d35; }
    .fbp-country-option-code { min-width:42px; font-weight:700; font-variant-numeric:tabular-nums; }
    .fbp-country-option-name { overflow:hidden; text-overflow:ellipsis; }
    .fbp-edit-error { margin:7px 0 0; color:#b42318; font-size:13px; }
    .fbp-edit-photo-tools { display:flex; align-items:center; justify-content:center; gap:12px; margin-top:-25px; margin-bottom:26px; }
    .fbp-edit-photo-tools button { border:0; padding:5px 8px; background:transparent; color:var(--fbp-wine); font-size:13px; font-weight:650; cursor:pointer; }
    .fbp-edit-actions { display:flex; gap:13px; margin-top:9px; }
    .fbp-edit-actions a,.fbp-edit-actions button { display:flex; flex:1; align-items:center; justify-content:center; min-height:58px; border:1px solid #e1d6db; border-radius:12px; background:#fff; color:#4b303a; font-size:17px; font-weight:750; text-decoration:none; cursor:pointer; transition:transform .18s ease, box-shadow .18s ease, filter .18s ease; }
    .fbp-edit-actions a:hover,.fbp-edit-actions button:hover { transform:translateY(-1px); box-shadow:0 7px 18px rgba(75,24,43,.09); }
    .fbp-edit-actions button { border-color:var(--fbp-wine); background:linear-gradient(110deg,var(--fbp-wine-dark),#a1164b); color:#fff; }
    @media(max-width:600px) {
        .fbp-edit-topbar { min-height:78px; padding:13px 20px; gap:15px; }
        .fbp-edit-back { width:48px; height:48px; flex-basis:48px; }
        .fbp-edit-topbar h1 { font-size:21px; }
        .fbp-edit-body { padding:26px 22px 35px; }
        .fbp-edit-photo-area { margin:19px auto 36px; }
        .fbp-edit-photo-control { width:138px; height:138px; }
        .fbp-edit-camera { width:49px; height:49px; }
        .fbp-edit-form { gap:17px; }
        .fbp-edit-field label { font-size:16px; }
        .fbp-edit-field input,.fbp-edit-field select { min-height:57px; font-size:17px; }
        .fbp-edit-phone-row { min-height:57px; }
        .fbp-edit-phone-row input { min-height:55px; padding-right:12px !important; font-size:17px; }
        .fbp-country-trigger { min-height:55px; min-width:60px; padding-inline:13px 9px; font-size:17px; line-height:1.2; }
        .fbp-edit-actions { gap:10px; }
        .fbp-edit-actions a,.fbp-edit-actions button { min-height:54px; font-size:15px; }
    }
    @media(max-width:360px) {
        .fbp-edit-body { padding-inline:17px; }
        .fbp-edit-actions { flex-direction:column-reverse; }
    }
    @media(prefers-reduced-motion:reduce) { .fbp-edit-screen * { transition:none !important; } }
@media(min-width:801px) {
 .fbp-edit-screen { width:100%; max-width:1000px; border:1px solid #e9e5e7; border-radius:20px; overflow:hidden; }
 .fbp-edit-topbar { min-height:64px; padding:12px 26px; gap:14px; }
 .fbp-edit-back { width:40px; height:40px; flex-basis:40px; }
 .fbp-edit-screen { max-width:1040px; box-shadow:0 12px 34px rgba(48,20,31,.045); }
 .fbp-edit-body { padding:24px 30px 27px; }
 .fbp-edit-form { display:grid; grid-template-columns:minmax(190px, .72fr) repeat(2,minmax(0,1fr)); column-gap:22px; row-gap:15px; align-items:start; }
 .fbp-edit-form > div:first-of-type { grid-row:1 / 5; min-width:0; padding:14px 20px 18px 8px; border-right:1px solid #eee7ea; }
 .fbp-edit-photo-area { margin:4px auto 22px; }
 .fbp-edit-photo-control { width:124px; height:124px; }
 .fbp-edit-photo-image { font-size:42px; }
 .fbp-edit-camera { width:40px; height:40px; }
 .fbp-edit-photo-tools { flex-wrap:wrap; margin-top:-20px; margin-bottom:20px; }
 .fbp-edit-field label { font-size:14px; }
 .fbp-edit-field input,.fbp-edit-field select { min-height:48px; padding-inline:15px; border-radius:11px; font-size:15px; }
 .fbp-edit-phone-row { min-height:48px; }
 .fbp-edit-phone-row input { min-height:46px; padding-right:12px !important; font-size:15px; }
 .fbp-country-trigger { min-height:46px; min-width:56px; padding-inline:13px 8px; font-size:15px; line-height:1.2; }
 .fbp-edit-email input { padding-left:46px !important; padding-inline-start:46px !important; }
 .fbp-edit-email .fbp-email-icon { left:14px; width:19px; height:19px; }
 .fbp-edit-actions { grid-column:2 / 4; justify-content:flex-end; margin-top:0; gap:10px; }
 .fbp-edit-actions a,.fbp-edit-actions button { min-height:46px; font-size:14px; }
 .fbp-edit-actions button { color:#fff !important; }
}
@media(min-width:801px) and (max-width:1050px) {
 .fbp-edit-body { padding:22px 22px 25px; }
 .fbp-edit-form { grid-template-columns:minmax(165px, .68fr) minmax(0, 1.5fr); column-gap:17px; row-gap:14px; }
 .fbp-edit-form > div:first-of-type { grid-row:1 / 6; padding-inline:8px 16px; }
 .fbp-edit-photo-control { width:108px; height:108px; }
 .fbp-edit-actions { grid-column:2; }
}
</style>

<div class="fbp-edit-screen">
    <header class="fbp-edit-topbar">
        <a class="fbp-edit-back" href="{{ route('user.profile.index') }}" aria-label="Kembali ke Profil">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <h1>Edit Profil</h1>
    </header>

    <div class="fbp-edit-body">
        <form class="fbp-edit-form" method="POST" action="{{ route('user.profile.update') }}" enctype="multipart/form-data">
            @csrf
            <div>
                <div class="fbp-edit-photo-area">
                    <button class="fbp-edit-photo-control" type="button" id="fbp-photo-trigger" aria-label="Ubah foto profil">
                        <span class="fbp-edit-photo-image">
                            <img id="fbp-photo-preview" src="{{ $photoUrl ?: '' }}" alt="Pratinjau foto profil" @unless($photoUrl) hidden @endunless>
                            <span id="fbp-photo-initial" @if($photoUrl) hidden @endif>{{ $initial }}</span>
                        </span>
                        <span class="fbp-edit-camera" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h3l1.5-2h7L17 7h3v12H4z"/><circle cx="12" cy="13" r="4"/></svg>
                        </span>
                    </button>
                </div>
                <input id="fbp-photo-input" name="photo" type="file" accept="image/jpeg,image/png,image/webp" hidden>
                <input id="fbp-remove-photo" name="remove_photo" type="hidden" value="0">
                <div class="fbp-edit-photo-tools">
                    <button type="button" id="fbp-choose-photo">Pilih foto</button>
                    <button type="button" id="fbp-remove-photo-button" @unless($photoUrl) hidden @endunless>Hapus foto</button>
                </div>
                <small class="fbp-edit-hint" style="text-align:center">JPG, PNG, atau WebP · Maksimal 2 MB</small>
                @error('photo') <p class="fbp-edit-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="fbp-edit-field">
                <label for="fbp-name">Nama Lengkap<span class="fbp-edit-required">*</span></label>
                <input id="fbp-name" name="name" type="text" value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name" required>
                @error('name') <p class="fbp-edit-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="fbp-edit-field">
                <label for="fbp-email">Email<span class="fbp-edit-required">*</span></label>
                <div class="fbp-edit-email">
                    <svg class="fbp-email-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    <input id="fbp-email" type="email" value="{{ $user->email }}" readonly aria-readonly="true" tabindex="-1">
                </div>
            </div>

            <div class="fbp-edit-field">
                <label for="fbp-gender">Jenis Kelamin</label>
                <div class="fbp-edit-select-wrap">
                    <select id="fbp-gender" name="gender" aria-label="Pilih jenis kelamin">
                        <option value="" disabled @selected(old('gender', $user->gender) === null || old('gender', $user->gender) === '')>Pilih jenis kelamin</option>
                        <option value="male" @selected(old('gender', $user->gender) === 'male')>Laki-laki</option>
                        <option value="female" @selected(old('gender', $user->gender) === 'female')>Perempuan</option>
                    </select>
                </div>
                @error('gender') <p class="fbp-edit-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="fbp-edit-field">
                <label for="fbp-phone">Nomor Handphone</label>
                <div class="fbp-edit-phone-row">
                    <div class="fbp-country-picker" id="fbp-country-picker" aria-expanded="false">
                        <input type="hidden" id="fbp-phone-code" name="phone_country_code" value="{{ $selectedPhoneCode }}">
                        <button class="fbp-country-trigger" type="button" id="fbp-country-trigger" aria-label="Pilih kode negara, saat ini {{ $selectedPhoneCode }}" aria-haspopup="listbox" aria-expanded="false" aria-controls="fbp-country-menu">
                            <span class="fbp-country-code" id="fbp-country-code-label">{{ $selectedPhoneCode }}</span>
                        </button>
                        <div class="fbp-country-menu" id="fbp-country-menu" role="listbox" aria-label="Pilih kode negara" hidden>
                            @foreach($phoneCountries as [$code, $country])
                                <button class="fbp-country-option" type="button" role="option" data-code="{{ $code }}" data-country="{{ $country }}" aria-selected="{{ $selectedPhoneCode === $code ? 'true' : 'false' }}">
                                    <span class="fbp-country-option-code">{{ $code }}</span>
                                    <span class="fbp-country-option-name">{{ $country }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <input id="fbp-phone" name="phone_number" type="tel" inputmode="tel" autocomplete="tel-national" maxlength="20" value="{{ $phoneNational }}" placeholder="858 3277 9146" aria-label="Nomor handphone">
                </div>
                @error('phone_number') <p class="fbp-edit-error" role="alert">{{ $message }}</p> @enderror
                @error('phone_country_code') <p class="fbp-edit-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="fbp-edit-actions">
                <a href="{{ route('user.profile.index') }}">Batal</a>
                <button type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const input = document.getElementById('fbp-photo-input');
    const trigger = document.getElementById('fbp-photo-trigger');
    const choose = document.getElementById('fbp-choose-photo');
    const remove = document.getElementById('fbp-remove-photo-button');
    const removeValue = document.getElementById('fbp-remove-photo');
    const preview = document.getElementById('fbp-photo-preview');
    const initial = document.getElementById('fbp-photo-initial');
    const name = document.getElementById('fbp-name');
    if (!input || !trigger || !preview || !initial || !name) return;

    let localUrl = null;

    const showInitial = () => {
        preview.hidden = true;
        initial.hidden = false;
    };

    preview.addEventListener('error', () => {
        showInitial();
    });

    if (preview.getAttribute('src') && preview.complete && preview.naturalWidth === 0) {
        showInitial();
    }
    const openPicker = () => input.click();
    trigger.addEventListener('click', openPicker);
    choose.addEventListener('click', openPicker);

    function updateInitial() {
        const value = name.value.trim();
        initial.textContent = value ? Array.from(value)[0].toLocaleUpperCase('id-ID') : 'U';
    }
    name.addEventListener('input', updateInitial);

    const phone = document.getElementById('fbp-phone');
    const phoneCode = document.getElementById('fbp-phone-code');
    const countryPicker = document.getElementById('fbp-country-picker');
    const countryTrigger = document.getElementById('fbp-country-trigger');
    const countryMenu = document.getElementById('fbp-country-menu');
    const countryCodeLabel = document.getElementById('fbp-country-code-label');
    const countryNameLabel = document.getElementById('fbp-country-name-label');
    const form = trigger.closest('form');

    // Format tampilan nomor dengan spasi; nilai dikirim ke server tanpa spasi.
    const formatPhone = (value, code = phoneCode?.value || '+62') => {
        let digits = String(value || '').replace(/\D/g, '').slice(0, 15);
        if (code === '+62' && digits.startsWith('0')) digits = digits.slice(1);
        const groups = [];
        if (code === '+62') {
            if (digits.length) groups.push(digits.slice(0, 3));
            if (digits.length > 3) groups.push(digits.slice(3, 7));
            if (digits.length > 7) groups.push(digits.slice(7, 11));
            if (digits.length > 11) groups.push(digits.slice(11));
        } else {
            for (let i = 0; i < digits.length; i += 3) groups.push(digits.slice(i, i + 3));
        }
        return groups.filter(Boolean).join(' ');
    };

    const updatePhoneFormat = () => {
        if (!phone) return;
        const cursor = phone.selectionStart ?? phone.value.length;
        const digitsBeforeCursor = phone.value.slice(0, cursor).replace(/\D/g, '').length;
        phone.value = formatPhone(phone.value);
        let nextCursor = phone.value.length;
        if (digitsBeforeCursor < phone.value.replace(/\D/g, '').length) {
            let seen = 0;
            nextCursor = 0;
            while (nextCursor < phone.value.length && seen < digitsBeforeCursor) {
                if (/\d/.test(phone.value[nextCursor])) seen++;
                nextCursor++;
            }
        }
        try { phone.setSelectionRange(nextCursor, nextCursor); } catch (_) {}
    };

    const closeCountryMenu = () => {
        if (!countryPicker || !countryMenu || !countryTrigger) return;
        countryPicker.setAttribute('aria-expanded', 'false');
        countryTrigger.setAttribute('aria-expanded', 'false');
        countryMenu.hidden = true;
    };
    countryTrigger?.addEventListener('click', () => {
        const open = countryMenu && countryMenu.hidden;
        if (!countryMenu || !countryPicker) return;
        countryMenu.hidden = !open;
        countryPicker.setAttribute('aria-expanded', open ? 'true' : 'false');
        countryTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    countryMenu?.querySelectorAll('[data-code]').forEach(option => {
        option.addEventListener('click', () => {
            const code = option.dataset.code || '+62';
            phoneCode.value = code;
            countryCodeLabel.textContent = code;
            if (countryNameLabel) countryNameLabel.textContent = option.dataset.country || 'Negara';
            countryMenu.querySelectorAll('[data-code]').forEach(item => item.setAttribute('aria-selected', item === option ? 'true' : 'false'));
            if (phone) phone.value = formatPhone(phone.value, code);
            closeCountryMenu();
            phone?.focus();
        });
    });
    document.addEventListener('click', event => {
        if (countryPicker && !countryPicker.contains(event.target)) closeCountryMenu();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeCountryMenu();
    });
    phone?.addEventListener('input', updatePhoneFormat);
    if (phone && phone.value) phone.value = formatPhone(phone.value);
    form?.addEventListener('submit', () => {
        if (phone) {
            let digits = phone.value.replace(/\D/g, '');
            if (phoneCode?.value === '+62' && digits.startsWith('0')) digits = digits.slice(1);
            phone.value = digits;
        }
    });

    input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran foto maksimal 2 MB.');
            input.value = '';
            return;
        }
        if (localUrl) URL.revokeObjectURL(localUrl);
        localUrl = URL.createObjectURL(file);
        preview.src = localUrl;
        preview.hidden = false;
        initial.hidden = true;
        removeValue.value = '0';
        remove.hidden = false;
    });

    remove?.addEventListener('click', () => {
        input.value = '';
        if (localUrl) URL.revokeObjectURL(localUrl);
        localUrl = null;
        preview.removeAttribute('src');
        preview.hidden = true;
        initial.hidden = false;
        removeValue.value = '1';
        remove.hidden = true;
        updateInitial();
    });
})();
</script>
@endsection
