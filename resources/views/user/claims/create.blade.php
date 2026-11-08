@extends('user.layouts.campus')
@section('title', 'Ajukan Klaim')
@section('content')
    <div class="cu-title"><span class="cu-kicker">KEMBALIKAN KEPADA PEMILIKNYA</span>
        <h1>Ajukan klaim barang</h1>
        <p>Jelaskan bukti kepemilikanmu untuk diperiksa oleh admin.</p>
    </div>
    <div class="cu-claim-layout">
        <form class="cu-panel" method="POST" enctype="multipart/form-data" action="{{ route('user.claims.store', $item) }}">
            @csrf<label>Alasan klaim dan ciri kepemilikan *
                <textarea name="proof_description" rows="6" maxlength="3000" required
                    placeholder="Ceritakan ciri yang hanya diketahui pemilik barang...">{{ old('proof_description') }}</textarea>
            </label><label class="cu-upload">@include('user.components.icon', ['icon' => 'upload'])<strong>Foto bukti kepemilikan
                    (opsional)</strong><span>JPG, PNG, WEBP · maksimal 4 MB</span><input name="proof_photo" type="file"
                    accept="image/jpeg,image/png,image/webp" data-preview data-max="4194304"></label><img class="cu-preview"
                hidden alt="Pratinjau bukti">
            <p class="cu-file-info" role="status"></p>
            <div class="cu-note">Setelah klaim dikirim, chat dengan admin tersedia untuk verifikasi dan pengaturan serah
                terima.</div>
            <div class="cu-actions"><a class="cu-btn light" href="{{ route('user.items.show', $item) }}">Batal</a><button
                    class="cu-btn">Kirim klaim →</button></div>
        </form>
        <aside class="cu-panel cu-claim-context">
            <h2>Informasi barang</h2>@include('user.components.card')
        </aside>
    </div>
@endsection
