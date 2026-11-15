@extends('user.layouts.campus')

@section('title', 'Profil saya')

@section('content')
    @php
        $user = auth()->user();
        
        $photoPath = $user->profile_photo_path;

$photoUrl = $photoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath)
    ? route('user.profile.photo', ['v' => basename($photoPath)])
    : null;

        $initial = mb_strtoupper(mb_substr(trim($user->name), 0, 1));
        $profileError = $errors->has('name') || $errors->has('photo')
            || $errors->has('remove_photo');
        $passwordError = $errors->has('current_password') || $errors->has('password');
    @endphp

    <style>
        .fbp {
            max-width: 900px;
            margin: 0 auto 48px;
        }

        .fbp [hidden] {
            display: none !important;
        }

        .fbp .fp-title {
            margin: 0 0 20px;
        }

        .fbp .fp-hero {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: clamp(24px, 5vw, 42px);
            border-radius: 20px;
            background: linear-gradient(110deg, var(--dark), var(--wine));
            color: white;
        }

        .fbp .fp-avatar {
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            width: 80px;
            height: 80px;
            overflow: hidden;
            border: 3px solid #ffffffa8;
            border-radius: 50%;
            background: #fae7eb;
            color: var(--wine);
            font-size: 32px;
            font-weight: 700;
        }

        .fbp .fp-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .fbp .fp-identity {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .fbp .fp-identity h2 {
            margin: 0 0 4px;
            color: white;
            font-size: clamp(22px, 4vw, 32px);
        }

        .fbp .fp-identity p {
            margin: 0;
            color: #f7e4e9;
        }

        .fbp .fp-card {
            margin-top: 16px;
            padding: clamp(20px, 4vw, 30px);
            border: 1px solid var(--line);
            border-radius: 16px;
            background: white;
        }

        .fbp .fp-row,
        .fbp .fp-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .fbp .fp-row {
            justify-content: space-between;
        }

        .fbp .fp-card h2 {
            margin: 0;
            font-size: 20px;
        }

        .fbp .fp-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin: 22px 0 0;
        }

        .fbp .fp-grid dt,
        .fbp .fp-hint {
            color: #62665f;
            font-size: 13px;
        }

        .fbp .fp-grid dd {
            margin: 4px 0 0;
            overflow-wrap: anywhere;
            font-weight: 600;
        }

        .fbp .fp-field {
            margin-top: 18px;
        }

        .fbp .fp-hint {
            display: block;
            margin: 6px 0 0;
        }

        .fbp .fp-file {
            display: block;
            max-width: 100%;
            margin-top: 8px;
        }

        .fbp .fp-actions {
            justify-content: flex-end;
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1px solid var(--line);
        }

        .fbp .fp-btn,
        .fbp .fp-link {
            min-height: 42px;
            padding: 8px 16px;
            border: 1px solid #d7d9d3;
            border-radius: 9px;
            background: white;
            color: var(--ink);
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        .fbp .fp-primary {
            border-color: var(--wine);
            background: var(--wine);
            color: white;
        }

        .fbp .fp-primary:disabled {
            border-color: #deded9;
            background: #deded9;
            color: #575954;
            cursor: default;
        }

        .fbp .fp-link {
            display: inline-block;
            margin-top: 12px;
            color: var(--wine);
        }

        .fbp .fp-status {
            margin: 12px 0 0;
            color: var(--wine);
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .fbp .fp-grid {
                grid-template-columns: 1fr;
            }

            .fbp .fp-hero {
                gap: 14px;
            }

            .fbp .fp-avatar {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
        }
    /* Informasi pribadi */
.fbp .fp-grid dt,
.fbp .fp-hint,
.fbp label {
    font-size: 14px;
}

.fbp .fp-grid dd {
    font-size: 16px;
}

/* Foto dan tombol pilih ditempatkan berdekatan */
.fbp .fp-photo-editor {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    padding: 16px;
    border: 1px solid var(--line);
    border-radius: 12px;
    background: #faf7f8;
}

.fbp .fp-photo-editor .fp-thumb {
    width: 64px;
    height: 64px;
    border: 2px solid white;
    font-size: 24px;
}

.fbp .fp-photo-controls {
    position: relative;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin: 8px 0;
}

/* Input file tetap dapat digunakan dengan keyboard */
.fbp .fp-photo-controls input.fp-picker {
    position: absolute;
    width: 1px;
    height: 1px;
    min-height: 0;
    padding: 0;
    margin: 0;
    border: 0;
    overflow: hidden;
    clip-path: inset(50%);
}

.fbp .fp-picker:focus-visible + label {
    outline: 3px solid var(--wine);
    outline-offset: 3px;
}

.fbp .fp-photo-controls label.fp-btn {
    display: inline-flex;
    align-items: center;
    margin: 0;
    color: var(--wine);
    cursor: pointer;
}

.fbp .fp-btn,
.fbp .fp-link {
    min-height: 44px;
}

/* Teks tombol mengikuti keadaan formulir kata sandi */
.fbp details .fp-when-open,
.fbp details[open] .fp-when-closed {
    display: none;
}

.fbp details[open] .fp-when-open {
    display: inline;
}
    .fbp .fp-toast {
    position: fixed;
    top: max(16px, env(safe-area-inset-top));
    right: 16px;
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 12px;
    width: max-content;
    max-width: min(380px, calc(100% - 32px));
    padding: 14px 18px;
    border: 1px solid #d6e5da;
    border-radius: 16px;
    background: #fff;
    color: #203b29;
    box-shadow: 0 8px 28px rgb(28 47 34 / 16%);
    font-size: 15px;
    font-weight: 600;
    pointer-events: none;
    animation: fp-toast-in 220ms ease-out;
    transition: opacity 220ms ease, transform 220ms ease;
}

.fbp .fp-toast-check {
    display: grid;
    place-items: center;
    flex: 0 0 30px;
    height: 30px;
    border-radius: 50%;
    background: #e4f3e8;
    color: #246039;
}

.fbp .fp-toast.is-leaving {
    opacity: 0;
    transform: translateY(-8px);
}

@keyframes fp-toast-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 600px) {
    .fbp .fp-toast {
        left: 12px;
        right: 12px;
        width: auto;
        max-width: none;
    }
}

@media (prefers-reduced-motion: reduce) {
    .fbp .fp-toast {
        animation: none;
        transition: none;
    }
}
    #fp-edit-dialog, #fp-password-dialog {
    box-sizing: border-box;
    width: min(520px, calc(100% - 32px));
    max-height: 85dvh;
    margin: auto;
    padding: clamp(20px, 4vw, 28px);
    overflow-y: auto;
    overscroll-behavior: contain;
    border: 1px solid #e7e1e3;
    border-radius: 18px;
    background: white;
    color: var(--ink);
    box-shadow: 0 20px 64px rgb(40 10 20 / 22%);
}

