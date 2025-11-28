@extends('layout.main')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

        #export-options-container.hidden {
            display: none;
        }

        #presensi-table thead th.no-sort::before,
        #presensi-table thead th.no-sort::after {
            display: none !important;
        }

        .collapse-icon {
            transition: transform 0.3s ease;
        }

        a[aria-expanded="true"] .collapse-icon {
            transform: rotate(180deg);
        }

        @media (max-width: 767px) {

            table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control {
                position: relative !important;
                padding-left: 35px !important;
                /* Beri ruang lebih untuk ikon */
            }

            table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before {
                height: 16px !important;
                width: 16px !important;
                border-radius: 50% !important;
                border: 2px solid #556ee6 !important;
                background-color: #556ee6 !important;
                top: 50% !important;
                left: 8px !important;
                line-height: 12px !important;
                font-weight: bold !important;
                box-shadow: none !important;
            }

            table.dataTable.dtr-inline.collapsed>tbody>tr.parent>td.dtr-control:before {
                background-color: #f1b44c !important;
                /* Warna oranye saat terbuka */
                border-color: #f1b44c !important;
            }


            /* 2. Perbaikan Layout Card Detail */
            .child-row-container {
                width: 100%;
                margin: 0;
                padding: 0;
                background-color: #fff;
                border-top: 3px solid #556ee6;
            }

            .child-row-header {
                background-color: #f8f9fa;
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #e9ecef;
            }

            .student-name {
                font-size: 1.05rem;
                font-weight: 600;
                color: #343a40;
            }

            .student-school {
                font-size: 0.85rem;
                color: #6c757d;
            }

            .child-row-body {
                padding: 1rem;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1.25rem 0.75rem;
                /* Jarak vertikal lebih besar */
            }

            .detail-item,
            .detail-item-full {
                display: flex;
                flex-direction: column;
            }

            .detail-item-full {
                grid-column: 1 / -1;
            }

            .detail-label {
                font-weight: 600;
                color: #74788d;
                margin-bottom: 0.3rem;
                font-size: 0.8rem;
                text-transform: uppercase;
                display: flex;
                align-items: center;
                gap: 0.4rem;
            }

            .detail-label i.fa-fw {
                color: #a6a9be;
            }

            /* 3. Perbaikan Teks Keterangan yang Keluar */
            .detail-data {
                color: #495057;
                font-weight: 500;
                word-break: break-word !important;
                /* Paksa pindah baris */
                white-space: normal !important;
                /* Pastikan tidak ada no-wrap */
                overflow-wrap: break-word !important;
            }

            /* 4. Perbaikan Layout Tombol Tindakan */
            .actions-wrapper {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                /* Izinkan tombol turun jika layar sangat sempit */
                gap: 0.5rem;
            }
        }
    </style>
@endsection

