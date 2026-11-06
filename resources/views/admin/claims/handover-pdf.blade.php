<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Bukti Serah Terima</title>
    <link rel="stylesheet"
        href="{{ 'file:///' . ltrim(str_replace('\\', '/', public_path('css/pages/admin/claims/handover-pdf.css')), '/') }}">
    <link rel="stylesheet"
        href="{{ 'file:///' . ltrim(str_replace('\\', '/', public_path('css/shared/view-utilities.css')), '/') }}">
</head>

<body>
    <header class="header">
        <div class="brand">{{ $settings['site_name'] }}</div>
        <h1>BUKTI SERAH TERIMA</h1>
        <div>Dokumen penyerahan barang temuan · {{ $settings['institution_name'] }}</div>
    </header>
    <div class="code">{{ $claim->handover_code }}</div>
    <p class="paragraph">Pada {{ $claim->returned_at->timezone('Asia/Jakarta')->format('d/m/Y') }} pukul
        {{ $claim->returned_at->timezone('Asia/Jakarta')->format('H:i') }} WIB, barang temuan di bawah ini diserahkan
        kepada penerima yang telah diperiksa identitas dan bukti kepemilikannya oleh petugas.</p>
    <h2>01 / IDENTITAS BARANG</h2>
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
    <h2>02 / DATA PENYERAHAN</h2>
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
        <h2>03 / CATATAN PENYERAHAN</h2>
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
    <div class="footer">Data penyerahan tercatat pada sistem FindBack. Dokumen ini belum memuat tanda tangan digital;
        kolom tanda tangan dapat diisi pada cetakan. Lampiran bukti disimpan terpisah dalam arsip sistem.</div>
</body>

</html>
