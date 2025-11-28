@extends('layout.main')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        .btn-action:focus,
        .btn-action:active {
            outline: none !important;
            box-shadow: none !important;
        }

        .swal2-popup.animate__animated:not(.swal2-toast) {
            animation-duration: 400ms;
        }

        .swal2-toast.animate__animated {
            animation-duration: 450ms;
        }

        #teams-table thead th.no-sort::before,
        #teams-table thead th.no-sort::after {
            display: none !important;
        }

        .collapse-icon {
            transition: transform 0.3s ease;
        }

        a[aria-expanded="true"] .collapse-icon {
            transform: rotate(180deg);
        }

        a[aria-expanded="true"] .collapse-icon {
            transform: rotate(180deg);
        }

        /* === CSS BARU UNTUK MERAPIKAN DETAIL (MAXIMIZE VIEW) === */
        table.dataTable.dtr-inline.collapsed>tbody>tr[role="row"]>td:first-child,
        table.dataTable.dtr-inline.collapsed>tbody>tr[role="row"]>th:first-child {
            position: relative;
            padding-left: 30px;
            /* Beri ruang untuk ikon '+' */
        }

        ul.dtr-details {
            padding-left: 0;
            /* Hapus padding default */
            list-style-type: none;
            margin-bottom: 0;
        }

        ul.dtr-details li {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.4rem 0;
            /* Kurangi padding vertikal */
            border-bottom: 1px solid #f0f0f0;
        }

        ul.dtr-details li:last-child {
            border-bottom: none;
        }

        .dtr-title {
            font-weight: bold;
            flex-shrink: 0;
            /* Mencegah judul menyusut */
            margin-right: 1rem;
        }

        .dtr-data {
            text-align: right;
            /* Ratakan kanan data agar rapi */
            word-break: break-word;
            /* Agar teks panjang tidak merusak layout */
        }

        tr.child td.child {
            padding: 0 !important;
            /* Hapus padding bawaan dari sel kontainer */
        }

        ul.dtr-details {
            width: 100%;
            /* Paksa daftar detail untuk meregang penuh */
            padding: 0.5rem 1rem;
            /* Beri padding di sini agar konten tidak menempel ke tepi */
        }
        
        
    </style>
@endsection

