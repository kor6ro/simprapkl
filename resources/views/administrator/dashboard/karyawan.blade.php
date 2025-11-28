@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="mb-sm-0 font-size-18">Dashboard Karyawan</h4>
                <p class="text-muted mb-0">Selamat datang kembali, <span class="fw-bold">{{ $karyawan->name }}</span>!</p>
            </div>
        </div>
    </div>

    {{-- Kartu Ringkasan --}}
    <div class="row">
        <div class="col-md-6 col-xl-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">Tugas Tim Perlu Ditinjau</p>
                            <h4 class="mb-0">{{ $pendingTaskCount }} Tim</h4>
                        </div>
                        <div class="flex-shrink-0 avatar-sm rounded-circle bg-warning-subtle align-self-center">
                            <span class="avatar-title rounded-circle">
                                <i class="fas fa-tasks text-warning font-size-24"></i>
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('admin.tim.index', ['status' => 'belum_selesai']) }}" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">Kehadiran Anggota Tim Hari Ini</p>
                            <h4 class="mb-0">{{ $hadirHariIniCount }} dari {{ $totalSiswaBimbingan }} Siswa</h4>
                        </div>
                        <div class="flex-shrink-0 avatar-sm rounded-circle bg-success-subtle align-self-center">
                            <span class="avatar-title rounded-circle">
                                <i class="fas fa-user-check text-success font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Tim Hari Ini --}}
    <div class="row mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Tim Anda Hari Ini ({{ now()->translatedFormat('l, d F Y') }})</h5>
                </div>
                <div class="card-body">
                    @if ($teamsToday->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Anda tidak memimpin tim manapun hari ini.</p>
                        </div>
                    @else
                        @foreach ($teamsToday as $tim)
                            <div class="card border mb-3 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $tim->divisi->nama_divisi ?? 'Nama Tim Tidak Ada' }}</h6>
                                            <p class="card-text text-muted mb-2">
                                                Anggota: 
                                                <span class="fw-medium">
                                                    {{ $tim->anggota->pluck('name')->implode(', ') }}
                                                </span>
                                            </p>
                                        </div>
                                        <div>
                                            <span class="badge {{ $tim->status_badge_class }} fs-6">{{ $tim->status_text }}</span>
                                        </div>
                                    </div>
                                   <a href="{{ route('admin.tim.index', ['ketua_ids' => [$karyawan->id], 'divisi_id' => $tim->divisi_id]) }}" class="btn btn-sm btn-outline-primary mt-2">
    <i class="fas fa-arrow-right me-1"></i> Lihat Detail Tim
</a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection