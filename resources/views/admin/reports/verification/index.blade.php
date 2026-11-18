@extends('admin.layouts.items')
@section('title', 'Verifikasi Laporan')
@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/header-consistency.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-verification.css') }}?v=31">
    <link rel="stylesheet" href="{{ asset('css/admin/verification-toast-v1.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/verification-table-columns.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/admin/verification-photo-responsive.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-pm-detail-date.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/report-filters.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/report-list-polish.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/hover-consistency-v6.css') }}?v=20261115-6">
    <link rel="stylesheet" href="{{ asset('css/admin/hover-line-v7.css') }}?v=20261115-7">
@endsection

@section('content')

    <div class="fb-page-heading fb-work-heading">
        <div>
            <p class="fb-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
            <h1>Verifikasi <span>Laporan</span></h1>
            <p class="fb-heading-text">Kelola laporan yang membutuhkan pemeriksaan dan tindak lanjut administrator.</p>
        </div>
    </div>
    @if (session('success') || session('warning'))
        @php $verificationToastWarning = (bool) session('warning'); @endphp
        <div class="fb-verification-toast{{ $verificationToastWarning ? ' is-warning' : '' }}"
             id="fb-verification-toast" role="{{ $verificationToastWarning ? 'alert' : 'status' }}"
             aria-live="{{ $verificationToastWarning ? 'assertive' : 'polite' }}" aria-atomic="true">
            <span class="fb-verification-toast-icon" aria-hidden="true">{{ $verificationToastWarning ? '!' : '✓' }}</span>
            <span class="fb-verification-toast-copy">
                <strong>{{ $verificationToastWarning ? 'Perlu perhatian' : 'Verifikasi berhasil' }}</strong>
                <small>{{ $verificationToastWarning ? session('warning') : session('success') }}</small>
            </span>
            <button type="button" class="fb-verification-toast-close" aria-label="Tutup notifikasi"
                    onclick="this.closest('.fb-verification-toast').remove()">&times;</button>
        </div>
    @endif
    @include('admin.reports.verification.results')

@endsection
