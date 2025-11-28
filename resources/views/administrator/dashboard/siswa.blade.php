@extends('layout.main')

@section('content')
    {{-- Judul Halaman Siswa --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="mb-sm-0 font-size-18">Dashboard Siswa</h4>
                <p class="text-muted mb-0">Selamat datang kembali, <span class="fw-bold">{{ $siswa->name }}</span>!</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="cil-sun me-2 text-muted"></i>Presensi Pagi</span>
                            @if ($pagi = $presensiHariIni->get('pagi'))
                                <span
                                    class="fw-bold text-success">{{ \Carbon\Carbon::parse($pagi->presensi_at)->format('H:i') }}
                                    WIB</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Belum</span>
                            @endif
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="cil-moon me-2 text-muted"></i>Presensi Sore</span>
                            @if ($sore = $presensiHariIni->get('sore'))
                                <span
                                    class="fw-bold text-success">{{ \Carbon\Carbon::parse($sore->presensi_at)->format('H:i') }}
                                    WIB</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Belum</span>
                            @endif
                        </div>
                    </div>

                    <hr class="my-3">

                    <h5 class="card-title mb-3">Rekap Presensi Bulan Ini ({{ $bulanTeks }})</h5>
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

                    <hr class="my-3">

                    <h5 class="card-title mb-2">Tugas Tim Hari Ini</h5>
                    <div>
                         @forelse ($daftarTimHariIni as $tim)
        <div class="card border shadow-sm mb-3">
            <div class="card-body">
                <p class="mb-2">
                    Anda tergabung dalam tim divisi <strong>{{ $tim->divisi->nama_divisi }}</strong> 
                    (PIC: {{ $tim->ketua->pluck('name')->implode(', ') }})
                </p>
                <p class="mb-0">
                    Status tugas: <span class="badge {{ $tim->status_badge_class }}">{{ $tim->status_text }}</span>
                </p>
            </div>
        </div>
    @empty
        <div class="text-center my-3">
            <i class="cil-briefcase cil-3x text-muted"></i>
            <p class="text-muted mt-2 mb-0">Tidak ada tugas tim hari ini.</p>
        </div>
    @endforelse

    <div class="d-grid gap-2 mt-3">
        <a href="{{ route('admin.tim.index') }}" class="btn btn-primary btn-sm flex-grow-1">
            <i class="fas fa-users me-1"></i> Lihat Semua Detail Tim & Laporan
        </a>
        
        {{-- Tombol untuk task breakdown tidak berubah --}}
        @if ($todaysTask)
            @if ($todaysTask->tipe == 'file')
                <a href="{{ asset('uploads/daily_tasks/' . $todaysTask->task_breakdown) }}"
                    target="_blank" class="btn btn-info btn-sm flex-grow-1">
                    <i class="fas fa-file-alt me-1"></i> Lihat Task Breakdown
                </a>
            @elseif($todaysTask->tipe == 'teks')
                <button type="button" class="btn btn-info btn-sm view-task-text flex-grow-1"
                    data-bs-toggle="modal" data-bs-target="#taskTextModal"
                    data-task-text="{{ $todaysTask->deskripsi_tugas }}">
                    <i class="fas fa-eye me-1"></i> Lihat Task Breakdown
                </button>
            @endif
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Modal untuk menampilkan Task Breakdown berbasis Teks --}}
    <div class="modal fade" id="taskTextModal" tabindex="-1" aria-labelledby="taskTextModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskTextModalLabel">Rincian Tugas Harian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <pre id="taskTextContent" style="white-space: pre-wrap; word-wrap: break-word; font-weight: bold;"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Event listener untuk tombol "Lihat Rincian Tugas" di dashboard siswa
            $('.card-body').on('click', '.view-task-text', function() {
                const taskText = $(this).data('task-text');

                const urlRegex =
                    /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
                const textWithLinks = taskText.replace(urlRegex, function(url) {
                    return `<a href="${url}" target="_blank" rel="noopener noreferrer">${url}</a>`;
                });

                $('#taskTextContent').html(textWithLinks);
            });
        });
    </script>
@endsection
