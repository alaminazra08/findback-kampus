<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Bukti Serah Terima</title>
    <style>{!! file_get_contents(public_path('css/pages/admin/claims/handover-pdf.css')) !!}</style>
</head>

<body>
    <table class="letterhead">
        <tr>
            <td class="letterhead-logo">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo-findback.png'))) }}"
                    alt="Logo FindBack Kampus">
            </td>
            <td class="institution"><strong>{{ $settings['site_name'] }}</strong><br>{{ $settings['institution_name'] }}</td>
        </tr>
    </table>
    <h1>BUKTI SERAH TERIMA BARANG</h1>
    <div class="code">Nomor serah terima <strong>{{ $claim->handover_code }}</strong></div>
    <p class="paragraph">Pada {{ $claim->returned_at->timezone('Asia/Jakarta')->format('d/m/Y') }} pukul
        {{ $claim->returned_at->timezone('Asia/Jakarta')->format('H:i') }} WIB, barang temuan di bawah ini diserahkan
        kepada penerima yang telah diperiksa identitas dan bukti kepemilikannya oleh petugas.</p>
    <h2>Identitas Barang</h2>
    <table class="facts">
        <tr>
            <td>Nama barang</td>
            <td>{{ $claim->handover_item_title }}</td>
        </tr>
        <tr>
            <td>Nomor laporan barang</td>
            <td>BRG-{{ str_pad((string) $claim->lost_found_item_id, 4, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td>Nomor pengajuan klaim</td>
            <td>K-{{ str_pad((string) $claim->id, 4, '0', STR_PAD_LEFT) }}</td>
        </tr>
    </table>
    <h2>Data Penyerahan</h2>
    <table class="facts">
        <tr>
            <td>Nama penerima</td>
            <td>{{ $claim->recipient_name }}</td>
        </tr>
        @if ($claim->recipient_identifier)
            <tr>
                <td>NIM</td>
                <td>{{ $claim->recipient_identifier }}</td>
            </tr>
        @endif
        <tr>
            <td>Petugas penyerah</td>
            <td>{{ $claim->handover_officer_name }}</td>
        </tr>
        <tr>
            <td>Tempat serah terima barang</td>
            <td>{{ $claim->handover_location }}</td>
        </tr>
        <tr>
            <td>Tanggal dan waktu</td>
            <td>{{ $claim->returned_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</td>
        </tr>
        <tr>
            <td>Lampiran bukti</td>
            <td>{{ $claim->handover_evidence_path ? 'Tersimpan dalam arsip privat sistem' : 'Berkas lampiran tidak tercatat' }}
            </td>
        </tr>
    </table>
    @if ($claim->handover_notes)
        <h2>Catatan Penyerahan</h2>
        <div class="notes">{{ $claim->handover_notes }}</div>
    @endif
    <table class="signatures">
        <tr>
            <td>Penerima<div class="sign-name">{{ $claim->recipient_name }}</div>
            </td>
            <td>Petugas penyerah<div class="sign-name">{{ $claim->handover_officer_name }}</div>
            </td>
        </tr>
    </table>
    <div class="footer">
        Kolom tanda tangan dapat diisi pada cetakan.
        @if ($claim->handover_evidence_path)
            Lampiran bukti tersimpan terpisah dalam arsip FindBack.
        @endif
    </div>
</body>

</html>
