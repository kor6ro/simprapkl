@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Selamat datang, {{ auth()->user()->name }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Tampilan berbeda untuk Admin dan Siswa --}}
    @if (auth()->user()->group_id == 1)
        {{-- Dashboard Admin --}}
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-1 overflow-hidden">
                                <p class="text-truncate font-size-14 mb-2">Total Presensi Hari Ini</p>
                                <h4 class="mb-0">{{ $todayPresensi }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-primary rounded-3">
                                    <i class="fas fa-users font-size-16"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-1 overflow-hidden">
                                <p class="text-truncate font-size-14 mb-2">Total Pengguna</p>
                                <h4 class="mb-0">{{ $totalUsers }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-success rounded-3">
                                    <i class="fas fa-user-friends font-size-16"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-1 overflow-hidden">
                                <p class="text-truncate font-size-14 mb-2">Presensi Pagi</p>
                                <h4 class="mb-0">{{ $pagiPresensi }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-warning rounded-3">
                                    <i class="fas fa-sun font-size-16"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-1 overflow-hidden">
                                <p class="text-truncate font-size-14 mb-2">Presensi Sore</p>
                                <h4 class="mb-0">{{ $sorePresensi }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-info rounded-3">
                                    <i class="fas fa-moon font-size-16"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Presensi Terbaru</h4>
                    </div>
                    <div class="card-body">
                        @if ($recentPresensi->count() > 0)
                            <div class="table-responsive">
                                <table class="table align-middle table-nowrap">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Tanggal</th>
                                            <th>Sesi</th>
                                            <th>Waktu</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentPresensi as $presensi)
                                            <tr>
                                                <td>{{ $presensi->user->name ?? 'N/A' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($presensi->tanggal_presensi)->format('d/m/Y') }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-soft-{{ $presensi->sesi == 'pagi' ? 'primary' : 'secondary' }}">
                                                        {{ ucfirst($presensi->sesi) }}
                                                    </span>
                                                </td>
                                                <td>{{ $presensi->waktu_presensi }}</td>
                                                <td>
                                                    <span
                                                        class="badge badge-soft-success">{{ ucfirst($presensi->status) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center">
                                <p class="text-muted">Belum ada data presensi</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Dashboard Siswa --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-1"></i>
                        Informasi Presensi
                    </h6>
                    <p class="mb-0">
                        Untuk melakukan presensi, silakan kunjungi halaman
                        <a href="{{ route('presensi.index') }}" class="alert-link fw-bold">Presensi</a>
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Riwayat Presensi Terbaru</h4>
                    </div>
                    <div class="card-body">
                        @if ($riwayatPresensi->count() > 0)
                            <div class="table-responsive">
                                <table class="table align-middle table-nowrap">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Sesi</th>
                                            <th>Waktu</th>
                                            <th>Status</th>
                                            <th>Jenis</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($riwayatPresensi as $presensi)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($presensi->tanggal_presensi)->format('d/m/Y') }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-soft-{{ $presensi->sesi == 'pagi' ? 'primary' : 'secondary' }}">
                                                        {{ ucfirst($presensi->sesi) }}
                                                    </span>
                                                </td>
                                                <td>{{ $presensi->waktu_presensi ?? '-' }}</td>
                                                <td>
                                                    @if ($presensi->status == 'hadir')
                                                        <span class="badge badge-soft-success">Hadir</span>
                                                    @elseif($presensi->status == 'terlambat')
                                                        <span class="badge badge-soft-warning">Terlambat</span>
                                                    @elseif($presensi->status == 'izin')
                                                        <span class="badge badge-soft-info">Izin</span>
                                                    @elseif($presensi->status == 'sakit')
                                                        <span class="badge badge-soft-danger">Sakit</span>
                                                    @else
                                                        <span
                                                            class="badge badge-soft-dark">{{ ucfirst($presensi->status) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($presensi->jenis)
                                                        <span
                                                            class="badge badge-soft-warning">{{ $presensi->jenis }}</span>
                                                    @else
                                                        <span class="badge badge-soft-success">Normal</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="{{ route('presensi.index') }}" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i>Lihat Semua Presensi
                                </a>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="avatar-sm mx-auto mb-3">
                                    <span class="avatar-title rounded-circle bg-light text-muted font-size-16">
                                        <i class="fas fa-calendar-times"></i>
                                    </span>
                                </div>
                                <h6 class="text-muted">Belum ada riwayat presensi</h6>
                                <p class="text-muted mb-3">Mulai presensi untuk melihat riwayat di sini</p>
                                <a href="{{ route('presensi.index') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Mulai Presensi
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
