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
    .fbp-edit-photo-control { position:relative; width:150px; height:150px; padding:0; border:0; border-radius:50%; background:#f5e6ec; cursor:pointer; }
    .fbp-edit-photo-image { width:100%; height:100%; display:grid; place-items:center; overflow:hidden; border:1px solid #e5d5dc; border-radius:50%; color:var(--fbp-wine); font-size:48px; font-weight:800; }
    .fbp-edit-photo-image img { width:100%; height:100%; object-fit:cover; }
    .fbp-edit-camera { position:absolute; right:-2px; bottom:-2px; width:54px; height:54px; display:grid; place-items:center; border:1px solid #dedadd; border-radius:50%; background:#fff; color:#262326; box-shadow:0 3px 10px rgba(0,0,0,.08); }
    .fbp-edit-camera svg { width:25px; height:25px; }
    .fbp-edit-form { display:flex; flex-direction:column; gap:25px; }
    .fbp-edit-field { min-width:0; }
    .fbp-edit-field label { display:block; margin:0 0 9px; color:#242226; font-size:17px; font-weight:650; }
    .fbp-edit-required { color:#b4233c; }
    .fbp-edit-field input,.fbp-edit-field select { display:block; width:100%; min-height:62px; padding:0 18px; border:1px solid #dedee4; border-radius:13px; outline:none; background:#fff; color:#252326; font:inherit; font-size:19px; }
    .fbp-edit-field input:focus,.fbp-edit-field select:focus { border-color:#a63b60; box-shadow:0 0 0 3px rgba(133,13,53,.10); }
    .fbp-edit-field input[readonly] { background:#f0f2f6; color:#555a63; }
    .fbp-edit-email { position:relative; }
    .fbp-edit-email .fbp-email-icon { position:absolute; top:50%; left:17px; width:22px; height:22px; transform:translateY(-50%); color:#737780; pointer-events:none; }
    .fbp-edit-email input { padding-left:52px; }
    .fbp-edit-hint { display:block; margin-top:7px; color:#88868b; font-size:12px; line-height:1.5; }
    .fbp-edit-error { margin:7px 0 0; color:#b42318; font-size:13px; }
    .fbp-edit-photo-tools { display:flex; align-items:center; justify-content:center; gap:12px; margin-top:-25px; margin-bottom:26px; }
    .fbp-edit-photo-tools button { border:0; padding:5px 8px; background:transparent; color:var(--fbp-wine); font-size:13px; font-weight:650; cursor:pointer; }
    .fbp-edit-actions { display:flex; gap:13px; margin-top:9px; }
    .fbp-edit-actions a,.fbp-edit-actions button { display:flex; flex:1; align-items:center; justify-content:center; min-height:58px; border:1px solid #e1d6db; border-radius:12px; background:#fff; color:#4b303a; font-size:17px; font-weight:750; text-decoration:none; cursor:pointer; }
    .fbp-edit-actions button { border-color:var(--fbp-wine); background:linear-gradient(110deg,var(--fbp-wine-dark),#a1164b); color:#fff; }
    @media(max-width:600px) {
        .fbp-edit-topbar { min-height:78px; padding:13px 20px; gap:15px; }
        .fbp-edit-back { width:48px; height:48px; flex-basis:48px; }
        .fbp-edit-topbar h1 { font-size:21px; }
        .fbp-edit-body { padding:26px 22px 35px; }
        .fbp-edit-photo-area { margin:19px auto 36px; }
        .fbp-edit-photo-control { width:138px; height:138px; }
        .fbp-edit-camera { width:49px; height:49px; }
        .fbp-edit-form { gap:22px; }
        .fbp-edit-field label { font-size:16px; }
        .fbp-edit-field input,.fbp-edit-field select { min-height:57px; font-size:17px; }
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
 .fbp-edit-body { padding:28px 30px 32px; }
 .fbp-edit-form { display:grid; grid-template-columns: minmax(200px, .8fr) repeat(2,minmax(0,1fr)); gap:20px; align-items:start; }
 .fbp-edit-form > div:first-of-type { grid-row:1 / 4; padding:12px 18px; border-right:1px solid #eee7ea; }
 .fbp-edit-photo-control { width:112px; height:112px; }
 .fbp-edit-camera { width:38px; height:38px; }
 .fbp-edit-photo-tools { flex-wrap:wrap; }
 .fbp-edit-field label { font-size:14px; }
 .fbp-edit-field input,.fbp-edit-field select { min-height:46px; font-size:15px; }
 .fbp-edit-actions { grid-column:2 / 4; justify-content:flex-end; margin-top:0; }
 .fbp-edit-actions a,.fbp-edit-actions button { min-height:44px; font-size:14px; }
 .fbp-edit-actions button { color:#fff !important; }
}
@media(min-width:801px) and (max-width:1050px) {
 .fbp-edit-form { grid-template-columns:minmax(180px, .8fr) minmax(0, 1.5fr); }
 .fbp-edit-form > div:first-of-type { grid-row:1 / 6; }
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
                    <input id="fbp-email" type="email" value="{{ $user->email }}" readonly aria-readonly="true">
                </div>
                <small class="fbp-edit-hint">Email akun digunakan untuk masuk dan tidak dapat diubah.</small>
            </div>

            <div class="fbp-edit-field">
                <label for="fbp-gender">Jenis Kelamin</label>
                <select id="fbp-gender" name="gender">
                    <option value="">Pilih jenis kelamin</option>
                    <option value="male" @selected(old('gender', $user->gender) === 'male')>Laki-laki</option>
                    <option value="female" @selected(old('gender', $user->gender) === 'female')>Perempuan</option>
                </select>
                @error('gender') <p class="fbp-edit-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="fbp-edit-field">
                <label for="fbp-phone">Nomor Handphone</label>
                <input id="fbp-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" maxlength="20" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 08xxxxxxxxxx">
                @error('phone') <p class="fbp-edit-error" role="alert">{{ $message }}</p> @enderror
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

    const savedPhoto = preview.getAttribute('src');
    let localUrl = null;
    const openPicker = () => input.click();
    trigger.addEventListener('click', openPicker);
    choose.addEventListener('click', openPicker);

    function updateInitial() {
        const value = name.value.trim();
        initial.textContent = value ? Array.from(value)[0].toLocaleUpperCase('id-ID') : 'U';
    }
    name.addEventListener('input', updateInitial);

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