@section('content')
    @if (in_array(auth()->user()->group_id, [1, 2, 4, 3, 5, 6, 7]))
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Tugas</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Tugas</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-start gap-2 mb-3">
            @if (in_array(auth()->user()->group_id, [2, 5]))
                <a href="{{ route('admin.tim.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> Tambah Tim
                </a>
            @endif
            @if (in_array(auth()->user()->group_id, [2, 5]))
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#uploadDailyTaskModal">
                    <i class="fas fa-upload me-1"></i> Upload Task Harian
                </button>
            @endif
        </div>

        <div class="card bg-light border-primary mb-4">
            <div class="card-body">
                <h5 class="card-title text-primary">Task Breakdown Hari Ini
                    ({{ \Carbon\Carbon::now()->format('l, d F Y') }})</h5>
                @if ($todaysTask)
                    @if ($todaysTask->tipe == 'file')
                        <a href="{{ asset('uploads/daily_tasks/' . $todaysTask->task_breakdown) }}" target="_blank"
                            class="btn btn-info">
                            <i class="fas fa-file-alt me-1"></i> Lihat File Task
                        </a>
                    @elseif($todaysTask->tipe == 'teks')
                        <button type="button" class="btn btn-primary view-task-text" data-bs-toggle="modal"
                            data-bs-target="#taskTextModal" data-task-text="{{ $todaysTask->deskripsi_tugas }}">
                            <i class="fas fa-eye me-1"></i> Lihat Rincian Tugas
                        </button>
                    @endif
                @else
                    <p class="text-muted mb-0">Belum ada task breakdown yang diupload untuk hari ini.</p>
                @endif
            </div>
        </div>

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
        <div class="card">
            {{-- Bagian Header yang Bisa Diklik --}}
            <div class="card-header bg-light">
                <a class="text-dark text-decoration-none d-flex justify-content-between align-items-center"
                    data-bs-toggle="collapse" href="#timFilterCollapse" role="button" aria-expanded="false"
                    aria-controls="timFilterCollapse">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i> Filter Data Tugas
                    </h5>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </a>
            </div>

            {{-- Bagian Konten Filter yang Bisa Dilipat --}}
            <div class="collapse" id="timFilterCollapse">
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
                            <label for="filter-ketua" class="form-label">Ketua Tim:</label>
                            <select id="filter-ketua" class="form-select form-select-sm">
                                <option value="">Semua Ketua</option>
                                @foreach ($karyawan as $ketua)
                                    <option value="{{ $ketua->id }}">{{ $ketua->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="filter-divisi" class="form-label">Jenis Tim:</label>
                            <select id="filter-divisi" class="form-select form-select-sm">
                                <option value="">Semua</option>
                                @foreach ($daftar_divisi as $divisi)
                                    <option value="{{ $divisi->id }}">{{ $divisi->nama_divisi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="filter-bulan" class="form-label">Bulan/Tahun:</label>
                            <input type="month" id="filter-bulan" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="filter-status" class="form-label">Status Tugas:</label>
                            <select id="filter-status" class="form-select form-select-sm">
                                <option value="">Semua</option>
                                <option value="belum_selesai">Belum Selesai</option>
                                <option value="tugas_selesai">Tugas Selesai</option>
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

            {{-- Bagian Body Utama untuk Tabel --}}
            <div class="card-body">
            
               <table class="table table-striped" id="teams-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>Ketua Tim</th>
                                <th>Jenis Tim</th>
                                <th>Anggota Tim</th>
                                <th>Dibuat Pada</th>
                                <th>Task Breakdown</th>
                                <th>Laporan Siswa</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
            </div>
        </div>

        @if (in_array(auth()->user()->group_id, [2, 5]))
            <div class="modal fade" id="uploadDailyTaskModal" tabindex="-1" aria-labelledby="uploadDailyTaskModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="uploadDailyTaskModalLabel">Upload Task Breakdown Harian</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <form action="{{ route('admin.task.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="applicable_date" class="form-label">Tanggal Berlaku</label>
                                    <input type="date" class="form-control" id="applicable_date"
                                        name="applicable_date" value="{{ now()->toDateString() }}" required>
                                </div>

                                <input type="hidden" name="tipe" value="teks">

                                <ul class="nav nav-tabs nav-fill" id="taskTypeTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="text-input-tab" data-bs-toggle="tab"
                                            data-bs-target="#text-input" type="button" role="tab"
                                            aria-controls="text-input" aria-selected="true">Input Teks</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="file-upload-tab" data-bs-toggle="tab"
                                            data-bs-target="#file-upload" type="button" role="tab"
                                            aria-controls="file-upload" aria-selected="false">Upload File</button>
                                    </li>
                                </ul>

                                <div class="tab-content border border-top-0 p-3" id="taskTypeTabContent">
                                    <div class="tab-pane fade show active" id="text-input" role="tabpanel"
                                        aria-labelledby="text-input-tab">
                                        <label for="deskripsi_tugas" class="form-label">Deskripsi Tugas</label>
                                        <textarea class="form-control" name="deskripsi_tugas" id="deskripsi_tugas" rows="10"
                                            placeholder="Salin dan tempel atau ketik rincian tugas di sini..."></textarea>
                                    </div>
                                    <div class="tab-pane fade" id="file-upload" role="tabpanel"
                                        aria-labelledby="file-upload-tab">
                                        <label for="task_file" class="form-label">Pilih File</label>
                                        <input class="form-control" type="file" id="task_file" name="task_file"
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                        <div class="form-text">File yang diizinkan: PDF, Word, JPG, PNG. Ukuran maksimal
                                            5MB.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Simpan Tugas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection
@section('js')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Logika untuk menyimpan state collapse filter
            const filterCollapseElement = document.getElementById('timFilterCollapse');
            const filterStateKey = 'timFilterState';
            if (sessionStorage.getItem(filterStateKey) === 'true') {
                new bootstrap.Collapse(filterCollapseElement, {
                    toggle: false
                }).show();
            }
            filterCollapseElement.addEventListener('show.bs.collapse', () => sessionStorage.setItem(
                filterStateKey, 'true'));
            filterCollapseElement.addEventListener('hide.bs.collapse', () => sessionStorage.setItem(
                filterStateKey, 'false'));


            // --- [IMPLEMENTASI LOCALSTORAGE FILTER] ---

            // 1. Definisikan kunci filter dan selector-nya
            const filterKeys = {
                'tim_periode': '#filter-periode',
                'tim_ketua': '#filter-ketua',
                'tim_divisi': '#filter-divisi',
                'tim_bulan': '#filter-bulan',
                'tim_status': '#filter-status'
            };

            // 2. Fungsi untuk menyimpan nilai ke localStorage
            function saveFilter(key, value) {
                if (value) {
                    localStorage.setItem(key, value);
                } else {
                    localStorage.removeItem(key); // Hapus jika nilainya kosong
                }
            }

            // 3. Fungsi untuk memuat filter dari localStorage
            function loadFilters() {
                // [PENTING] Cek apakah ada filter bulan/periode di localStorage
                const bulanTersimpan = localStorage.getItem('tim_bulan');
                const periodeTersimpan = localStorage.getItem('tim_periode');

                // Jika TIDAK ADA filter bulan/periode yang tersimpan, jangan muat apa-apa.
                // Ini akan memicu filter "default hari ini" di controller.
                if (!bulanTersimpan && !periodeTersimpan) {
                    return; // Biarkan filter kosong
                }

                // Jika ADA, baru muat semua filter yang tersimpan
                for (const [key, selector] of Object.entries(filterKeys)) {
                    const savedValue = localStorage.getItem(key);
                    if ($(selector).length && savedValue) {
                        $(selector).val(savedValue);
                    }
                }
            }

            // 4. Panggil loadFilters() SEBELUM DataTables diinisialisasi
            loadFilters();

            // --- [AKHIR IMPLEMENTASI] ---


            const teamsDataTable = $('#teams-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true, //
                ajax: {
                    url: '{{ route('admin.tim.data') }}', //
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' //
                    },
                    data: function(d) {
                        // DataTables akan otomatis mengambil nilai dari input yang sudah diisi oleh loadFilters()
                        d.ketua_id = $('#filter-ketua').val();
                        d.divisi_id = $('#filter-divisi').val();
                        d.bulan = $('#filter-bulan').val();
                        d.status = $('#filter-status').val();
                        d.periode_id = $('#filter-periode').val(); //
                    },
                    error: function(xhr, error, code) {
                        console.log(xhr, error, code);
                        alert('Gagal memuat data. Cek console (F12) untuk detail error.');
                    }
                },
                order: [],
                columns: [ //
                    {
                        data: 'ketua_names',
                        name: 'ketua.name',
                        orderable: false,
                        className: 'no-sort',
                        render: function(data, type, row) { //
                            if (!Array.isArray(data) || data.length === 0)
                                return '<span class="text-muted fst-italic">-</span>';
                            const limit = 2;
                            const popoverContent = data.join('<br>');
                            let displayHtml;
                            if (data.length > limit) {
                                const displayedNames = data.slice(0, limit).join(', ');
                                const remainingCount = data.length - limit;
                                displayHtml =
                                    `${displayedNames} <span class="badge bg-primary ms-1" style="cursor: help;">+${remainingCount} lagi</span>`;
                            } else {
                                displayHtml = data.join(', ');
                            }
                            return `<div data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="top" title="Daftar Ketua Tim" data-bs-html="true" data-bs-content="${popoverContent}">${displayHtml}</div>`;
                        }
                    },
                    {
                        data: 'divisi_name',
                        name: 'divisi.nama_divisi',
                        orderable: false,
                        className: 'no-sort' //
                    },
                    {
                        data: 'anggota_names',
                        name: 'anggota_names',
                        orderable: false,
                        searchable: false,
                        className: 'no-sort',
                        render: function(data, type, row) { //
                            if (!Array.isArray(data) || data.length === 0) {
                                return '<span class="text-muted fst-italic">-</span>';
                            }
                            const limit = 2;
                            const popoverContent = data.join('<br>');
                            let displayHtml;
                            if (data.length > limit) {
                                const displayedNames = data.slice(0, limit).join(', ');
                                const remainingCount = data.length - limit;
                                displayHtml =
                                    `${displayedNames} <span class="badge bg-secondary ms-1" style="cursor: help;">+${remainingCount} lagi</span>`;
                            } else {
                                displayHtml = data.join(', ');
                            }
                            return `<div data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="top" title="Daftar Anggota Tim" data-bs-html="true" data-bs-content="${popoverContent}">${displayHtml}</div>`;
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        orderable: false,
                        className: 'no-sort',
                        render: function(data) { //
                            if (!data) return '';
                            const date = new Date(data);
                            if (isNaN(date)) return 'Tanggal tidak valid';
                            return date.toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            }) + ' ' + date.toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                        }
                    },
                    {
                        data: 'task_breakdown_data',
                        name: 'task_breakdown_data',
                        orderable: false,
                        searchable: false,
                        className: 'no-sort text-center',
                        render: function(data, type, row) { //
                            if (!data) {
                                return `<span class="text-muted">-</span>`;
                            }
                            if (data.tipe === 'file') {
                                return `<a href="${data.content}" target="_blank" class="btn btn-sm btn-outline-info" title="Lihat File"><i class="fas fa-file-alt"></i></a>`;
                            }
                            if (data.tipe === 'teks') {
                                return `<button type="button" class="btn btn-sm btn-outline-primary view-task-text" data-bs-toggle="modal" data-bs-target="#taskTextModal" data-task-text="${data.content}" title="Lihat Teks"><i class="fas fa-eye"></i></button>`;
                            }
                            return '<span class="text-muted fst-italic">-</span>';
                        }
                    },
                    {
                        data: 'id',
                        name: 'laporan',
                        orderable: false,
                        searchable: false,
                        className: 'no-sort',
                        render: (data) =>
                            `<a href="{{ url('admin/laporan') }}?tim_id=${data}" class="btn btn-info btn-sm" title="Lihat Laporan"><i class="fas fa-eye"></i> Detail</a>` //
                    },
                    {
                        data: 'status_data',
                        name: 'status_approval',
                        orderable: false,
                        searchable: false,
                        className: 'no-sort',
                        render: function(data, type, row) { //
                            // ... (render function tidak diubah)
                            return `<span class="badge ${data.badge_class}">${data.text}</span>`;
                        }
                    },
                    {
                        data: 'action_data',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: "150px",
                        className: "text-center no-sort",
                        render: function(data, type, row) { //
                            // ... (render function tidak diubah)
                            if (data.status_approval === 'tugas_selesai') {
                                return '<span class="text-muted fst-italic">Terkunci</span>';
                            }
                            let buttonsHtml = '';
                            const currentUser = {
                                id: {{ auth()->user()->id }},
                                group_id: {{ auth()->user()->group_id }}
                            };
                            const isAdmin = [1, 2].includes(currentUser.group_id);
                            const isTeamLeader = currentUser.group_id === 5 && Array.isArray(data
                                .ketua_ids) && data.ketua_ids.includes(currentUser.id);
                            const csrfField = '{{ csrf_field() }}';
                            if (isAdmin || isTeamLeader) {
                                buttonsHtml +=
                                    `<a href="${data.edit_url}" class="btn btn-warning btn-action btn-sm mx-1" title="Edit"><i class="fas fa-edit"></i></a>`;
                            }
                            if (isAdmin || isTeamLeader) {
                                const deleteMethodField = '{{ method_field('DELETE') }}';
                                const ketuaNames = row.ketua_names.join(', ');
                                buttonsHtml +=
                                    `<form action="${data.destroy_url}" method="POST" class="d-inline form-hapus">${csrfField}${deleteMethodField}<button type="button" class="btn btn-danger btn-action btn-sm mx-1 action-hapus" data-ketua="${ketuaNames}" title="Hapus"><i class="fas fa-trash-alt"></i></button></form>`;
                            }
                            if (buttonsHtml) {
                                return `<div class="row-action">${buttonsHtml}</div>`;
                            }
                            return '<span class="text-muted fst-italic">-</span>';
                        }
                    }
                ],
                // ... (createdRow dan language tidak diubah)
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                drawCallback: function() { //
                    $('.popover').remove();
                    const popoverTriggerList = [].slice.call(document.querySelectorAll(
                        '[data-bs-toggle="popover"]'));
                    popoverTriggerList.map(function(popoverTriggerEl) {
                        return new bootstrap.Popover(popoverTriggerEl);
                    });
                }
            });

            // --- [MODIFIKASI] Event handler filter untuk menyimpan ke localStorage ---
            $('#filter-ketua, #filter-divisi, #filter-bulan, #filter-status, #filter-periode').on('change', function() {
                const key = Object.keys(filterKeys).find(k => filterKeys[k] === `#${this.id}`);
                if (key) {
                    saveFilter(key, $(this).val());
                }
                teamsDataTable.ajax.reload();
            });

            // --- [PENAMBAHAN] Event handler untuk tombol Reset Filter ---
            $('#reset-filter-btn').on('click', function() {
                // Hapus semua nilai dari localStorage
                for (const key of Object.keys(filterKeys)) {
                    localStorage.removeItem(key);
                }
                
                // Reset semua field filter ke nilai kosong
                $('#filter-periode').val('');
                $('#filter-ketua').val('');
                $('#filter-divisi').val('');
                $('#filter-bulan').val('');
                $('#filter-status').val('');
                
                // Muat ulang tabel. Controller akan otomatis kembali ke "data hari ini"
                teamsDataTable.ajax.reload();
            });


            // --- (Sisa kode event handler di bawah ini tidak diubah) ---
            const tableBody = $('#teams-table tbody');
            tableBody.on('click', '.action-hapus', function() { //
                // ... (fungsi delete)
            });
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) { //
                // ... (fungsi ganti tab modal)
            });
            $(document).on('click', '.view-task-text', function() { //
                // ... (fungsi lihat task text)
            });
            
            // HAPUS event handler '.btn-reject, .btn-undo-finish' DAN '#viewFeedbackModal'
            // KARENA SUDAH TIDAK RELEVAN DI MENU TUGAS
            
            const Toast = Swal.mixin({ //
                // ... (konfigurasi Toast)
            });
            @if (session('success')) //
                Toast.fire({
                    icon: 'success',
                    title: '{{ session('success') }}'
                });
            @elseif (session('error')) Toast.fire({
                    icon: 'error',
                    title: '{{ session('error') }}'
                });
            @elseif ($errors->any()) Toast.fire({
                    icon: 'error',
                    title: '{{ $errors->first() }}'
                });
            @endif
        });
    </script>
@endsection