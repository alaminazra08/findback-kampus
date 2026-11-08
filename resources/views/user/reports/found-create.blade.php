@extends('user.layouts.campus')
@section('title', 'Lapor Barang Ditemukan')
@section('content')
    @include('user.reports.form', ['kind' => 'found', 'heading' => 'Lapor Barang Ditemukan'])
@endsection
