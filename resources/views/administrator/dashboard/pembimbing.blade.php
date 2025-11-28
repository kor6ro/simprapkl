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

    <div class="row mt-3">
        <div class="col-12">
            <div class="card" id="filterCard">
                <div class="card-header bg-light">
                    <a class="text-dark text-decoration-none d-flex justify-content-between align-items-center"
                        data-bs-toggle="collapse" href="#filterCollapse" role="button" aria-expanded="false"
                        aria-controls="filterCollapse">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-filter me-2"></i> Filter Detail Aktivitas Siswa
                        </h5>
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </a>
                </div>
                <div class="collapse" id="filterCollapse">
                    <div class="card-body">
                        <form id="filterForm" action="{{ route('dashboard') }}" method="GET">
                            <div class="d-flex flex-wrap justify-content-center align-items-end gap-3">

                                {{-- Filter Periode PKL --}}
                                <div>
                                    <label for="periode_filter" class="form-label small mb-1">Periode PKL</label>
                                    <select id="periode_filter" name="periode_pkl_id"
                                        class="form-select form-select-sm auto-submit-filter" style="width: 220px;">
                                        <option value="">Semua Periode</option>
                                        @foreach ($periodePkls as $periode)
                                            <option value="{{ $periode->id }}"
                                                @if ($periode->id == $selectedPeriodePklId) selected @endif>
                                                {{ \Carbon\Carbon::parse($periode->awal_periode)->format('d M Y') }} -
                                                {{ \Carbon\Carbon::parse($periode->akhir_periode)->format('d M Y') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filter Sekolah --}}
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

                                {{-- Filter Program Keahlian --}}
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

                                {{-- Filter Bulan --}}
                                <div>
                                    <label for="bulan_filter" class="form-label small mb-1">Bulan</label>
                                    <input id="bulan_filter" type="month" name="bulan"
                                        class="form-control form-control-sm auto-submit-filter" style="width: 220px;"
                                        value="{{ sprintf('%04d-%02d', $selectedTahun, $selectedBulan) }}">
                                </div>

                                {{-- Filter Nama Siswa --}}
                                <div>
                                    <label for="nama_filter" class="form-label small mb-1">Nama Siswa</label>
                                    <div class="input-group input-group-sm" style="width: 280px;">
                                        <input id="nama_filter" type="text" name="siswa_nama" class="form-control"
                                            placeholder="Cari..." value="{{ request()->query('siswa_nama') ?? '' }}">
                                        <button class="btn btn-outline-secondary" type="submit"><i
                                                class="fas fa-search"></i></button>
                                    </div>
                                </div>

                                {{-- Tombol Reset --}}
                                <div>
                                    <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm"
                                        id="resetFilter"><i class="fas fa-sync-alt me-1"></i> Reset</a>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row mt-3">
        @forelse ($dataSiswaDetail as $data)
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-size-16">{{ $data['siswa']->name }}</h6>
                        <span
                            class="text-muted font-size-13">{{ $data['siswa']->sekolah->nama ?? 'Sekolah tidak terdaftar' }}</span>
                    </div>
                    <div class="card-body p-4">
                        <div>
                            <p class="text-muted fw-bold mb-3">Rekap Presensi Bulan Ini</p>
                            <div class="row g-2">
                                {{-- Kartu Hadir --}}
                                <div class="col-md-3 col-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="avatar-sm rounded bg-success-subtle text-success d-flex align-items-center justify-content-center">
                                                        <i class="cil-check-alt fs-4"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <p class="text-muted mb-1">Hadir</p>
                                                    <h5 class="mb-0">{{ $data['rekap_bulan_ini']['hadir'] ?? 0 }}</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Kartu Sakit --}}
                                <div class="col-md-3 col-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="avatar-sm rounded bg-warning-subtle text-warning d-flex align-items-center justify-content-center">
                                                        <i class="cil-hospital fs-4"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <p class="text-muted mb-1">Sakit</p>
                                                    <h5 class="mb-0">{{ $data['rekap_bulan_ini']['sakit'] ?? 0 }}</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Kartu Izin --}}
                                <div class="col-md-3 col-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="avatar-sm rounded bg-info-subtle text-info d-flex align-items-center justify-content-center">
                                                        <i class="cil-info fs-4"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <p class="text-muted mb-1">Izin</p>
                                                    <h5 class="mb-0">{{ $data['rekap_bulan_ini']['izin'] ?? 0 }}</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Kartu Alpa --}}
                                <div class="col-md-3 col-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="avatar-sm rounded bg-danger-subtle text-danger d-flex align-items-center justify-content-center">
                                                        <i class="cil-x-circle fs-4"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <p class="text-muted mb-1">Alpa</p>
                                                    <h5 class="mb-0">{{ $data['rekap_bulan_ini']['alpa'] ?? 0 }}</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-0">
                        <div>
                            <p class="text-muted fw-bold mb-3">Kegiatan Hari Ini</p>
                            @if ($data['kegiatan_hari_ini']->isNotEmpty())
                                <div class="d-flex flex-wrap gap-2 mb-3">
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
                                        <span class="badge {{ $badgeClass }}">
                                            <span style="font-size: 1.15em;">
                                                {{ $loop->iteration }}.
                                                {{ $laporan->jenisKegiatan->nama_kegiatan ?? 'Kegiatan tidak spesifik' }}
                                            </span>
                                        </span>
                                    @endforeach
                                </div>
                                <a href="{{ route('admin.laporan.index', ['filter_user_id' => $data['siswa']->id, 'filter_tanggal' => now()->toDateString()]) }}"
                                    class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-search me-1"></i> Lihat Detail Laporan
                                </a>
                            @else
                                <p class="text-muted fst-italic font-size-14 mb-0">Belum ada laporan kegiatan.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center">Tidak ada data siswa bimbingan yang dapat ditampilkan
                    sesuai filter.
                </div>
            </div>
        @endforelse
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const filterCollapseElement = document.getElementById('filterCollapse');
            const studentListContainer = document.getElementById('student-list-container');
            const resetBtn = document.getElementById('resetFilter');

            const filterStateKey = 'dashboardFilterState';
            const scrollToResultsKey = 'dashboardScrollToResults';

            const saveStatesBeforeSubmit = () => {
                const isFilterOpen = filterCollapseElement.classList.contains('show');
                sessionStorage.setItem(filterStateKey, isFilterOpen);

                sessionStorage.setItem(scrollToResultsKey, 'true');
            };

            if (sessionStorage.getItem(filterStateKey) === 'true') {
                const bsCollapse = new bootstrap.Collapse(filterCollapseElement, {
                    toggle: false
                });
                bsCollapse.show();
            }

            if (sessionStorage.getItem(scrollToResultsKey) === 'true') {
                if (studentListContainer) {
                    studentListContainer.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
                sessionStorage.removeItem(scrollToResultsKey);
            }

            filterForm.addEventListener('submit', saveStatesBeforeSubmit);

            filterForm.querySelectorAll('.auto-submit-filter').forEach(element => {
                element.addEventListener('change', () => filterForm.requestSubmit());
            });

            resetBtn.addEventListener('click', function(e) {
                sessionStorage.removeItem(filterStateKey);
                sessionStorage.removeItem(scrollToResultsKey);
            });
        });
    </script>
@endpush