#fp-edit-dialog::backdrop, #fp-password-dialog::backdrop {
    background: rgb(28 16 21 / 48%);
}

body:has(#fp-edit-dialog[open], #fp-password-dialog[open]) {
    overflow: hidden;
}

.fp-dialog-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.fp-dialog-header h2 {
    margin: 0;
}

.fp-dialog-close {
    display: grid;
    place-items: center;
    width: 44px;
    height: 44px;
    padding: 0;
    border: 0;
    border-radius: 50%;
    background: #f6f1f3;
    color: var(--wine);
    font-size: 24px;
    cursor: pointer;
}
    #fp-edit,
#fp-password-open,
#fp-remove-button,
#fp-save,
label[for="fp-photo"].fp-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
}

.fbp .fp-btn .cu-icon {
    display: block;
    width: 17px;
    height: 17px;
    flex: 0 0 17px;
}

#fp-remove-button[hidden] {
    display: none;
}
    /* Judul dan tombol tutup di kedua dialog profil */
.fbp .fp-dialog-header {
    align-items: center;
    gap: 16px;
}

.fbp .fp-dialog-header h2 {
    margin: 0;
    font-size: 22px;
    line-height: 1.25;
}

.fbp .fp-dialog-header .fp-dialog-close {
    display: grid;
    place-items: center;
    flex: 0 0 44px;
    width: 44px;
    height: 44px;
    padding: 0;
    border: 0;
    border-radius: 50%;
    background: #f7f1f3;
    color: var(--wine);
    font-family: Arial, sans-serif;
    font-size: 28px;
    font-weight: 400;
    line-height: 1;
}

