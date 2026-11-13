@extends('admin.layouts.items')
@section('title', 'Verifikasi Laporan')
@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/header-consistency.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-verification.css') }}?v=31">
    <link rel="stylesheet" href="{{ asset('css/admin/verification-table-columns.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/admin/verification-photo-responsive.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-pm-detail-date.css') }}?v=1">
@endsection

@section('content')

    <div class="fb-page-heading">
        <div>
            <p class="fb-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
            <h1>Verifikasi <span>Laporan</span></h1>
            <p class="fb-heading-text">Kelola laporan yang membutuhkan pemeriksaan dan tindak lanjut administrator.</p>
        </div><span class="fb-date-label fb-pm-date" aria-label="Tanggal hari ini"><svg viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="5" width="18" height="16" rx="2" />
                <path d="M7 3v4M17 3v4M3 10h18" />
            </svg>{{ now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') }}</span>
    </div>
    @if (session('success'))
        <div class="fb-v-alert is-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="fb-v-alert" role="alert">{{ session('warning') }}</div>
    @endif
    @include('admin.reports.verification.results')

@endsection
