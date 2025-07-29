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
                    @if (isRole('Admin'))
                        <p>Halo, Admin!</p>
                    @elseif (isRole('Siswa'))
                        <p>Halo, Siswa PKL!</p>
                    @elseif (isRole('Guru'))
                        <p>Halo, Pembimbing!</p>
                    @endif
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="#">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card mini-stats-wid">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">Total Users</p>
                            <h4 class="mb-0">{{ $totalUsers }}</h4>
                        </div>

                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title">
                                    <i class="fa fa-users font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mini-stats-wid">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">Presensi Hari Ini</p>
                            <h4 class="mb-0">{{ $todayPresensi }}</h4>
                        </div>

                        <div class="flex-shrink-0  align-self-center">
                            <div class="avatar-sm rounded-circle bg-success mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-success">
                                    <i class="fa fa-calendar-check font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mini-stats-wid">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">Presensi Pagi</p>
                            <h4 class="mb-0">{{ $pagiPresensi }}</h4>
                        </div>

                        <div class="flex-shrink-0 align-self-center">
                            <div class="avatar-sm rounded-circle bg-warning mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-warning">
                                    <i class="fa fa-sun font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mini-stats-wid">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">Presensi Sore</p>
                            <h4 class="mb-0">{{ $sorePresensi }}</h4>
                        </div>

                        <div class="flex-shrink-0 align-self-center">
                            <div class="avatar-sm rounded-circle bg-info mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-info">
                                    <i class="fa fa-moon font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-md-7">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Presensi Terbaru</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Jenis</th>
                                    <th>Sesi</th>
                                    <th>Jam</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPresensi as $presensi)
                                    <tr>
                                        <td>{{ $presensi->user->name ?? 'N/A' }}</td>
                                        <td>{{ $presensi->jenisPresensi->nama ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $presensi->sesi == 'pagi' ? 'warning' : 'info' }}">
                                                {{ ucfirst($presensi->sesi) }}
                                            </span>
                                        </td>
                                        <td>{{ $presensi->jam_presensi ? date('H:i', strtotime($presensi->jam_presensi)) : 'N/A' }}
                                        </td>
                                        <td>{{ $presensi->tanggal_presensi ? date('d/m/Y', strtotime($presensi->tanggal_presensi)) : 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data presensi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Setting Presensi Aktif</h4>
                    @if ($activeSetting)
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Jadwal Presensi:</h6>
                            <div class="mb-2">
                                <strong>Sesi Pagi:</strong><br>
                                {{ $activeSetting->pagi_mulai }} - {{ $activeSetting->pagi_selesai }}
                            </div>
                            <div class="mb-0">
                                <strong>Sesi Sore:</strong><br>
                                {{ $activeSetting->sore_mulai }} - {{ $activeSetting->sore_selesai }}
                            </div>
                        </div>
                        <a href="{{ route('presensi_setting.index') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-cog me-1"></i> Kelola Setting
                        </a>
                    @else
                        <div class="alert alert-warning">
                            <strong>Peringatan:</strong> Tidak ada setting presensi yang aktif.
                        </div>
                        <a href="{{ route('presensi_setting.create') }}" class="btn btn-success btn-sm">
                            <i class="fa fa-plus me-1"></i> Buat Setting
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/js/scripts/apexcharts.init.js') }}"></script>
@endsection
