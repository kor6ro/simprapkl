@extends('layout.main')
@section('css')
    <style>
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between ">
                <h4 class="mb-sm-0 font-size-18">
                    <p>Selamat datang, {{ auth()->user()->name }}</p>
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Tombol Presensi untuk Siswa --}}
    @if (isRole('Siswa'))
        <div class="row mb-3">
            <div class="col-auto">
                <a href="{{ route('presensi.create') }}" class="btn btn-success">
                    <i class="fa fa-plus me-1"></i> Presensi Hari Ini
                </a>
            </div>
        </div>
    @endif

    {{-- Hanya tampil untuk Admin & Guru --}}
@endsection

@section('js')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/js/scripts/apexcharts.init.js') }}"></script>
@endsection