@section('content')
    {{-- Judul Halaman --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Data Presensi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Presensi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Kartu Status Presensi untuk Siswa --}}
    @if (auth()->user()->group_id == 4)
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Status Presensi Hari Ini</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-0 py-2">{{ $statusPresensi['message'] }}</div>
                <div class="mt-3 d-flex gap-2">
                    @if ($statusPresensi['can_presensi'])
                        <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                            data-bs-target="#presensiModal">
                            <i class="cil-camera me-1"></i> Presensi Kamera
                        </button>
                    @endif

                    <button class="btn btn-warning d-flex align-items-center" data-bs-toggle="modal"
                        data-bs-target="#izinModal">
                        <i class="cil-envelope-closed me-1"></i> Ajukan Izin/Sakit
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- KOTAK UTAMA UNTUK FILTER DAN TABEL --}}
    <div class="card">
        @if (in_array(auth()->user()->group_id, [1, 2, 3, 5, 6, 7]))
            {{-- [MODIFIKASI] HEADER FILTER YANG BISA DIKLIK --}}
            <div class="card-header bg-light">
                <a class="text-dark text-decoration-none d-flex justify-content-between align-items-center"
                    data-bs-toggle="collapse" href="#filterCollapse" role="button" aria-expanded="false"
                    aria-controls="filterCollapse">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i> Filter Data Presensi
                    </h5>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </a>
            </div>

            {{-- [MODIFIKASI] KONTEN FILTER YANG BISA DISEMBUNYIKAN --}}
            <div class="collapse" id="filterCollapse">
                <div class="card-body border-bottom">
                    {{-- Baris ini hanya berisi input filter --}}
                    <div class="row">
                        <div class="col-md-2">
                            <label for="filter_periode" class="form-label">Filter Periode</label>
                            <select id="filter_periode" class="form-select">
                                <option value="">Semua Periode</option>
                                @foreach ($periodePkls as $periode)
                                    <option value="{{ $periode->id }}">
                                        {{ \Carbon\Carbon::parse($periode->awal_periode)->format('d M Y') }} -
                                        {{ \Carbon\Carbon::parse($periode->akhir_periode)->format('d M Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter-bulan" class="form-label">Filter Bulan & Tahun</label>
                            <input type="month" id="filter-bulan" class="form-control"
                                value="{{ request('filter_bulan', date('Y-m')) }}">
                        </div>
                        <div class="col-md-2">
                            <label for="filter-sekolah" class="form-label">Filter Sekolah</label>
                            <select id="filter-sekolah" class="form-select">
                                <option value="">Semua Sekolah</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter-approval" class="form-label">Status Approval</label>
                            <select id="filter-approval" class="form-select">
                                <option value="" {{ request('status_approval') == '' ? 'selected' : '' }}>Semua
                                </option>
                                <option value="pending_all"
                                    {{ request('status_approval') == 'pending_all' ? 'selected' : '' }}>
                                    Menunggu Persetujuan</option>
                                <option value="approved" {{ request('status_approval') == 'approved' ? 'selected' : '' }}>
                                    Disetujui
                                </option>
                                <option value="rejected" {{ request('status_approval') == 'rejected' ? 'selected' : '' }}>
                                    Ditolak
                                </option>
                                <option value="none" {{ request('status_approval') == 'none' ? 'selected' : '' }}>Tanpa
                                    Approval
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="custom-search" class="form-label">Cari Siswa</label>
                            <input type="text" id="custom-search" class="form-control" placeholder="Nama siswa..."
                                value="{{ request('search.value') }}">
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- [MODIFIKASI] BAGIAN CARD BODY UTAMA UNTUK TOMBOL DAN TABEL --}}
        <div class="card-body">
            @if (in_array(auth()->user()->group_id, [1, 2, 5]))
                <div class="mb-3">
                    <div class="btn-group">

                        @if (in_array(auth()->user()->group_id, [1, 2]))
                            <button type="button" id="export-btn" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#exportPdfModal">
                                <i class="fa fa-file-pdf me-1"></i> Export PDF
                            </button>
                        @endif

                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#alpaBatchModal">
                            <i class="fa fa-user-times me-1"></i> Buat Presensi Alpa
                        </button>
                    </div>
                </div>
            @endif

            {{-- TABEL PRESENSI --}}
            <table id="presensi-table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
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

    {{-- Modals --}}
    <x-modals.camera id="presensiModal" :status-presensi="$statusPresensi" />
    <x-modals.form_izinsakit id="izinModal" />
    <x-modals.alpa_change id="alpaChangeModal" />
    <x-modals.image_viewer id="imageViewerModal" />
    <x-modals.export_pdf id="exportPdfModal" />
    <div class="modal fade" id="alpaBatchModal" tabindex="-1" aria-labelledby="alpaBatchModalLabel"
        aria-hidden="true">
        {{-- Konten modal alpa batch tidak berubah --}}
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alpaBatchModalLabel">Buat Presensi Alpa Massal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-batch-alpa">
                        <div class="alert alert-info small">
                            Fitur ini akan membuat presensi <strong>Alpa</strong> untuk sesi Pagi & Sore pada siswa yang
                            dipilih. Data yang sudah ada (Hadir, Izin, Sakit) tidak akan ditimpa.
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_alpa" class="form-label">Pilih Tanggal</label>
                            <input type="date" id="tanggal_alpa" name="tanggal_alpa" class="form-control"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="target_type" class="form-label">Target Siswa</label>
                            <select name="target_type" id="target_type" class="form-select">
                                <option value="all_missing" selected>Semua Siswa Tanpa Presensi</option>
                                <option value="specific">Pilih Siswa Tertentu</option>
                            </select>
                        </div>
                        <div class="mb-3" id="specific-students-container" style="display:none;">
                            <label for="user_ids" class="form-label">Pilih Nama Siswa</label>
                            <select name="user_ids[]" id="user_ids" class="form-control" multiple="multiple"></select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="submit-batch-alpa" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Buat Alpa
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="d-none">
        <form id="form-destroy" method="post">@csrf @method('DELETE')</form>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let presensiTable;

        $(document).ready(function() {
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
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
            const urlParams = new URLSearchParams(window.location.search);
            const filterApprovalFromUrl = urlParams.get('filter_approval');

            if (filterApprovalFromUrl) {
                $('#filter-approval').val(filterApprovalFromUrl);
            }

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
                    // ... (bagian ajax tidak berubah)
                    url: "{{ route('presensi.data.unified') }}",
                    type: "POST",
                    data: function(d) {
                        d.filter_bulan = $('#filter-bulan').val();
                        d.filter_sekolah = $('#filter-sekolah').val();
                        d.filter_approval = $('#filter-approval').val();
                        d.filter_periode = $('#filter_periode').val();
                        d.search = {
                            value: $('#custom-search').val()
                        };
                    }
                },
                columns: [
                    // ... (bagian columns tidak berubah)
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'nama',
                        name: 'user.name',
                        responsivePriority: 10000
                    },
                    {
                        data: 'sekolah',
                        name: 'user.sekolah.nama',
                        responsivePriority: 10001
                    },
                    {
                        data: 'tanggal',
                        name: 'presensi_at',
                        responsivePriority: 2
                    },
                    {
                        data: 'sesi_badge',
                        name: 'sesi',
                        responsivePriority: 3
                    },
                    {
                        data: 'jam',
                        name: 'presensi_at',
                        responsivePriority: 10002
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        responsivePriority: 1
                    },
                    {
                        data: 'approval_badge',
                        name: 'approval_status',
                        responsivePriority: 10003
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
                        data: 'action',
                        name: 'action',
                        searchable: false,
                        orderable: false,
                        className: 'text-center'
                    }
                ],
                order: [
                    [7, 'asc'],
                    [3, 'desc'],
                    [4, 'desc']
                ],
                responsive: {
                    details: {
                        renderer: function(api, rowIdx, columns) {
                            let data = $.map(columns, (col) => col.hidden ? {
                                title: $(api.column(col.columnIndex).header()).text(),
                                data: col.data
                            } : '').filter(val => val !== '');
                            if (data.length === 0) return false;

                            const nama = data.find(item => item.title === 'Nama')?.data || '-';
                            const sekolah = data.find(item => item.title === 'Sekolah')?.data || '-';

                            const buktiData = data.find(item => item.title === 'Bukti')?.data || '';
                            const aksiData = data.find(item => item.title === 'Aksi')?.data || '';

                            const details = data.filter(item => !['Nama', 'Sekolah', 'Bukti', 'Aksi']
                                .includes(item.title));

                            let html = `<div class="child-row-container">
                                <div class="child-row-header">
                                    <div class="student-name">${nama}</div>
                                    <div class="student-school">${sekolah}</div>
                                </div>
                                <div class="child-row-body">`;

                            details.forEach(item => {
                                let icon = '';
                                switch (item.title) {
                                    case 'Jam':
                                        icon = '<i class="far fa-clock fa-fw"></i>';
                                        break;
                                    case 'Approval':
                                        icon = '<i class="far fa-check-square fa-fw"></i>';
                                        break;
                                    case 'Keterangan':
                                        icon = '<i class="far fa-comment-dots fa-fw"></i>';
                                        break;
                                    default:
                                        icon = '<i class="fas fa-info-circle fa-fw"></i>';
                                        break;
                                }
                                let itemClass = (item.title === 'Keterangan') ?
                                    'detail-item-full' : 'detail-item';
                                html += `<div class="${itemClass}">
                                <div class="detail-label">${icon} ${item.title}</div>
                                <div class="detail-data">${item.data || '-'}</div>
                            </div>`;
                            });

                            if (buktiData || aksiData) {
                                html += `<div class="detail-item-full">
                                 <div class="detail-label"><i class="fas fa-cogs fa-fw"></i> Tindakan</div>
                                 <div class="detail-data actions-wrapper">
                                     ${buktiData} ${aksiData}
                                 </div>
                             </div>`;
                            }

                            html += '</div></div>';
                            return $(html);
                        }
                    }
                },
            });

            $('#imageViewerModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var imageUrl = button.data('image-url');
                var modal = $(this);
                modal.find('.viewer-image').attr('src', imageUrl);
            });

            function loadSekolahFilter() {
                fetch("{{ route('presensi.sekolah.list') }}").then(response => response.json())
                    .then(data => {
                        const sekolahSelect = document.getElementById('filter-sekolah');
                        data.forEach(s => {
                            sekolahSelect.add(new Option(s.nama, s.id));
                        });
                    }).catch(error => console.error('Error loading school list:', error));
            }

            // ==========================================================
            // [MODIFIKASI] Fungsi ini diubah sesuai Langkah 3
            // ==========================================================
            function processApproval(presensiId, action) {
                const actionText = action === 'approve' ? 'menyetujui' : 'menolak';
                Swal.fire({
                    title: `Anda yakin ingin ${actionText}?`,
                    // [PENAMBAHAN] Baris ini ditambahkan untuk memberi tahu admin
                    text: "Semua sesi terkait (pagi/sore) dari pengajuan ini akan ikut diproses.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: `Ya, ${actionText}!`,
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(`{{ url('presensi/approval') }}/${presensiId}`, {
                                action: action
                            })
                            .done(response => {
                                Swal.fire('Berhasil!', response.message ||
                                    'Tindakan berhasil diproses.', 'success');
                                presensiTable.ajax.reload(null, false);
                            })
                            .fail(xhr => {
                                Swal.fire('Gagal!', xhr.responseJSON?.message ||
                                    'Terjadi kesalahan.',
                                    'error');
                            });
                    }
                });
            }

            function deletePresensi(presensiId) {
                Swal.fire({
                    icon: "warning",
                    title: "Anda Yakin?",
                    text: "Data ini akan dihapus permanen.",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Hapus!",
                    cancelButtonText: "Batal",
                }).then(result => {
                    if (result.value) {
                        const form = document.getElementById('form-destroy');
                        form.action = `{{ url('presensi') }}/${presensiId}`;
                        form.submit();
                    }
                });
            }
            // ==========================================================
            // AKHIR MODIFIKASI
            // ==========================================================


            $('#filter-bulan, #filter-sekolah, #filter-approval, #filter_periode').on('change', function() {
                presensiTable.ajax.reload();
            });

            let searchTimeout;
            $('#custom-search').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    presensiTable.ajax.reload();
                }, 500);
            });

            // Kode ini sudah benar dan tidak perlu diubah.
            // Kode ini memanggil `processApproval` dan `deletePresensi`
            $('#presensi-table tbody').on('click', '.action-btn', function() {
                const action = $(this).data('action');
                const id = $(this).data('id');
                if (action === 'approve' || action === 'reject') {
                    processApproval(id, action);
                } else if (action === 'delete') {
                    deletePresensi(id);
                }
            });

            const filterCollapseElement = document.getElementById('filterCollapse');
            const filterStateKey = 'presensiFilterState';

            if (sessionStorage.getItem(filterStateKey) === 'true') {
                const bsCollapse = new bootstrap.Collapse(filterCollapseElement, {
                    toggle: false
                });
                bsCollapse.show();
            }

            if (filterCollapseElement) {
                filterCollapseElement.addEventListener('show.bs.collapse', function() {
                    sessionStorage.setItem(filterStateKey, 'true');
                });

                filterCollapseElement.addEventListener('hide.bs.collapse', function() {
                    sessionStorage.setItem(filterStateKey, 'false');
                });
            }

            @if (in_array(auth()->user()->group_id, [1, 2, 5, 6, 7]))
                loadSekolahFilter();
            @endif

            @if (in_array(auth()->user()->group_id, [1, 2, 5]))
                initializeBatchAlpaModal();
            @endif
        });

        function initializeBatchAlpaModal() {
            const $modal = $('#alpaBatchModal');
            const $selectTarget = $('#target_type');
            const $specificContainer = $('#specific-students-container');
            const $selectUsers = $('#user_ids');
            const $submitBtn = $('#submit-batch-alpa');
            const $spinner = $submitBtn.find('.spinner-border');

            $selectUsers.select2({
                placeholder: 'Cari dan pilih siswa...',
                dropdownParent: $modal,
                ajax: {
                    url: "{{ route('presensi.siswa.list') }}",
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });

            $selectTarget.on('change', function() {
                $specificContainer.toggle(this.value === 'specific');
            }).trigger('change');

            $submitBtn.on('click', function() {
                const formData = new FormData($('#form-batch-alpa')[0]);
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                $submitBtn.prop('disabled', true);
                $spinner.removeClass('d-none');

                $.ajax({
                    url: "{{ route('presensi.batch.alpa') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $modal.modal('hide');
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        presensiTable.ajax.reload();
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON;
                        Swal.fire('Gagal!', error.message || 'Terjadi kesalahan.', 'error');
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false);
                        $spinner.addClass('d-none');
                    }
                });
            });

            $modal.on('hidden.bs.modal', function() {
                $('#form-batch-alpa')[0].reset();
                $selectTarget.trigger('change');
                $selectUsers.val(null).trigger('change');
            });
        }
    </script>

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
