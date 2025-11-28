@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-left">
                    <h4 class="mb-sm-0 font-size-18">Dashboard Utama</h4>
                    <p class="text-muted mb-0">Ringkasan aktivitas siswa untuk bulan <span
                            class="fw-bold">{{ $bulanTeks }}</span>.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">Presensi Perlu Tinjauan</p>
                            <h4 class="mb-0">{{ $pendingPresensiCount }}</h4>
                        </div>
                        <div class="flex-shrink-0 avatar-sm rounded-circle bg-warning-subtle align-self-center">
                            <span class="avatar-title rounded-circle">
                                <i class="fas fa-hourglass-half text-warning font-size-24"></i>
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('presensi.index', ['filter_approval' => 'pending_all']) }}"
                        class="stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">Tugas Tim Belum Selesai</p>
                            <h4 class="mb-0">{{ $pendingTimCount }}</h4>
                        </div>
                        <div class="flex-shrink-0 avatar-sm rounded-circle bg-danger-subtle align-self-center">
                            <span class="avatar-title rounded-circle">
                                <i class="fas fa-tasks text-danger font-size-24"></i>
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('admin.tim.index', ['status' => 'belum_selesai']) }}" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">Total Presensi Masuk Hari Ini</p>
                            <h4 class="mb-0">{{ $hadirHariIni }} <span
                                    class="text-muted fw-normal font-size-14">Sesi</span></h4>
                        </div>
                        <div class="flex-shrink-0 avatar-sm rounded-circle bg-success-subtle align-self-center">
                            <span class="avatar-title rounded-circle">
                                <i class="fas fa-check-circle text-success font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tombol Rekapitulasi (Hanya Admin) --}}
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3">Rekapitulasi Presensi & Laporan PKL</h5>
                    <p class="card-text text-muted mb-3">Klik tombol di bawah ini untuk melihat dan mengelola rekapitulasi
                        presensi dan laporan siswa secara lengkap.</p>
                    <a href="{{ route('dashboard.rekapitulasi.show') }}" class="btn btn-info">
                        <i class="fas fa-file-alt me-1"></i> Rekapitulasi PKL
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Data Siswa --}}
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4 text-center">Filter Detail Aktivitas Siswa</h5>
                    <form id="filterForm" action="{{ route('dashboard') }}" method="GET">
                        <div class="d-flex flex-wrap justify-content-center align-items-end gap-3">
                            <div>
                                <label for="sekolah_filter" class="form-label small mb-1">Sekolah</label>
                                <select id="sekolah_filter" name="sekolah_id"
                                    class="form-select form-select-sm auto-submit-filter" style="width: 220px;">
                                    <option value="">Semua Sekolah</option>
                                    @foreach ($sekolahs as $sekolah)
                                        <option value="{{ $sekolah->id }}"
                                            @if ($sekolah->id == $selectedSekolahId) selected @endif>
                                            {{ $sekolah->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="program_filter" class="form-label small mb-1">Program Keahlian</label>
                                <select id="program_filter" name="program_keahlian_id"
                                    class="form-select form-select-sm auto-submit-filter" style="width: 220px;">
                                    <option value="">Semua Program</option>
                                    @foreach ($programKeahlians as $program)
                                        <option value="{{ $program->id }}"
                                            @if ($program->id == $selectedProgramKeahlianId) selected @endif>
                                            {{ $program->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="bulan_filter" class="form-label small mb-1">Bulan</label>
                                <input id="bulan_filter" type="month" name="bulan"
                                    class="form-control form-control-sm auto-submit-filter" style="width: 220px;"
                                    value="{{ sprintf('%04d-%02d', $selectedTahun, $selectedBulan) }}">
                            </div>
                            <div>
                                <label for="nama_filter" class="form-label small mb-1">Nama Siswa</label>
                                <div class="input-group input-group-sm" style="width: 280px;">
                                    <input id="nama_filter" type="text" name="siswa_nama" class="form-control"
                                        placeholder="Cari..." value="{{ request()->query('siswa_nama') ?? '' }}">
                                    <button class="btn btn-outline-secondary" type="submit"><i
                                            class="fas fa-search"></i></button>
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm"><i
                                        class="fas fa-sync-alt me-1"></i> Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        @forelse ($dataSiswaDetail as $data)
            <div class="col-12 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-size-16">{{ $data['siswa']->name }}</h6>
                        <span
                            class="text-muted font-size-13">{{ $data['siswa']->sekolah->nama ?? 'Sekolah tidak terdaftar' }}</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 border-end pe-4">
                                <p class="text-muted fw-bold mb-3">Rekap Presensi Bulan Ini</p>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-size-14"><i
                                            class="fas fa-check-circle text-success me-2"></i>Hadir</span>
                                    <span
                                        class="badge font-size-13 bg-success-subtle text-success">{{ $data['rekap_bulan_ini']['hadir'] }}
                                        Hari</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-size-14"><i
                                            class="fas fa-notes-medical text-warning me-2"></i>Sakit</span>
                                    <span
                                        class="badge font-size-13 bg-warning-subtle text-warning">{{ $data['rekap_bulan_ini']['sakit'] }}
                                        Hari</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-size-14"><i
                                            class="fas fa-info-circle text-info me-2"></i>Izin</span>
                                    <span
                                        class="badge font-size-13 bg-info-subtle text-info">{{ $data['rekap_bulan_ini']['izin'] }}
                                        Hari</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-size-14"><i
                                            class="fas fa-times-circle text-danger me-2"></i>Alpa</span>
                                    <span
                                        class="badge font-size-13 bg-danger-subtle text-danger">{{ $data['rekap_bulan_ini']['alpa'] }}
                                        Hari</span>
                                </div>
                            </div>

                            <div class="col-md-6 ps-4">
                                <p class="text-muted fw-bold mb-3">Kegiatan Hari Ini</p>
                                @if ($data['kegiatan_hari_ini']->isNotEmpty())
                                    <ol class="mb-2 ps-3">
                                        @foreach ($data['kegiatan_hari_ini'] as $laporan)
                                            @php
                                                $status = optional($laporan->tim)->status_approval ?? 'belum_selesai';
                                                $badgeClass = '';
                                                switch ($status) {
                                                    case 'tugas_selesai':
                                                        $badgeClass = 'bg-success-subtle text-success';
                                                        break;
                                                    case 'ditolak':
                                                        $badgeClass = 'bg-danger-subtle text-danger';
                                                        break;
                                                    default:
                                                        $badgeClass = 'bg-warning-subtle text-warning';
                                                        break;
                                                }
                                            @endphp
                                            <li class="mb-2">
                                                <span class="badge fs-6 {{ $badgeClass }}">
                                                    {{ $laporan->jenisKegiatan->nama_kegiatan ?? 'Kegiatan tidak spesifik' }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ol>
                                    <a href="{{ route('admin.laporan.index', ['filter_user_id' => $data['siswa']->id, 'filter_tanggal' => now()->toDateString()]) }}"
                                        class="btn btn-sm btn-outline-info mt-2">
                                        <i class="fas fa-search me-1"></i> Lihat Detail Laporan
                                    </a>
                                @else
                                    <p class="text-muted fst-italic font-size-14 mb-0">Belum ada laporan kegiatan.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center">Tidak ada data siswa bimbingan yang dapat ditampilkan sesuai
                    filter.
                </div>
            </div>
        @endforelse
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const autoSubmitElements = filterForm.querySelectorAll('.auto-submit-filter');

            autoSubmitElements.forEach(element => {
                element.addEventListener('change', function() {
                    filterForm.submit();
                });
            });
        });
    </script>


    {{-- Judul Halaman Siswa --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="mb-sm-0 font-size-18">Dashboard Siswa</h4>
                <p class="text-muted mb-0">Selamat datang kembali, <span class="fw-bold">{{ $siswa->name }}</span>!
                </p>
            </div>
        </div>
    </div>

    {{-- Kartu Utama Siswa --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{-- 1. Status Presensi Hari Ini --}}
                    <h5 class="card-title">Status Presensi Hari Ini ({{ now()->translatedFormat('l, d M Y') }})</h5>
                    <div class="row mt-3">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <div class="p-3 border rounded text-center">
                                <h6 class="text-muted">Presensi Pagi</h6>
                                @if ($pagi = $presensiHariIni->get('pagi'))
                                    <h5 class="mt-2"><i class="fas fa-check-circle text-success me-1"></i>
                                        {{ $pagi->status }}</h5>
                                    <p class="mb-0 text-muted">pukul
                                        {{ \Carbon\Carbon::parse($pagi->jam_presensi)->format('H:i') }}</p>
                                @else
                                    <h5 class="mt-2 text-warning"><i class="fas fa-hourglass-start me-1"></i> Belum
                                        Presensi</h5>
                                    <p class="mb-0 text-muted">-</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 border rounded text-center">
                                <h6 class="text-muted">Presensi Sore</h6>
                                @if ($sore = $presensiHariIni->get('sore'))
                                    <h5 class="mt-2"><i class="fas fa-check-circle text-success me-1"></i>
                                        {{ $sore->status }}</h5>
                                    <p class="mb-0 text-muted">pukul
                                        {{ \Carbon\Carbon::parse($sore->jam_presensi)->format('H:i') }}</p>
                                @else
                                    <h5 class="mt-2 text-warning"><i class="fas fa-hourglass-start me-1"></i> Belum
                                        Presensi</h5>
                                    <p class="mb-0 text-muted">-</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <hr>

                    {{-- 2. Tugas Tim Hari Ini --}}
                    <h5 class="card-title mt-4">Tugas Tim Hari Ini</h5>
                    <div class="mt-3">
                        @if ($timHariIni)
                            <p>Anda tergabung dalam tim hari ini dengan detail sebagai berikut:</p>
                            <ul>
                                <li><strong>Ketua Tim:</strong> {{ $timHariIni->ketua->name ?? 'N/A' }}</li>
                                <li><strong>Anggota:</strong> {{ $timHariIni->anggota->pluck('name')->implode(', ') }}
                                </li>
                                <li><strong>Status Tugas:</strong> <span
                                        class="badge bg-{{ $timHariIni->status_approval == 'selesai' ? 'success' : 'warning' }}">{{ ucfirst(str_replace('_', ' ', $timHariIni->status_approval)) }}</span>
                                </li>
                            </ul>
                        @else
                            <p class="text-muted fst-italic">Anda tidak tergabung dalam tim manapun hari ini.</p>
                        @endif
                        <div class="d-flex align-items-center gap-2 mt-3">
                            <a href="{{ route('admin.tim.index') }}" class="btn btn-primary btn-sm">Lihat Detail
                                Tugas</a>
                            @if ($todaysTask)
                                <a href="{{ asset('uploads/daily_tasks/' . $todaysTask->task_breakdown) }}"
                                    target="_blank" class="btn btn-info btn-sm">
                                    <i class="fas fa-file-pdf me-1"></i> Lihat Task Breakdown
                                </a>
                            @endif
                        </div>
                    </div>
                    <hr>

                    {{-- 3. Rekap Presensi Bulan Ini --}}
                    <h5 class="card-title mt-4">Rekap Presensi Bulan Ini ({{ $bulanTeks }})</h5>
                    <div class="mt-3">
                        @if ($rekapBulananSiswa)
                            <div class="d-flex justify-content-between align-items-center mb-2"><span><i
                                        class="fas fa-check-circle text-success me-2"></i>Hadir</span><span
                                    class="badge bg-success-subtle text-success">{{ $rekapBulananSiswa['hadir'] }}
                                    Hari</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2"><span><i
                                        class="fas fa-notes-medical text-warning me-2"></i>Sakit</span><span
                                    class="badge bg-warning-subtle text-warning">{{ $rekapBulananSiswa['sakit'] }}
                                    Hari</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2"><span><i
                                        class="fas fa-info-circle text-info me-2"></i>Izin</span><span
                                    class="badge bg-info-subtle text-info">{{ $rekapBulananSiswa['izin'] }}
                                    Hari</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center"><span><i
                                        class="fas fa-times-circle text-danger me-2"></i>Alpa</span><span
                                    class="badge bg-danger-subtle text-danger">{{ $rekapBulananSiswa['alpa'] }}
                                    Hari</span>
                            </div>
                        @else
                            <p class="text-muted">Data rekap belum tersedia.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