/* Foto dan aksi tetap berdekatan saat ukuran layar berubah */
#fp-edit-dialog .fp-photo-editor {
    padding: 16px;
    gap: 10px 14px;
    border: 1px solid #e9dfe2;
    border-radius: 14px;
    background: #fbf8f9;
}

#fp-edit-dialog .fp-photo-controls {
    column-gap: 8px;
    row-gap: 6px;
}

#fp-edit-dialog #fp-photo-hint {
    color: #645d60;
    font-size: 13px;
    line-height: 1.45;
}

/* Tombol mudah dikenali saat memakai keyboard */
.fbp dialog :is(button, input):focus-visible,
#fp-edit-dialog .fp-picker:focus-visible + label {
    outline: 3px solid #ad5570;
    outline-offset: 3px;
}

/* Umpan balik saat kursor berada di atas tombol */
@media (hover: hover) {
    .fbp .fp-dialog-close:hover {
        background: #eedfe4;
    }

    #fp-edit-dialog label[for="fp-photo"]:hover,
    #fp-edit-dialog #fp-remove-button:hover {
        border-color: #cda9b4;
        background: #fff;
    }
}

/* Ruang lebih lega untuk nama dan tombol pada layar HP */
@media (max-width: 480px) {
    #fp-edit-dialog,
    #fp-password-dialog {
        width: calc(100% - 24px);
        padding: 18px;
    }

    #fp-edit-dialog .fp-photo-editor {
        padding: 12px;
        column-gap: 10px;
    }

    #fp-edit-dialog .fp-photo-controls .fp-btn {
        gap: 5px;
        padding-inline: 8px;
    }
}
    #fp-edit-dialog .fp-photo-editor {
    display: grid;
    grid-template-columns: 56px minmax(0, 1fr);
    grid-template-areas:
        "title title"
        "avatar actions"
        "hint hint";
    align-items: center;
    gap: 10px 14px;
    padding: 16px;
    border: 1px solid #e8dfe2;
    border-radius: 14px;
    background: #fbf8f9;
}

#fp-edit-dialog .fp-photo-title {
    grid-area: title;
    font-size: 14px;
}

#fp-edit-dialog .fp-thumb {
    grid-area: avatar;
    width: 56px;
    height: 56px;
}

#fp-edit-dialog .fp-photo-controls {
    grid-area: actions;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    min-width: 0;
    margin: 0;
}

#fp-edit-dialog #fp-photo-hint {
    grid-area: hint;
    margin: 0;
    color: #665e61;
    line-height: 1.45;
}

#fp-edit-dialog label[for="fp-photo"] {
    border-color: #d8b3bf;
    background: #fff;
    color: var(--wine);
}

#fp-edit-dialog #fp-save:enabled {
    background: var(--wine);
    border-color: var(--wine);
    color: white;
}

.fbp .fp-dialog-header .fp-dialog-close {
    font-size: 28px;
    line-height: 1;
}

@media (max-width: 480px) {
    #fp-edit-dialog .fp-photo-editor {
        grid-template-columns: 44px minmax(0, 1fr);
        gap: 8px 10px;
        padding: 12px;
    }

    #fp-edit-dialog .fp-thumb {
        width: 44px;
        height: 44px;
    }

    #fp-edit-dialog .fp-photo-controls .fp-btn {
        min-height: 44px;
        padding: 6px 8px;
        gap: 5px;
    }
}
   
/* Tombol dan dialog profil responsif */
@media (max-width: 600px) {
    .fbp dialog {
        box-sizing: border-box;
        width: calc(100% - 24px);
        max-width: 100%;
        max-height: calc(100dvh - 32px);
        overflow-y: auto;
    }

    .fbp dialog .fp-actions {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            minmax(0, 1.5fr);
        gap: 10px;
    }

    .fbp dialog .fp-actions .fp-btn {
        width: 100%;
        min-width: 0;
        min-height: 44px;
        padding: 10px 8px;
        white-space: normal;
        text-align: center;
        justify-content: center;
        line-height: 1.3;
    }
}

@media (max-width: 360px) {
    .fbp dialog .fp-actions {
        grid-template-columns: 1fr;
    }
}



#fp-confirm-note:empty {
    display: none;
}

/* Pesan validasi profil */
.fbp .fp-error {
    margin: 6px 0 0;
    color: #b42318;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.5;
}

.fbp input[aria-invalid="true"] {
    border-color: #b42318;
    background-color: #fff8f7;
}

.fbp input:focus-visible,
.fbp button:focus-visible {
    outline: 3px solid rgba(137, 36, 66, 0.25);
    outline-offset: 2px;
}

    
/* Interaksi tombol yang lebih halus */
.fbp .fp-btn {
    transition:
        background-color 0.2s ease,
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.fbp .fp-btn:enabled:active {
    transform: translateY(1px);
}

@media (prefers-reduced-motion: reduce) {
    .fbp .fp-btn {
        transition: none;
    }
}

    </style>

    <div class="fbp" id="fb-profile">
        @if (session('profile_toast'))
    <div id="fp-toast" class="fp-toast" role="status" aria-atomic="true">
        <span class="fp-toast-check" aria-hidden="true">✓</span>
        <span>{{ session('profile_toast') }}</span>
    </div>
@endif
        <h1 class="fp-title">Profil saya</h1>

        <section class="fp-hero" aria-label="Identitas akun">
            <div class="fp-avatar" aria-hidden="true">
                <img id="fp-avatar-image"
                     @if ($photoUrl) src="{{ $photoUrl }}" @else hidden @endif
                     alt="">
                <span id="fp-initial" @if ($photoUrl) hidden @endif>
                    {{ $initial }}
                </span>
            </div>

            <div class="fp-identity">
                <h2 id="fp-preview-name">{{ $user->name }}</h2>
                <p>{{ $user->email }}</p>
            </div>
        </section>

        <section class="fp-card" aria-labelledby="fp-info-heading">
            <div class="fp-row">
                <h2 id="fp-info-heading">Informasi pribadi</h2>
                <button type="button"
        id="fp-edit"
        class="fp-btn"
        aria-haspopup="dialog"
        aria-controls="fp-edit-dialog">
    @include('user.components.icon', ['icon' => 'edit'])
<span>Edit profil</span>
</button>
            </div>

           <dl id="fp-read" class="fp-grid">
                <div>
                    <dt>Nama lengkap</dt>
                    <dd>{{ $user->name }}</dd>
                </div>
                <div>
                    <dt>Email terdaftar</dt>
                    <dd>{{ $user->email }}</dd>
                </div>
            </dl>

            <dialog id="fp-edit-dialog" aria-labelledby="fp-dialog-title">
    <div class="fp-dialog-header">
        <h2 id="fp-dialog-title">Edit profil</h2>

        <button type="button"
                id="fp-dialog-close"
                class="fp-dialog-close"
                aria-label="Tutup formulir">
            ×
        </button>
    </div>

    <form id="fp-form"
          method="POST"
          action="{{ route('user.profile.update') }}"
          enctype="multipart/form-data">
                @csrf

                
<div class="fp-field">
    <label for="fp-name">Nama lengkap</label>

    <input
        id="fp-name"
        name="name"
        type="text"
        value="{{ old('name', $user->name) }}"
        autocomplete="name"
        maxlength="255"
        required
        @error('name')
            aria-invalid="true"
            aria-describedby="fp-name-error"
        @enderror
    >

    @error('name')
        <p id="fp-name-error"
           class="fp-error"
           role="alert">
            {{ $message }}
        </p>
    @enderror
</div>


                <div class="fp-field">
                   <div class="fp-photo-editor">
    <strong class="fp-photo-title">Foto profil</strong>

    <div class="fp-avatar fp-thumb" aria-hidden="true">
        <img id="fp-thumb-image"
             @if ($photoUrl) src="{{ $photoUrl }}" @else hidden @endif
             alt="">
        <span id="fp-thumb-initial" @if ($photoUrl) hidden @endif>
            {{ $initial }}
        </span>
    </div>

    <div class="fp-photo-controls">
        
<input id="fp-photo"
       class="fp-picker"
       name="photo"
       type="file"
       accept="image/jpeg,image/png,image/webp"
       aria-describedby="fp-photo-hint @error('photo') fp-photo-error @enderror"
       @error('photo') aria-invalid="true" @enderror>


        <label for="fp-photo" class="fp-btn">
            @include('user.components.icon', ['icon' => 'upload'])
            <span>Pilih foto</span>
        </label>

        <button type="button"
                id="fp-remove-button"
                class="fp-btn"
                @unless ($photoUrl) hidden @endunless>
            @include('user.components.icon', ['icon' => 'trash'])
            <span>Hapus foto</span>
        </button>
    </div>

    <small id="fp-photo-hint" class="fp-hint">
        JPG, PNG, atau WebP · Maksimal 2 MB
    </small>

@error('photo')
    <p id="fp-photo-error"
       class="fp-error"
       role="alert">
        {{ $message }}
    </p>
@enderror

</div>

            <input type="hidden" id="fp-remove" name="remove_photo" value="0">
        </div>

        <p id="fp-status" class="fp-status" role="status"></p>

        <div class="fp-actions">
            <button type="button" id="fp-cancel" class="fp-btn">
                Batal
            </button>

            <button type="submit" id="fp-save" class="fp-btn fp-primary">
                @include('user.components.icon', ['icon' => 'check'])
                <span>Simpan perubahan</span>
            </button>
        </div>
    </form>
</dialog>
        </section>

        <section class="fp-card" aria-labelledby="fp-security-heading">
    <div class="fp-row">
    <h2 id="fp-security-heading">Keamanan akun</h2>

    <button type="button"
            id="fp-password-open"
            class="fp-btn"
            aria-haspopup="dialog"
            aria-controls="fp-password-dialog">
        @include('user.components.icon', ['icon' => 'lock'])
<span>Ubah kata sandi</span>
    </button>
</div>

<dialog id="fp-password-dialog" aria-labelledby="fp-password-title">
    <div class="fp-dialog-header">
        <h2 id="fp-password-title">Ubah kata sandi</h2>

        <button type="button"
                id="fp-password-close"
                class="fp-dialog-close"
                aria-label="Tutup formulir kata sandi">
            ×
        </button>
    </div>

    <form id="fp-password-form"
          method="POST"
          action="{{ route('user.profile.password') }}">
        @csrf

        <div class="fp-field">
            <label for="fp-current-password">Kata sandi saat ini</label>
            <input id="fp-current-password"
                   name="current_password"
                   type="password"
                   autocomplete="current-password"
                   required
                   @error('current_password')
                       aria-invalid="true" aria-describedby="fp-current-error"
                   @enderror>

           
@error('current_password')
    <p id="fp-current-error"
       class="fp-error"
       role="alert">
        {{ $message }}
    </p>
@enderror

        </div>

        <div class="fp-field">
            <label for="fp-password">Kata sandi baru</label>
            <input id="fp-password"
                   name="password"
                   type="password"
                   autocomplete="new-password"
                   minlength="8"
                   required
                   aria-describedby="fp-password-hint @error('password') fp-new-error @enderror"
                   @error('password') aria-invalid="true" @enderror>

            <small id="fp-password-hint" class="fp-hint">Minimal 8 karakter.</small>

            
@error('password')
    <p id="fp-new-error"
       class="fp-error"
       role="alert">
        {{ $message }}
    </p>
@enderror

        </div>

        <div class="fp-field">
            <label for="fp-confirm">Konfirmasi kata sandi baru</label>
            <input id="fp-confirm"
       name="password_confirmation"
       type="password"
       autocomplete="new-password"
       minlength="8"
       aria-describedby="fp-confirm-note"
       required>


<p id="fp-confirm-note"
   class="fp-error"
   role="status"
   aria-live="polite"></p>

        </div>

        <div class="fp-actions">
            <button type="button" id="fp-password-cancel" class="fp-btn">
                Batal
            </button>

            <button type="submit" class="fp-btn fp-primary">
                Perbarui kata sandi
            </button>
        </div>
        </form>
</dialog>
</section>
</div>

    <script>
        (() => {
            const root = document.getElementById('fb-profile');
            const get = (id) => root.querySelector('#' + id);

            const form = get('fp-form');
            
            const nameInput = get('fp-name');
            const photoInput = get('fp-photo');
            const removeInput = get('fp-remove');
            const removeButton = get('fp-remove-button');
            const previewName = get('fp-preview-name');
            const avatar = get('fp-avatar-image');
            const initial = get('fp-initial');
            const status = get('fp-status');
            const saveButton = get('fp-save');

            const savedName = previewName.textContent.trim();
            const savedPhoto = avatar.getAttribute('src');
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            let localPhoto = null;

            function releasePreview() {
                if (localPhoto) {
                    URL.revokeObjectURL(localPhoto);
                    localPhoto = null;
                }
            }

            function renderPreview() {
                const name = nameInput.value.trim();
                const visibleName = name || savedName;
                const photo = localPhoto || (removeInput.value === '1' ? null : savedPhoto);

                previewName.textContent = visibleName;
                initial.textContent = Array.from(visibleName)[0]?.toLocaleUpperCase('id') || '?';

                if (photo) {
                    avatar.src = photo;
                } else {
                    avatar.removeAttribute('src');
                }

                avatar.hidden = !photo;
                initial.hidden = Boolean(photo);
                removeButton.hidden = !photo;
                const thumbImage = get('fp-thumb-image');
const thumbInitial = get('fp-thumb-initial');

if (photo) {
    thumbImage.src = photo;
} else {
    thumbImage.removeAttribute('src');
}

thumbImage.hidden = !photo;
thumbInitial.hidden = Boolean(photo);
thumbInitial.textContent = initial.textContent;

                const changed = name !== savedName || Boolean(localPhoto)
                    || removeInput.value === '1';

                saveButton.disabled = !name || !changed || !photoInput.validity.valid;
            }

           
            nameInput.addEventListener('input', renderPreview);

            photoInput.addEventListener('change', () => {
                releasePreview();
                photoInput.setCustomValidity('');
                status.textContent = '';

                const file = photoInput.files[0];

                if (file) {
                    let error = '';

                    if (!allowedTypes.includes(file.type)) {
                        error = 'Gunakan gambar JPG, PNG, atau WebP.';
                    } else if (file.size > 2 * 1024 * 1024) {
                        error = 'Ukuran foto maksimal 2 MB.';
                    }

                    if (error) {
                        // Buang file yang tidak valid agar nama tetap bisa disimpan.
                        photoInput.value = '';
                        status.textContent = error;
                    } else {
                        localPhoto = URL.createObjectURL(file);
                        removeInput.value = '0';
                        status.textContent = 'Pratinjau foto diperbarui. Simpan untuk menerapkan.';
                    }
                }

                renderPreview();
            });

            removeButton.addEventListener('click', () => {
                releasePreview();
                photoInput.value = '';
                removeInput.value = savedPhoto ? '1' : '0';
                status.textContent = savedPhoto
                    ? 'Foto akan dihapus setelah perubahan disimpan.'
                    : 'Pilihan foto dibatalkan.';
                renderPreview();
            });

            

            form.addEventListener('submit', () => {
                nameInput.value = nameInput.value.trim();
            });

          const dialog = get('fp-edit-dialog');
const openButton = get('fp-edit');

function openProfileDialog() {
    renderPreview();
    dialog.showModal();
    nameInput.focus({ preventScroll: true });
}

function cancelProfileEdit() {
    // Khôi phục tên và ảnh đã lưu khi người dùng hủy.
    releasePreview();
    nameInput.value = savedName;
    photoInput.value = '';
    photoInput.setCustomValidity('');
    removeInput.value = '0';
    status.textContent = '';

    renderPreview();
    dialog.close();
    openButton.focus({ preventScroll: true });
}

openButton.addEventListener('click', openProfileDialog);
get('fp-cancel').addEventListener('click', cancelProfileEdit);
get('fp-dialog-close').addEventListener('click', cancelProfileEdit);

dialog.addEventListener('cancel', (event) => {
    event.preventDefault();
    cancelProfileEdit();
});

// Laravel trả lỗi thì mở lại hộp để người dùng sửa.
@if ($profileError)
    status.textContent = 'Periksa kembali isian. Jika ingin mengganti foto, pilih ulang filenya.';
        openProfileDialog();
@endif
})();


/* Pertahankan posisi scroll setelah profil disimpan */
(() => {
    const profile = document.getElementById('fb-profile');
    if (!profile) return;

    const scrollKey = 'findback:profil:scroll';

    // Mengingat posisi saat formulir dikirim.
    profile.addEventListener('submit', (event) => {
        if (
            event.target.id === 'fp-form' ||
            event.target.id === 'fp-password-form'
        ) {
            sessionStorage.setItem(
                scrollKey,
                String(window.scrollY)
            );
        }
    });

    const savedPosition = sessionStorage.getItem(scrollKey);

    if (savedPosition === null) return;

    const targetY = Number(savedPosition);

    if (!Number.isFinite(targetY)) {
        sessionStorage.removeItem(scrollKey);
        return;
    }

    // Mencegah browser menimpa posisi scroll.
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    const restoreScroll = () => {
        const maxScroll = Math.max(
            0,
            document.documentElement.scrollHeight -
            window.innerHeight
        );

        window.scrollTo({
            top: Math.min(
                Math.max(0, targetY),
                maxScroll
            ),
            behavior: 'instant'
        });
    };

    // Pulihkan posisi setelah halaman selesai dimuat.
    window.addEventListener('load', () => {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                restoreScroll();
                sessionStorage.removeItem(scrollKey);
            });
        });
    }, { once: true });
})();

    const profileToast = document.getElementById('fp-toast');

