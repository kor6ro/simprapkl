@extends('layout.main')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .video-container {
            position: relative;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
            aspect-ratio: 1 / 1;
            max-width: 400px;
            width: 100%;
        }

        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-image {
            width: 100%;
            max-width: 400px;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Data Presensi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Presensi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Fitur Presensi untuk Siswa (hanya muncul jika login sebagai siswa) --}}
    @if (Auth::user()->group_id == 4)
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Status Presensi Hari Ini</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-0 py-2">{{ $statusPresensi['message'] }}</div>
                <div class="mt-3 d-flex gap-2">
                    @if ($statusPresensi['can_presensi'])
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#presensiModal">📷 Presensi
                            Kamera</button>
                    @endif
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#izinModal">🏥 Ajukan
                        Izin/Sakit</button>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            {{-- BAGIAN FILTER --}}
            <div class="row mb-2">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="filter-bulan" class="form-label">Filter Bulan & Tahun</label>
                        <input type="month" id="filter-bulan" class="form-control" value="{{ date('Y-m') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="filter-sekolah" class="form-label">Filter Sekolah</label>
                        <select id="filter-sekolah" class="form-select">
                            <option value="">Semua Sekolah</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter-approval" class="form-label">Status Approval</label>
                        <select id="filter-approval" class="form-select">
                            <option value="">Semua</option>
                            <option value="pending_all">Menunggu Persetujuan</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="custom-search" class="form-label">Cari Siswa</label>
                        <input type="text" id="custom-search" class="form-control" placeholder="Nama siswa...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" onclick="applyFilters()">
                            <i class="fas fa-filter me-1"></i> Terapkan
                        </button>
                    </div>
                </div>
                <hr>
                {{-- AKHIR BAGIAN FILTER --}}

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="{{ route('presensi.create') }}" class="btn btn-success">
                        <i class="fa fa-plus me-1"></i> Tambah Presensi
                    </a>
                    <div class="btn-group">
                        <button class="btn btn-outline-success btn-sm" onclick="exportRekapExcel()">
                            <i class="fas fa-file-excel"></i> Export Rekap Excel
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="exportRekapPDF()">
                            <i class="fas fa-file-pdf"></i> Export Rekap PDF
                        </button>
                    </div>
                </div>

                <table id="presensi-table" class="table table-striped table-bordered dt-responsive nowrap"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Sekolah</th>
                            <th>Tanggal</th>
                            <th>Sesi</th>
                            <th>Jam</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th>Keterangan</th>
                            <th>Bukti</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- Form Hapus Tersembunyi --}}
        <div class="d-none">
            <form id="form-destroy" method="post">@csrf @method('DELETE')</form>
        </div>

        {{-- === MODALS === --}}
        {{-- Modal Presensi Kamera --}}
        <div class="modal fade" id="presensiModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">📷 Presensi Kamera</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">@include('administrator.presensi.partials.camera')</div>
                </div>
            </div>
        </div>
        {{-- Modal Form Izin/Sakit --}}
        <div class="modal fade" id="izinModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">🏥 Form Izin/Sakit</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">@include('administrator.presensi.partials.form_izinsakit')</div>
                </div>
            </div>
        </div>
        {{-- Modal untuk melihat gambar --}}
        <div class="modal fade" id="imageModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bukti Foto</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center"><img id="modalImage" src="" class="img-fluid"
                            alt="Bukti foto"></div>
                </div>
            </div>
        </div>
    @endsection

    @section('js')
        <script>
            let presensiTable;

            $(document).ready(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                presensiTable = $('#presensi-table').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    searching: false,
                    ajax: {
                        url: "{{ route('presensi.data.unified') }}",
                        type: "POST",
                        data: function(d) {
                            d.filter_bulan = $('#filter-bulan').val();
                            d.filter_sekolah = $('#filter-sekolah').val();
                            d.filter_approval = $('#filter-approval').val(); // Mengirim filter approval
                            d.search = {
                                value: $('#custom-search').val()
                            };
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            searchable: false,
                            orderable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'nama',
                            name: 'user.name'
                        },
                        {
                            data: 'sekolah',
                            name: 'user.sekolah.nama'
                        },
                        {
                            data: 'tanggal',
                            name: 'tanggal_presensi'
                        },
                        {
                            data: 'sesi_badge',
                            name: 'sesi'
                        },
                        {
                            data: 'jam_presensi',
                            name: 'jam_presensi'
                        },
                        {
                            data: 'status_badge',
                            name: 'status'
                        },
                        {
                            data: 'approval_badge',
                            name: 'approval_status'
                        },
                        {
                            data: 'keterangan',
                            name: 'keterangan'
                        },
                        {
                            data: 'bukti_foto',
                            name: 'bukti_foto',
                            searchable: false,
                            orderable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'id',
                            name: 'id',
                            searchable: false,
                            orderable: false,
                            render: function(data, type, row) {
                                let buttons = '';

                                // Logika Tombol Aksi untuk Admin
                                if (row.is_admin) {
                                    // Jika ada permintaan persetujuan
                                    if (row.approval_status === 'pending' || row.approval_status ===
                                        'pending_update') {
                                        buttons +=
                                            `<button class="btn btn-success btn-sm action-approve" title="Setujui"><i class="fa fa-check"></i></button>`;
                                        buttons +=
                                            `<button class="btn btn-danger btn-sm action-reject" title="Tolak"><i class="fa fa-times"></i></button>`;
                                    } else { // Jika tidak, tampilkan tombol standar
                                        buttons +=
                                            `<button class="btn btn-warning btn-sm action-edit"><i class="fa fa-edit"></i></button>`;
                                        buttons +=
                                            `<button class="btn btn-danger btn-sm action-hapus"><i class="fa fa-trash-alt"></i></button>`;
                                    }
                                }
                                // Logika Tombol Aksi untuk Siswa
                                else if (row.is_owner) {
                                    if (row.approval_status === 'pending_update') {
                                        return '<span class="badge bg-warning">Menunggu</span>';
                                    } else {
                                        return '<button class="btn btn-warning btn-sm action-edit"><i class="fa fa-edit"></i></button>';
                                    }
                                }
                                return `<div class="btn-group">${buttons}</div>`;
                            },
                            className: 'text-center'
                        }
                    ],
                    createdRow: function(row, data) {
                        // Handler untuk semua tombol
                        $(row).find(".action-edit").click(() => window.location.href =
                            `{{ url('presensi') }}/${data.id}/edit`);

                        $(row).find(".action-hapus").click(() => {
                            Swal.fire({
                                icon: "warning",
                                title: "Anda Yakin?",
                                text: "Data ini akan dihapus permanen.",
                                showCancelButton: true,
                                confirmButtonText: "Ya, Hapus!",
                                cancelButtonText: "Batal",
                            }).then(result => {
                                if (result.value) $('#form-destroy').attr('action',
                                    `{{ url('presensi') }}/${data.id}`).submit();
                            });
                        });

                        $(row).find(".action-approve").click(() => processApproval(data.id, 'approve'));
                        $(row).find(".action-reject").click(() => processApproval(data.id, 'reject'));
                    },
                    order: [
                        [3, 'desc']
                    ]
                });

                loadSekolahFilter();
            });

            function applyFilters() {
                presensiTable.ajax.reload();
            }

            function loadSekolahFilter() {
                $.get("{{ route('presensi.sekolah.list') }}", data => {
                    const sekolahSelect = $('#filter-sekolah');
                    sekolahSelect.find('option:not(:first)').remove();
                    data.forEach(s => sekolahSelect.append(new Option(s.nama, s.id)));
                });
            }

            // FUNGSI BARU UNTUK PROSES APPROVAL VIA AJAX
            function processApproval(presensiId, action) {
                const actionText = action === 'approve' ? 'menyetujui' : 'menolak';
                Swal.fire({
                    title: `Anda yakin ingin ${actionText}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: `Ya, ${actionText}!`,
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(`{{ url('presensi/approval') }}/${presensiId}`, {
                                action: action
                            })
                            .done(function(response) {
                                Swal.fire('Berhasil!', response.success, 'success');
                                presensiTable.ajax.reload(null, false); // Reload tabel tanpa pindah halaman
                            })
                            .fail(function() {
                                Swal.fire('Gagal!', 'Terjadi kesalahan saat memproses.', 'error');
                            });
                    }
                });
            }

            // ... (fungsi export dan showImage tetap sama) ...
        </script>

        @include('administrator.presensi.partials.camera_script')
        @if (session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}'
                });
            </script>
        @endif
        @if (session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{{ session('error') }}'
                });
            </script>
        @endif
    @endsection
