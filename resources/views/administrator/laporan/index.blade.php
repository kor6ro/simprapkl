@extends('layout.main')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        #laporan-table td.wrap-text {
            white-space: normal !important;
            /* Izinkan teks turun ke bawah */
            word-wrap: break-word;
            /* Pecah kata yang sangat panjang jika perlu */
            overflow-wrap: break-word;
            /* Alternatif untuk word-wrap */
            max-width: 300px;
            /* Opsional: batasi lebar maksimum kolom */
        }

        .row-action {
            display: flex;
            gap: 0.25rem;
            justify-content: center;
        }

        .uniform-status-badge {
            display: inline-block;
            min-width: 110px;
            padding-left: .75rem;
            padding-right: .75rem;
            text-align: center;
        }

        .btn-undo-approve {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
        }

        .btn-undo-approve:hover {
            background-color: #5a6268;
            border-color: #545b62;
            color: #fff;
        }

        #modalImage {
            max-width: 350px;
            width: 100%;
        }

        #laporan-table thead th.no-sort::before,
        #laporan-table thead th.no-sort::after {
            display: none !important;
        }

        .collapse-icon {
            transition: transform 0.3s ease;
        }

        a[aria-expanded="true"] .collapse-icon {
            transform: rotate(180deg);
        }

        table.dataTable.dtr-inline.collapsed>tbody>tr[role="row"]>td:first-child,
        table.dataTable.dtr-inline.collapsed>tbody>tr[role="row"]>th:first-child {
            position: relative;
            padding-left: 30px;
        }

        tr.child td.child {
            padding: 0 !important;
        }

        ul.dtr-details {
            width: 100%;
            list-style-type: none;
            margin-bottom: 0;
            padding: 0.5rem 1rem;
        }

        ul.dtr-details li {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.4rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        ul.dtr-details li:last-child {
            border-bottom: none;
        }

        .dtr-title {
            font-weight: bold;
            flex-shrink: 0;
            margin-right: 1rem;
        }

        .dtr-data {
            text-align: left;
            white-space: normal !important;
            word-break: break-word;
        }
     
    </style>
@endsection

@section('content')
    @if (in_array(auth()->user()->group_id, [1, 2, 4, 3, 5, 6, 7]))
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">{{ $judul }}</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Laporan</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-start gap-2 mb-3">
           @if ($isFilteredByTim)
    {{-- Jika ini adalah laporan tim, kembali ke halaman "Tugas" --}}
    <a href="{{ route('admin.tim.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
@elseif ($isFilteredFromDashboard)
    {{-- Jika ini dari dashboard, kembali ke dashboard --}}
    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
@endif

            @if (auth()->user()->group_id == 4)
    @php
     
        $createUrl = route('admin.laporan.create');
        if (isset($isFilteredByTim) && $isFilteredByTim) {
            $createUrl = route('admin.laporan.create', ['tim_id' => request('tim_id')]);
        }
    @endphp

    <a href="{{ $createUrl }}" class="btn btn-success">
        <i class="fas fa-plus me-1"></i> Buat Laporan Baru
    </a>
@endif
        </div>

        <div class="card">
            @if (!$isFilteredByTim && !$isFilteredFromDashboard)
                <div class="card-header bg-light">
                    <a class="text-dark text-decoration-none d-flex justify-content-between align-items-center"
                        data-bs-toggle="collapse" href="#filterCollapse" role="button" aria-expanded="false"
                        aria-controls="filterCollapse">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-filter me-2"></i> Filter Laporan
                        </h5>
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </a>
                </div>

                <div class="collapse" id="filterCollapse">
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-md-2 mb-2">
                                <label for="filter-periode" class="form-label">Periode PKL:</label>
                                <select id="filter-periode" class="form-select form-select-sm">
                                    <option value="">Semua Periode</option>
                                    @foreach ($periodePkls as $periode)
                                        <option value="{{ $periode->id }}">
                                            {{ \Carbon\Carbon::parse($periode->awal_periode)->format('d M Y') }} -
                                            {{ \Carbon\Carbon::parse($periode->akhir_periode)->format('d M Y') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="filter-bulan" class="form-label">Bulan/Tahun:</label>
                                <input type="month" id="filter-bulan" class="form-control form-control-sm"
                                    value="{{ date('Y-m') }}">
                            </div>
                            @if (in_array(auth()->user()->group_id, [1, 2]))
                                <div class="col-md-2 mb-2">
                                    <label for="filter-nama-siswa" class="form-label">Nama Siswa:</label>
                                    <input type="text" id="filter-nama-siswa" class="form-control form-control-sm"
                                        placeholder="Ketik nama siswa...">
                                </div>
                            @endif
                            <div class="col-md-2 mb-2">
                                <label for="filter-divisi" class="form-label">Divisi:</label>
                                <select id="filter-divisi" class="form-select form-select-sm">
                                    <option value="">Semua Divisi</option>
                                    @foreach ($daftarDivisi as $divisi)
                                        <option value="{{ $divisi->id }}">{{ $divisi->nama_divisi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="filter-status" class="form-label">Status Laporan:</label>
                                <select id="filter-status" class="form-select form-select-sm">
                                    <option value="">Semua Status</option>
                                    <option value="pending">Menunggu Persetujuan</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="rejected">Perlu Revisi</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2 d-flex align-items-end">
    <button type="button" id="reset-filter-btn" class="btn btn-secondary btn-sm w-100">
        <i class="fas fa-undo me-1"></i> Reset Filter
    </button>
</div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card-body">
                @if (in_array(auth()->user()->group_id, [1, 2]))
                    <button type="button" id="exportPdfBtn" class="btn btn-danger btn-sm mb-3">
                        <i class="fas fa-file-pdf me-1"></i> Export Laporan
                    </button>
                @endif

            <table class="table table-bordered table-striped" id="laporan-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            @if (auth()->user()->group_id != 4 || $isFilteredByTim)
                                <th>Nama Siswa</th>
                            @endif
                            <th>Tim</th>
                            <th>Jenis Kegiatan</th>
                            <th>Deskripsi</th>
                            <th>Bukti Foto</th>
                            <th>Tanggal Lapor</th>
                            <th>Status Laporan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="modal fade" id="fotoModal" tabindex="-1" aria-labelledby="fotoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fotoModalLabel">Foto Dokumentasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="" alt="Foto Dokumentasi" class="img-fluid" id="modalImage">
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="feedbackModalLabel">Berikan Alasan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="feedbackForm" action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="feedback" class="form-label">Alasan Revisi <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="4"
                                placeholder="Jelaskan mengapa tugas ini perlu direvisi..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger" id="feedbackSubmitButton">
                            Kirim Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="viewFeedbackModal" tabindex="-1" aria-labelledby="viewFeedbackModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewFeedbackModalLabel">Alasan Revisi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <blockquote class="blockquote">
                        <p id="feedbackContent" class="mb-0"></p>
                    </blockquote>
                    <div id="feedbackApprover" class="mt-3 text-muted small fst-italic"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Event handler untuk tombol Export PDF
            $('#exportPdfBtn').on('click', function() {
                const urlParams = new URLSearchParams(window.location.search);
                const timId = urlParams.get('tim_id');
                const filterUserId = urlParams.get('filter_user_id');
                const filterTanggal = urlParams.get('filter_tanggal');
                const bulan = $('#filter-bulan').val();
                const periodeId = $('#filter-periode').val();
                const namaSiswa = $('#filter-nama-siswa').val();
                const divisiId = $('#filter-divisi').val();
                const status = $('#filter-status').val();
                let exportUrl = '{{ route('admin.laporan.export.pdf') }}?';
                const params = new URLSearchParams();
                if (timId) params.append('tim_id', timId);
                if (filterUserId) params.append('filter_user_id', filterUserId);
                if (filterTanggal) params.append('filter_tanggal', filterTanggal);
                if (bulan) params.append('bulan', bulan);
                if (periodeId) params.append('periode_id', periodeId);
                if (namaSiswa) params.append('nama_siswa', namaSiswa);
                if (divisiId) params.append('divisi_id', divisiId);
                if (status) params.append('status', status);
                window.open(exportUrl + params.toString(), '_blank');
            });

            // Event handler untuk modal lihat foto
            $('#fotoModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const fotoUrl = button.data('foto');
                $(this).find('#modalImage').attr('src', fotoUrl);
            });

            // Event handler untuk modal lihat feedback
            $('#laporan-table').on('click', '[data-bs-target="#viewFeedbackModal"]', function() {
                const feedback = $(this).data('feedback');
                const approver = $(this).data('approver');
                $('#feedbackContent').text(feedback || 'Tidak ada feedback yang diberikan.');
                $('#feedbackApprover').text('Revisi Diminta Oleh: ' + approver);
            });

            // Konfigurasi Toast (Notifikasi)
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOut'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // --- [IMPLEMENTASI LOCALSTORAGE FILTER] ---

            // 1. Definisikan kunci filter dan selector-nya
            const filterKeys = {
                'laporan_periode': '#filter-periode',
                'laporan_bulan': '#filter-bulan',
                'laporan_nama_siswa': '#filter-nama-siswa',
                'laporan_divisi': '#filter-divisi',
                'laporan_status': '#filter-status'
            };

            // 2. Fungsi untuk menyimpan nilai ke localStorage
            function saveFilter(key, value) {
                if (value) {
                    localStorage.setItem(key, value);
                } else {
                    // Hapus jika nilainya kosong (misal: pilih "Semua Status")
                    localStorage.removeItem(key);
                }
            }

            // 3. Fungsi untuk memuat filter dari localStorage saat halaman dibuka
            function loadFilters() {
                let defaultBulan = true;
                for (const [key, selector] of Object.entries(filterKeys)) {
                    const savedValue = localStorage.getItem(key);
                    // Cek jika selector-nya ada di halaman ini
                    if ($(selector).length && savedValue) {
                        $(selector).val(savedValue);
                        if (key === 'laporan_bulan') {
                            defaultBulan = false; // Tandai jika filter bulan sudah di-set
                        }
                    }
                }
                // Jika filter bulan tidak ada di localStorage, set ke bulan ini
                if (defaultBulan && $('#filter-bulan').length) {
                    $('#filter-bulan').val('{{ date('Y-m') }}');
                }
            }

            // 4. Panggil loadFilters() SEBELUM DataTables diinisialisasi
            loadFilters();

            // --- [AKHIR IMPLEMENTASI] ---


            let debounceTimer;

            const table = $('#laporan-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('admin.laporan.data') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: function(d) {
                        // DataTables akan otomatis mengambil nilai dari input yang sudah diisi oleh loadFilters()
                        const urlParams = new URLSearchParams(window.location.search);
                        d.tim_id = urlParams.get('tim_id');
                        d.filter_user_id = urlParams.get('filter_user_id');
                        d.filter_tanggal = urlParams.get('filter_tanggal');
                        d.bulan = $('#filter-bulan').val();
                        d.periode_id = $('#filter-periode').val();
                        d.nama_siswa = $('#filter-nama-siswa').val();
                        d.divisi_id = $('#filter-divisi').val();
                        d.status = $('#filter-status').val();
                    }
                },
                order: [],
                columns: [ //
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'no-sort'
                    },
                    @if (auth()->user()->group_id != 4 || $isFilteredByTim)
                        {
                            data: 'nama_siswa',
                            name: 'user.name',
                            orderable: false,
                            className: 'no-sort'
                        },
                    @endif {
                        data: 'nama_tim',
                        name: 'tim.divisi.nama_divisi',
                        orderable: false,
                        className: 'no-sort'
                    },
                    {
                        data: 'nama_kegiatan',
                        name: 'jenisKegiatan.nama_kegiatan',
                        orderable: false,
                        className: 'no-sort'
                    },
                    {
                        data: 'deskripsi_singkat',
                        name: 'deskripsi_kegiatan',
                        orderable: false,
                        searchable: false,
                        className: 'no-sort wrap-text'
                    },
                    {
                        data: 'bukti_foto',
                        name: 'bukti_foto',
                        orderable: false,
                        searchable: false,
                        className: 'no-sort text-center',
                        render: function(data, type, row) {
                            if (data) {
                                return `<button type="button" class="btn btn-sm btn-outline-info lihat-foto" data-bs-toggle="modal" data-bs-target="#fotoModal" data-foto="${data}">Lihat Foto</button>`;
                            }
                            return '<span class="text-muted">-</span>';
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        orderable: false,
                        className: 'no-sort'
                    },
                    {
                        data: 'status_laporan_data',
                        name: 'status',
                        orderable: false,
                        searchable: false,
                        className: 'no-sort text-center',
                        render: function(data, type, row) {
                            if (!data) return '';
                            if (data.is_rejected) {
                                const feedbackIcon = ' <i class="fas fa-search-plus"></i>';
                                return `<span class="badge ${data.badge_class} uniform-status-badge" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#viewFeedbackModal" data-feedback="${data.feedback}" data-approver="${data.approver_name}">${data.text}${feedbackIcon}</span>`;
                            }
                            return `<span class="badge ${data.badge_class} uniform-status-badge">${data.text}</span>`;
                        }
                    },
                    {
                        data: 'action', //
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'no-sort text-center'
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
            });

            // --- [MODIFIKASI] Event handler filter untuk menyimpan ke localStorage ---
            $('#filter-bulan, #filter-periode, #filter-divisi, #filter-status').on('change', function() {
                const key = Object.keys(filterKeys).find(k => filterKeys[k] === `#${this.id}`);
                if (key) {
                    saveFilter(key, $(this).val());
                }
                table.ajax.reload();
            });

            $('#filter-nama-siswa').on('keyup', function() {
                const value = $(this).val();
                saveFilter('laporan_nama_siswa', value); // Simpan nilai

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    table.ajax.reload();
                }, 500);
            });

            // --- [PENAMBAHAN] Event handler untuk tombol Reset Filter ---
            $('#reset-filter-btn').on('click', function() {
                // Hapus semua nilai dari localStorage
                for (const key of Object.keys(filterKeys)) {
                    localStorage.removeItem(key);
                }
                
                // Reset semua field filter
                $('#filter-periode').val('');
                $('#filter-nama-siswa').val('');
                $('#filter-divisi').val('');
                $('#filter-status').val('');
                
                // Kembalikan filter bulan ke default (bulan ini) dan simpan
                const defaultBulan = '{{ date('Y-m') }}';
                $('#filter-bulan').val(defaultBulan);
                saveFilter('laporan_bulan', defaultBulan);
                
                // Muat ulang tabel
                table.ajax.reload();
            });


            // Event handler untuk tombol delete
            $('#laporan-table tbody').on('click', '.delete-btn', function() {
                const url = $(this).data('url');
                Swal.fire({
                    title: 'Anda yakin?',
                    text: "Laporan yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                '_token': '{{ csrf_token() }}',
                                '_method': 'DELETE'
                            },
                            success: function(response) {
                                Toast.fire({
                                    icon: 'success',
                                    title: response.success
                                });
                                table.ajax.reload();
                            },
                            error: function(xhr) {
                                Toast.fire({
                                    icon: 'error',
                                    title: 'Gagal menghapus data.'
                                });
                            }
                        });
                    }
                });
            });

            // Event handler untuk tombol reject (modal feedback)
            $('#laporan-table tbody').on('click', '.btn-reject', function() {
                const url = $(this).data('url');
                const modal = $('#feedbackModal');
                const form = $('#feedbackForm');
                form.attr('action', url);
                form.find('textarea[name="feedback"]').val('');
                modal.modal('show');
            });

            // Logika untuk menyimpan state collapse filter
            const filterCollapseElement = document.getElementById('filterCollapse');
            if (filterCollapseElement) {
                if (sessionStorage.getItem('laporanFilterState') === 'true') {
                    new bootstrap.Collapse(filterCollapseElement, {
                        toggle: false
                    }).show();
                }
                filterCollapseElement.addEventListener('show.bs.collapse', () => sessionStorage.setItem(
                    'laporanFilterState', 'true'));
                filterCollapseElement.addEventListener('hide.bs.collapse', () => sessionStorage.setItem(
                    'laporanFilterState', 'false'));
            }

            // Tampilkan notifikasi (Toast) jika ada dari session
            @if (session('success'))
                Toast.fire({
                    icon: 'success',
                    title: '{{ session('success') }}'
                });
            @elseif (session('error')) Toast.fire({
                    icon: 'error',
                    title: '{{ session('error') }}'
                });
            @endif
        });
    </script>
@endsection