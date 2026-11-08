@extends('user.layouts.campus')
@section('title', 'Lapor Barang Hilang')
@section('content')
    @include('user.reports.form', ['kind' => 'lost', 'heading' => 'Lapor Barang Hilang'])
@endsection
