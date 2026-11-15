@extends('admin.layouts.items')
@section('title', 'Verifikasi Laporan')
@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/header-consistency.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-verification.css') }}?v=31">
    <link rel="stylesheet" href="{{ asset('css/admin/verification-table-columns.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/admin/verification-photo-responsive.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-pm-detail-date.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/report-filters.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/report-list-polish.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/hover-consistency-v6.css') }}?v=20261115-6">
    <link rel="stylesheet" href="{{ asset('css/admin/hover-line-v7.css') }}?v=20261115-7">
    <link rel="stylesheet" href="{{ asset('css/admin/hover-responsive-v8.css') }}?v=20261115-8">
    <link rel="stylesheet" href="{{ asset('css/admin/statistics-motion-v12.css') }}?v=12">
@endsection

@section('content')
    <script defer src="{{ asset('js/admin/statistics-motion-v12.js') }}?v=12"></script>

    <div class="fb-page-heading fb-work-heading">
        <div>
            <p class="fb-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
            <h1>Verifikasi <span>Laporan</span></h1>
            <p class="fb-heading-text">Kelola laporan yang membutuhkan pemeriksaan dan tindak lanjut administrator.</p>
        </div>
    </div>
    @if (session('success'))
        <div class="fb-v-alert is-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="fb-v-alert" role="alert">{{ session('warning') }}</div>
    @endif
    @include('admin.reports.verification.results')

@endsection