if (profileToast) {
        const navbar = document.querySelector('.cu-header');

    if (navbar) {
        const positionToast = () => {
            const navbarBottom = navbar.getBoundingClientRect().bottom;
            profileToast.style.top = `${Math.max(0, navbarBottom) + 12}px`;
        };

        positionToast();
        window.addEventListener('resize', positionToast);
        window.addEventListener('scroll', positionToast, { passive: true });

        // Berhenti memantau posisi setelah notifikasi menghilang.
        window.setTimeout(() => {
            window.removeEventListener('resize', positionToast);
            window.removeEventListener('scroll', positionToast);
        }, 6300);
    }
    window.setTimeout(() => {
        profileToast.classList.add('is-leaving');

        window.setTimeout(() => {
            profileToast.remove();
        }, 220);
    }, 6000);
}
    (() => {
    const dialog = document.getElementById('fp-password-dialog');
    const form = document.getElementById('fp-password-form');
    const openButton = document.getElementById('fp-password-open');
    const currentPassword = document.getElementById('fp-current-password');
    const newPassword = document.getElementById('fp-password');
    const confirmation = document.getElementById('fp-confirm');

    function openPasswordDialog() {
        dialog.showModal();
        currentPassword.focus({ preventScroll: true });
    }

    function checkConfirmation() {
    const note = document.getElementById('fp-confirm-note');
    const mismatch = confirmation.value !== ''
        && confirmation.value !== newPassword.value;

    const message = mismatch ? 'Konfirmasi kata sandi belum cocok.' : '';

    confirmation.setCustomValidity(message);
    confirmation.setAttribute('aria-invalid', String(mismatch));

    if (note.textContent !== message) {
        note.textContent = message;
    }
}

    openButton.addEventListener('click', openPasswordDialog);

    document.getElementById('fp-password-close')
        .addEventListener('click', () => dialog.close());

    document.getElementById('fp-password-cancel')
        .addEventListener('click', () => dialog.close());

    newPassword.addEventListener('input', checkConfirmation);
    confirmation.addEventListener('input', checkConfirmation);

    // Chạy khi đóng bằng nút Batal, dấu × hoặc phím Esc.
    dialog.addEventListener('close', () => {
        form.reset();
        checkConfirmation();
        openButton.focus({ preventScroll: true });
    });

    // Nếu Laravel báo sai mật khẩu, mở lại hộp để người dùng thấy lỗi.
    @if ($passwordError)
        openPasswordDialog();
    @endif
})();
    </script>
@endsection