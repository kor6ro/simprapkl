@extends('layout.main')

{{-- Blok @section('styles') sudah dihapus --}}

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0">Rekapitulasi Presensi & Laporan PKL</h4>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Batal ke Dashboard
            </a>
        </div>

        {{-- Notifikasi --}}
        @if (session('success') || session('error'))
            <div class="alert alert-{{ session('success') ? 'success' : 'danger' }} alert-dismissible" role="alert">
                {{ session('success') ?: session('error') }}
                @if (session('download_file_path'))
                    <a href="{{ route('dashboard.rekapitulasi.export.download') }}" class="btn btn-sm btn-primary ms-3">
                        <i class="fas fa-download"></i> Unduh File
                    </a>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


        <div class="card mb-4">
            {{-- BARU: Card Header sebagai Tombol Toggle --}}
            <div class="card-header" id="filterCardHeader"
                style="cursor: pointer; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <h5 class="card-title mb-0">
                    <a href="#" class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse"
                        style="text-decoration: none; color: inherit;">
                        <span>
                            <i class="fas fa-filter me-2"></i>
                            Opsi Filter
                        </span>
                        {{-- Icon chevron akan berubah via JavaScript --}}
                        <i class="fas fa-chevron-up toggle-icon"></i>
                    </a>
                </h5>
            </div>

            {{-- BARU: Wrapper untuk Collapse --}}
            <div class="collapse" id="filterCollapse">
                <div class="card-body">
                    {{-- Form filter (Konten Asli Anda) --}}
                    <form action="{{ route('dashboard.rekapitulasi.show') }}" method="GET" id="filter-form">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="periode_pkl_id" class="form-label">Periode PKL</label>
                                <select name="periode_pkl_id" id="periode_pkl_id" class="form-select">
                                    <option value="">Semua Periode</option>
                                    @foreach ($periodePkls as $periode)
                                        <option value="{{ $periode->id }}"
                                            {{ ($filters['periode_pkl_id'] ?? '') == $periode->id ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::parse($periode->awal_periode)->format('d M Y') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="sekolah_id" class="form-label">Sekolah</label>
                                <select name="sekolah_id" id="sekolah_id" class="form-select">
                                    <option value="">Semua Sekolah</option>
                                    @foreach ($sekolahs as $sekolah)
                                        <option value="{{ $sekolah->id }}"
                                            {{ ($filters['sekolah_id'] ?? '') == $sekolah->id ? 'selected' : '' }}>
                                            {{ $sekolah->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="program_keahlian_id" class="form-label">Program Keahlian</label>
                                <select name="program_keahlian_id" id="program_keahlian_id" class="form-select">
                                    <option value="">Semua Program</option>
                                    @foreach ($programKeahlians as $program)
                                        <option value="{{ $program->id }}"
                                            {{ ($filters['program_keahlian_id'] ?? '') == $program->id ? 'selected' : '' }}>
                                            {{ $program->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control"
                                    value="{{ $filters['tanggal_awal'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control"
                                    value="{{ $filters['tanggal_akhir'] ?? '' }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Kartu Tabel Rekapitulasi (Konten Asli Anda) --}}
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Tabel Rekapitulasi</h5>
                    <form id="export-form" action="{{ route('dashboard.rekapitulasi.export.process') }}" method="POST">
                        @csrf
                        @foreach ($filters as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body" style="overflow-x: auto;">
                <table class="table table-bordered table-hover" id="rekap-table">
                    {{-- ... Isi tabel Anda (tidak berubah) ... --}}
                    <thead>
                        <tr>
                            <th class="text-nowrap text-center" rowspan="2" style="width: 50px; vertical-align: middle;">
                                NO</th>
                            <th class="text-nowrap text-center" rowspan="2"
                                style="min-width: 150px; vertical-align: middle;">NAMA SISWA</th>
                            @foreach ($semuaTanggal as $tanggal)
                                <th class="text-center" colspan="2" style="min-width: 60px;">{{ $tanggal->day }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($semuaTanggal as $tanggal)
                                @php $isHoliday = $tanggal->isWeekend(); @endphp
                                <th class="text-center {{ $isHoliday ? 'bg-light' : '' }}">Absen</th>
                                <th class="text-center {{ $isHoliday ? 'bg-light' : '' }}">Laporan</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rekapData as $index => $data)
                            <tr>
                                <td class="text-center" style="vertical-align: middle;">{{ $index + 1 }}</td>
                                <td class="text-nowrap" style="vertical-align: middle;">{{ $data['siswa']->name }}</td>
                                @foreach ($semuaTanggal as $tanggalObj)
                                    @php
                                        $tanggal = $tanggalObj->toDateString();
                                        $absen = $data['rekap_harian'][$tanggal]['absen'] ?? '-';
                                        $laporan = $data['rekap_harian'][$tanggal]['laporan'] ?? '-';
                                        $isHoliday = $tanggalObj->isWeekend();
                                    @endphp

                                    @if ($isHoliday && $absen === 'LBR')
                                        <td colspan="2" class="text-center"
                                            style="height: 40px; background-color: #f8f9fa; font-weight: bold; vertical-align: middle;">
                                            <span
                                                style="writing-mode: vertical-rl; text-orientation: mixed; white-space: nowrap; transform: rotate(270deg);">LIBUR</span>
                                        </td>
                                    @else
                                        @php
                                            $cellBaseStyle = 'height: 40px; vertical-align: middle;';
                                            $absenStyle = $cellBaseStyle;
                                            $laporanStyle = $cellBaseStyle;

                                            switch ($absen) {
                                                case 'H':
                                                    $absenStyle .= ' background-color: #A7F3D0;';
                                                    break; // Vibrant Green
                                                case 'S':
                                                    $absenStyle .= ' background-color: #FDE68A;';
                                                    break; // Vibrant Yellow
                                                case 'A':
                                                    $absenStyle .= ' background-color: #FCA5A5;';
                                                    break; // Vibrant Red
                                                case 'I':
                                                    $absenStyle .= ' background-color: #93C5FD;';
                                                    break; // Vibrant Blue
                                            }
                                            if ($laporan == 'OK') {
                                                $laporanStyle .= ' background-color: #93C5FD;'; // Vibrant Blue
                                            }
                                        @endphp
                                        <td class="text-center" style="{{ $absenStyle }}">
                                            {{ $absen }}
                                        </td>
                                        <td class="text-center" style="{{ $laporanStyle }}">
                                            {{ $laporan }}
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 2 + $semuaTanggal->count() * 2 }}" class="text-center">Tidak ada data
                                    untuk
                                    ditampilkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- BARU: JavaScript untuk mengubah icon chevron --}}
    {{-- Anda bisa memindahkan ini ke file JS terpisah atau @push('scripts') jika ada di layout Anda --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var filterCollapse = document.getElementById('filterCollapse');
            var toggleIcon = document.querySelector('[data-bs-target="#filterCollapse"] .toggle-icon');

            if (filterCollapse && toggleIcon) {
                // Event saat collapse mulai ditampilkan
                filterCollapse.addEventListener('show.bs.collapse', function() {
                    toggleIcon.classList.remove('fa-chevron-up');
                    toggleIcon.classList.add('fa-chevron-down');
                });

                // Event saat collapse mulai disembunyikan
                filterCollapse.addEventListener('hide.bs.collapse', function() {
                    toggleIcon.classList.remove('fa-chevron-down');
                    toggleIcon.classList.add('fa-chevron-up');
                });
            }
        });
    </script>
@endsection
