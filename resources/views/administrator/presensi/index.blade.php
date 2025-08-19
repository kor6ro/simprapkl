@extends('layout.main')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* === Styles utama === */
        .camera-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

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
            object-position: center;
        }

        .camera-overlay {
            position: absolute;
            top: 10px;
            left: 10px;
            color: white;
            background: rgba(0, 0, 0, 0.7);
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 12px;
        }

        .preview-image {
            width: 100%;
            max-width: 400px;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-camera {
            min-width: 120px;
        }

        .camera-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-bottom: 15px;
        }

        .status-indicator {
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 500;
        }

        .status-ready {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .presensi-status-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .session-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }

        .filter-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .filter-toggle-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .filter-toggle-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .filter-controls {
            display: none;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }

        .filter-controls.show {
            display: block;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 10px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .approval-pending-badge {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }

            100% {
                opacity: 1;
            }
        }

        .table-actions {
            white-space: nowrap;
        }

        .btn-group-sm>.btn,
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        /* Custom DataTables Header */
        .dataTables_wrapper .dataTables_filter {
            float: right;
            text-align: right;
            margin-bottom: 10px;
        }

        .dataTables_wrapper .dataTables_length {
            float: left;
        }

        .custom-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .search-filter-container {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .custom-search {
            min-width: 250px;
        }

        @media (max-width: 768px) {
            .custom-table-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-filter-container {
                justify-content: center;
            }

            .custom-search {
                min-width: 200px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">📱 Presensi Digital - Admin Dashboard</h4>
                <div class="page-title-right">
                    <button class="btn btn-danger btn-sm" onclick="generateAlpa()"
                        title="Generate Alpa untuk siswa yang belum presensi hari ini">
                        <i class="fas fa-exclamation-triangle"></i> Generate Alpa
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Cards --}}
    @if (Auth::user()->group_id == 2)
        <div class="stats-card">
            <div class="row">
                <div class="col-md-2">
                    <div class="stat-item">
                        <span class="stat-number" id="stat-hadir">-</span>
                        <span class="stat-label">Hadir Hari Ini</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-item">
                        <span class="stat-number" id="stat-terlambat">-</span>
                        <span class="stat-label">Terlambat</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-item">
                        <span class="stat-number" id="stat-izin">-</span>
                        <span class="stat-label">Izin</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-item">
                        <span class="stat-number" id="stat-sakit">-</span>
                        <span class="stat-label">Sakit</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-item">
                        <span class="stat-number" id="stat-alpa">-</span>
                        <span class="stat-label">Alpa</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-item">
                        <span class="stat-number approval-pending-badge" id="stat-pending">
                            {{ \App\Models\Presensi::where('approval_status', 'pending')->count() }}
                        </span>
                        <span class="stat-label">Pending Approval</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Status Presensi Pribadi (untuk siswa yang login sebagai admin) --}}
    @if (Auth::user()->group_id == 4)
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">📋 Status Presensi Hari Ini</h5>
            </div>
            <div class="card-body">
                <div class="presensi-status-card">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Sesi Pagi</h6>
                            @if ($statusPresensi['pagi_status'])
                                <span class="badge bg-success session-badge">✓ {{ $statusPresensi['pagi_status'] }}</span>
                                @if ($statusPresensi['pagi_jam'])
                                    <br><small class="text-muted">Jam: {{ $statusPresensi['pagi_jam'] }}</small>
                                @endif
                            @else
                                <span class="badge bg-secondary session-badge">Belum Presensi</span>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <h6>Sesi Sore</h6>
                            @if ($statusPresensi['sore_status'])
                                <span class="badge bg-success session-badge">✓ {{ $statusPresensi['sore_status'] }}</span>
                                @if ($statusPresensi['sore_jam'])
                                    <br><small class="text-muted">Jam: {{ $statusPresensi['sore_jam'] }}</small>
                                @endif
                            @else
                                <span class="badge bg-secondary session-badge">Belum Presensi</span>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <h6>Status Saat Ini</h6>
                            <div class="alert alert-info mb-0 py-2">{{ $statusPresensi['message'] }}</div>
                            @if ($statusPresensi['current_session'])
                                <small class="text-muted">Sesi: {{ ucfirst($statusPresensi['current_session']) }}</small>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    @if ($statusPresensi['can_presensi'])
                        <button class="btn btn-primary btn-sm" onclick="showPresensiModal()">📷 Presensi</button>
                    @endif
                    <button class="btn btn-warning btn-sm" onclick="showIzinModal()">🏥 Izin/Sakit</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Single Unified Table dengan Filter Terintegrasi --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">📊 Data Presensi Lengkap</h5>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-success" onclick="exportRekapExcel()">
                    <i class="fas fa-file-excel"></i> Export Rekap Excel
                </button>
                <button class="btn btn-outline-danger" onclick="exportRekapPDF()">
                    <i class="fas fa-file-pdf"></i> Export Rekap PDF
                </button>
            </div>
        </div>

        <div class="card-body">
            {{-- Custom Header dengan Search dan Filter --}}
            <div class="custom-table-header">
                <div class="search-filter-container">
                    <div class="input-group custom-search">
                        <input type="text" class="form-control" id="custom-search"
                            placeholder="Cari nama, sekolah, status...">
                        <button class="btn btn-outline-secondary" type="button" onclick="performSearch()"><i
                                class="fas fa-search"></i></button>
                    </div>
                    <button class="filter-toggle-btn" id="toggle-filters" onclick="toggleFilters()">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0">Tampilkan:</label>
                    <select id="custom-length" class="form-select" style="width: auto;">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-muted">entri</span>
                </div>
            </div>

            {{-- Filter Controls (Hidden by default) --}}
            <div class="filter-controls" id="filter-controls">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Tanggal</label>
                        <select id="filter-tanggal" class="form-select form-select-sm">
                            <option value="">Pilih Rentang</option>
                            <option value="today">Hari Ini</option>
                            <option value="yesterday">Kemarin</option>
                            <option value="week">7 Hari Terakhir</option>
                            <option value="month">30 Hari Terakhir</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Bulan/Tahun</label>
                        <input type="month" id="filter-bulan" class="form-control form-control-sm">
                    </div>
                    {{-- ... (Filter-filter lainnya tetap sama) ... --}}
                    <div class="col-md-2"><label class="form-label">Status</label><select id="filter-status"
                            class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="Tepat Waktu">Tepat Waktu</option>
                            <option value="Terlambat">Terlambat</option>
                            <option value="Sangat Terlambat">Sangat Terlambat</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Alpa">Alpa</option>
                        </select></div>
                    <div class="col-md-2"><label class="form-label">Sesi</label><select id="filter-sesi"
                            class="form-select form-select-sm">
                            <option value="">Semua Sesi</option>
                            <option value="pagi">Pagi</option>
                            <option value="sore">Sore</option>
                        </select></div>
                    <div class="col-md-2"><label class="form-label">Approval</label><select id="filter-approval"
                            class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                            <option value="none">Tidak Ada</option>
                        </select></div>
                    <div class="col-md-2"><label class="form-label">Sekolah</label><select id="filter-sekolah"
                            class="form-select form-select-sm">
                            <option value="">Semua Sekolah</option>
                        </select></div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm" onclick="applyFilters()"><i class="fas fa-check"></i>
                                Terapkan</button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters()"><i
                                    class="fas fa-times"></i> Reset</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DataTable --}}
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped table-hover" id="table-unified" style="width:100%">
                    {{-- ... (Thead Anda tetap sama) ... --}}
                    <thead class="table-dark">
                        <tr>
                            <th width="3%">#</th>
                            <th width="12%">Nama</th>
                            <th width="10%">Sekolah</th>
                            <th width="8%">Tanggal</th>
                            <th width="5%">Sesi</th>
                            <th width="5%">Jam</th>
                            <th width="10%">Status</th>
                            <th width="8%">Approval</th>
                            <th width="15%">Keterangan</th>
                            <th width="8%">Bukti Foto</th>
                            <th width="16%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Presensi Kamera --}}
    <div class="modal fade" id="presensiModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">📷 Presensi Kamera</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('administrator.presensi.partials.camera')
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Form Izin/Sakit --}}
    <div class="modal fade" id="izinModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🏥 Form Izin/Sakit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('administrator.presensi.partials.form_izinsakit')
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Request Approval --}}
    <div class="modal fade" id="requestApprovalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">📝 Request Perubahan Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="requestApprovalForm" method="POST" action="{{ route('presensi.request.approval.date') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tanggal_presensi" id="requestTanggal">

                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Request perubahan status untuk tanggal <strong id="displayTanggal"></strong>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status yang diminta <span class="text-danger">*</span></label>
                            <select name="requested_status" class="form-control" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="Izin">Izin</option>
                                <option value="Sakit">Sakit</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alasan/Keterangan <span class="text-danger">*</span></label>
                            <textarea name="keterangan" class="form-control" rows="4" placeholder="Jelaskan alasan..." minlength="20"
                                required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bukti Pendukung (Foto)</label>
                            <input type="file" name="bukti_foto" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Kirim Permintaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Approval Details --}}
    <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">✅ Process Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="approvalForm" method="POST">
                        @csrf
                        <input type="hidden" name="action" id="approvalAction">

                        <div class="mb-3">
                            <label class="form-label">Siswa</label>
                            <input type="text" id="studentName" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status yang Diminta</label>
                            <input type="text" id="requestedStatus" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alasan Siswa</label>
                            <textarea id="studentReason" class="form-control" rows="3" readonly></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan Admin</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-danger" onclick="submitApproval('reject')">
                                <i class="fas fa-times me-1"></i> Tolak
                            </button>
                            <button type="button" class="btn btn-success" onclick="submitApproval('approve')">
                                <i class="fas fa-check me-1"></i> Setujui
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal untuk melihat gambar --}}
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🖼️ Bukti Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" alt="Bukti foto">
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script>
        let unifiedTable;
        let currentPresensiId = null;

        $(function() {
            initializeUnifiedTable();
            loadStats();
            loadSekolahFilter();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Custom search dengan debounce
            let searchTimeout;
            $('#custom-search').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 500);
            });

            // Custom length change
            $('#custom-length').on('change', function() {
                unifiedTable.page.len($(this).val()).draw();
            });

            // Enter key pada search
            $('#custom-search').on('keypress', function(e) {
                if (e.which === 13) {
                    performSearch();
                }
            });

            // Auto apply filter saat berubah (opsional)
            $('#filter-tanggal, #filter-bulan, #filter-status, #filter-sesi, #filter-approval, #filter-sekolah')
                .on('change', function() {
                    // Uncomment jika ingin auto apply
                    // applyFilters();
                });
        }

        function initializeUnifiedTable() {
            unifiedTable = $('#table-unified').DataTable({
                processing: true,
                serverSide: true,
                searching: false, // Disable default search karena kita pakai custom
                lengthChange: false, // Disable default length change
                ajax: {
                    url: '{{ route('presensi.data.unified') }}',
                    data: function(d) {
                        // Filter parameters
                        d.filter_tanggal = $('#filter-tanggal').val();
                        d.filter_bulan = $('#filter-bulan').val();
                        d.filter_status = $('#filter-status').val();
                        d.filter_sesi = $('#filter-sesi').val();
                        d.filter_approval = $('#filter-approval').val();
                        d.filter_sekolah = $('#filter-sekolah').val();

                        // Custom search
                        d.search = {
                            value: $('#custom-search').val(),
                            regex: false
                        };

                        // Length
                        d.length = $('#custom-length').val();
                    },
                    error: function(xhr, error, code) {
                        console.error('Ajax error unified:', xhr.responseText);
                        showToast('Error loading data', 'error');
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '3%'
                    },
                    {
                        data: 'nama',
                        name: 'user.name',
                        width: '12%'
                    },
                    {
                        data: 'sekolah',
                        name: 'user.sekolah.nama',
                        width: '10%'
                    },
                    {
                        data: 'tanggal',
                        name: 'tanggal_presensi',
                        width: '8%'
                    },
                    {
                        data: 'sesi_badge',
                        name: 'sesi',
                        orderable: false,
                        width: '5%'
                    },
                    {
                        data: 'jam_presensi',
                        name: 'jam_presensi',
                        width: '5%'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false,
                        width: '10%'
                    },
                    {
                        data: 'approval_badge',
                        name: 'approval_status',
                        orderable: false,
                        searchable: false,
                        width: '8%'
                    },
                    {
                        data: 'keterangan',
                        name: 'keterangan',
                        width: '15%'
                    },
                    {
                        data: 'bukti_foto',
                        name: 'bukti_foto',
                        orderable: false,
                        searchable: false,
                        width: '8%'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false,
                        width: '16%'
                    }
                ],
                order: [
                    [3, 'desc'],
                    [4, 'asc']
                ],
                pageLength: 25,
                scrollX: true,
                dom: '<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                language: {
                    processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    zeroRecords: '<div class="text-center py-4"><i class="fas fa-search fa-2x text-muted mb-3"></i><br><h5>Data tidak ditemukan</h5><p class="text-muted">Coba ubah kriteria pencarian atau filter</p></div>',
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    search: "Cari:",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                },
                drawCallback: function(settings) {
                    // Update info setelah draw
                    updateTableInfo(settings);
                }
            });
        }

        function updateTableInfo(settings) {
            const api = new $.fn.dataTable.Api(settings);
            const info = api.page.info();

            // Custom info update bisa ditambahkan di sini jika diperlukan
            console.log('Table updated:', info);
        }

        function performSearch() {
            unifiedTable.ajax.reload();
        }

        function toggleFilters() {
            const filterControls = document.getElementById('filter-controls');
            const toggleBtn = document.getElementById('toggle-filters');

            if (filterControls.classList.contains('show')) {
                filterControls.classList.remove('show');
                toggleBtn.innerHTML = '<i class="fas fa-filter"></i> Filter';
            } else {
                filterControls.classList.add('show');
                toggleBtn.innerHTML = '<i class="fas fa-filter-circle-xmark"></i> Tutup Filter';
            }
        }

        function applyFilters() {
            unifiedTable.ajax.reload();
            loadStats();
            showToast('Filter berhasil diterapkan', 'success');
        }

        function resetFilters() {
            $('#filter-tanggal, #filter-bulan, #filter-status, #filter-sesi, #filter-approval, #filter-sekolah').val('');
            $('#custom-search').val('');
            applyFilters();
            showToast('Filter berhasil direset', 'info');
        }

        function loadStats() {
            @if (Auth::user()->group_id == 2)
                $.ajax({
                    url: '{{ route('presensi.stats') }}',
                    method: 'GET',
                    success: function(data) {
                        $('#stat-hadir').text(data.hadir || 0);
                        $('#stat-terlambat').text(data.terlambat || 0);
                        $('#stat-izin').text(data.izin || 0);
                        $('#stat-sakit').text(data.sakit || 0);
                        $('#stat-alpa').text(data.alpa || 0);
                        $('#stat-pending').text(data.pending || 0);
                    },
                    error: function() {
                        console.error('Error loading stats');
                    }
                });
            @endif
        }

        function loadSekolahFilter() {
            $.ajax({
                url: '{{ route('presensi.sekolah.list') }}',
                success: function(data) {
                    let options = '<option value="">Semua Sekolah</option>';
                    data.forEach(function(sekolah) {
                        options += `<option value="${sekolah.id}">${sekolah.nama}</option>`;
                    });
                    $('#filter-sekolah').html(options);
                },
                error: function() {
                    console.error('Error loading sekolah list');
                }
            });
        }

        // Toast notification function
        function showToast(message, type = 'info') {
            const toastHtml = `
                <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;

            $('body').append(toastHtml);
            const toastEl = $('.toast').last()[0];
            const toast = new bootstrap.Toast(toastEl);
            toast.show();

            // Remove element after hidden
            $(toastEl).on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }

        // Modal Functions
        function showPresensiModal() {
            new bootstrap.Modal(document.getElementById('presensiModal')).show();
        }

        function showIzinModal() {
            new bootstrap.Modal(document.getElementById('izinModal')).show();
        }

        function requestApprovalForDate(tanggal, presensiId) {
            document.getElementById('requestTanggal').value = tanggal;
            document.getElementById('displayTanggal').textContent = new Date(tanggal).toLocaleDateString('id-ID');
            new bootstrap.Modal(document.getElementById('requestApprovalModal')).show();
        }

        function showApprovalModal(presensiId, studentName, requestedStatus, keterangan) {
            currentPresensiId = presensiId;

            document.getElementById('studentName').value = studentName;
            document.getElementById('requestedStatus').value = requestedStatus;
            document.getElementById('studentReason').value = keterangan || '-';
            document.getElementById('approvalForm').action = `/presensi/approval/${presensiId}`;

            new bootstrap.Modal(document.getElementById('approvalModal')).show();
        }

        function submitApproval(action) {
            document.getElementById('approvalAction').value = action;
            document.getElementById('approvalForm').submit();
        }

        function processQuickApproval(presensiId, action) {
            if (!confirm(`Yakin ingin ${action === 'approve' ? 'menyetujui' : 'menolak'} permintaan ini?`)) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/presensi/approval/${presensiId}`;
            form.innerHTML = `
                @csrf
                <input type="hidden" name="action" value="${action}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        function showImage(url) {
            document.getElementById('modalImage').src = url;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }

        function editPresensi(id) {
            showToast('Fitur edit akan dikembangkan', 'info');
        }

        function deletePresensi(id) {
            if (confirm('Yakin ingin menghapus data presensi ini?')) {
                showToast('Fitur delete akan dikembangkan', 'info');
            }
        }

        function generateAlpa() {
            if (!confirm('Yakin ingin generate presensi Alpa untuk siswa yang belum presensi hari ini?')) {
                return;
            }

            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            btn.disabled = true;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('presensi.generate.alpa') }}';
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }

        // Update function exportExcel() dan exportPDF() di file JavaScript presensi

        function exportExcel() {
            const filters = getActiveFilters();

            // Tambahkan loading state
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            btn.disabled = true;

            const params = new URLSearchParams();

            // Add filters
            Object.keys(filters).forEach(key => {
                if (filters[key]) params.append(key, filters[key]);
            });

            // Add rekap format flag
            params.append('format', 'rekap');

            // Create form untuk POST request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('presensi.export.excel') }}';
            form.style.display = 'none';

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfInput);

            // Add parameters
            params.forEach((value, key) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            });

            document.body.appendChild(form);

            showToast('Sedang memproses export Excel...', 'info');

            // Submit form
            form.submit();

            // Reset button after a delay
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                document.body.removeChild(form);
            }, 3000);
        }

        function exportPDF() {
            const filters = getActiveFilters();

            // Tambahkan loading state
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            btn.disabled = true;

            const params = new URLSearchParams();

            // Add filters
            Object.keys(filters).forEach(key => {
                if (filters[key]) params.append(key, filters[key]);
            });

            // Add rekap format flag
            params.append('format', 'rekap');

            showToast('Sedang memproses export PDF...', 'info');

            // Open in new window untuk PDF
            const url = `{{ route('presensi.export.pdf') }}?${params.toString()}`;
            window.open(url, '_blank');

            // Reset button
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        }

        // Helper function untuk mengambil filter aktif
        function getActiveFilters() {
            return {
                filter_tanggal: $('#filter-tanggal').val(),
                filter_bulan: $('#filter-bulan').val(),
                filter_status: $('#filter-status').val(),
                filter_sesi: $('#filter-sesi').val(),
                filter_approval: $('#filter-approval').val(),
                filter_sekolah: $('#filter-sekolah').val(),
                search: $('#custom-search').val(),
                // Tambahkan parameter untuk rekap
                bulan: extractMonthFromFilter(),
                tahun: extractYearFromFilter()
            };
        }

        // Helper functions untuk extract bulan dan tahun
        function extractMonthFromFilter() {
            const filterBulan = $('#filter-bulan').val();
            if (filterBulan) {
                return filterBulan.split('-')[1]; // Extract month from YYYY-MM
            }
            return new Date().getMonth() + 1; // Current month
        }

        function extractYearFromFilter() {
            const filterBulan = $('#filter-bulan').val();
            if (filterBulan) {
                return filterBulan.split('-')[0]; // Extract year from YYYY-MM
            }
            return new Date().getFullYear(); // Current year
        }

        // Function untuk export rekap khusus (tanpa filter detail)
        function exportRekapExcel() {
            const bulan = extractMonthFromFilter();
            const tahun = extractYearFromFilter();

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('presensi.export.excel') }}';
            form.style.display = 'none';

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfInput);

            // Add bulan and tahun
            const bulanInput = document.createElement('input');
            bulanInput.type = 'hidden';
            bulanInput.name = 'bulan';
            bulanInput.value = bulan;
            form.appendChild(bulanInput);

            const tahunInput = document.createElement('input');
            tahunInput.type = 'hidden';
            tahunInput.name = 'tahun';
            tahunInput.value = tahun;
            form.appendChild(tahunInput);

            // Add format flag
            const formatInput = document.createElement('input');
            formatInput.type = 'hidden';
            formatInput.name = 'format';
            formatInput.value = 'rekap';
            form.appendChild(formatInput);

            document.body.appendChild(form);

            showToast('Memproses export Excel...', 'info');
            form.submit();

            setTimeout(() => {
                document.body.removeChild(form);
            }, 3000);
        }

        function exportRekapPDF() {
            const bulan = extractMonthFromFilter();
            const tahun = extractYearFromFilter();

            const params = new URLSearchParams({
                bulan: bulan,
                tahun: tahun,
                format: 'rekap'
            });

            showToast('Memproses export PDF...', 'info');
            window.open(`{{ route('presensi.export.pdf') }}?${params.toString()}`, '_blank');
        }

        // Update button export di HTML untuk menggunakan function baru
        function updateExportButtons() {
            // Update existing export buttons langsung tanpa dropdown
            const exportContainer = document.querySelector('.btn-group');
            if (exportContainer) {
                exportContainer.innerHTML = `
            <button type="button" class="btn btn-outline-success" onclick="exportRekapExcel()">
                <i class="fas fa-file-excel"></i> Export Rekap Excel
            </button>
            <button type="button" class="btn btn-outline-danger" onclick="exportRekapPDF()">
                <i class="fas fa-file-pdf"></i> Export Rekap PDF
            </button>
        `;
            }
        }


        // Call update function when page loads
        $(document).ready(function() {
            updateExportButtons();
        });

        // Auto refresh stats setiap 5 menit
        @if (Auth::user()->group_id == 2)
            setInterval(loadStats, 300000);
        @endif

        // Set current month as default untuk filter bulan
        $(document).ready(function() {
            const now = new Date();
            const currentMonth = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
            $('#filter-bulan').val(currentMonth);
        });
    </script>
    @include('administrator.presensi.partials.camera_script')
@endsection
