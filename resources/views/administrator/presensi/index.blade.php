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

        .izin-sakit-form {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .hidden-logo {
            display: none;
        }

        /* Approval tab specific styles */
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
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">📱 Presensi Digital</h4>
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

    {{-- Status Presensi + Tombol --}}
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

            {{-- Tombol di bagian bawah card-body --}}
            <div class="mt-3 d-flex gap-2">
                @if ($statusPresensi['can_presensi'])
                    <button class="btn btn-primary btn-sm" onclick="showPresensiModal()">📷 Presensi</button>
                @endif
                <button class="btn btn-warning btn-sm" onclick="showIzinModal()">🏥 Izin/Sakit</button>
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

    {{-- Tabs Navigation --}}
    <ul class="nav nav-tabs" id="presensiTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#hari-ini">Hari Ini</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#semua">Semua</button>
        </li>
        @if (Auth::user()->group_id == 2)
            <li class="nav-item">
                <button class="nav-link position-relative" data-bs-toggle="tab" data-bs-target="#approval">
                    ✅ Approval
                    @php
                        $pendingCount = \App\Models\Presensi::where('approval_status', 'pending')->count();
                    @endphp
                    @if ($pendingCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </button>
            </li>
        @endif
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content mt-3">
        {{-- Tab Hari Ini --}}
        <div class="tab-pane fade show active" id="hari-ini">
            <table class="table table-bordered" id="table-hari-ini" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Sekolah</th>
                        <th>Status</th>
                        <th>Bukti Foto</th>
                    </tr>
                </thead>
            </table>
        </div>

        {{-- Tab Semua --}}
        <div class="tab-pane fade" id="semua">
            <table class="table table-bordered table-striped" id="table-semua" style="width:100%">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th>Nama</th>
                        <th>Sekolah</th>
                        <th width="10%">Tanggal</th>
                        <th width="12%">Status Harian</th>
                        <th width="20%">Detail Sesi</th>
                        <th width="10%">Bukti Foto</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables akan mengisi ini -->
                </tbody>
            </table>
        </div>

        {{-- Tab Approval (Admin only) --}}
        @if (Auth::user()->group_id == 2)
            <div class="tab-pane fade" id="approval">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h5 class="text-warning">⏳ Permintaan Approval</h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-danger btn-sm me-2" onclick="generateAlpa()"
                            title="Generate Alpa untuk siswa yang belum presensi hari ini">
                            <i class="fas fa-exclamation-triangle"></i> Generate Alpa
                        </button>
                        <small class="text-muted">Total pending: <span
                                class="badge bg-warning">{{ $pendingCount ?? 0 }}</span></small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table-approval">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Siswa</th>
                                <th>Sekolah</th>
                                <th>Sesi</th>
                                <th>Status Awal</th>
                                <th>Status Diminta</th>
                                <th>Alasan</th>
                                <th>Bukti</th>
                                <th>Waktu Request</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables akan mengisi ini -->
                        </tbody>
                    </table>
                </div>

                {{-- History Section --}}
                <div class="mt-4">
                    <h5 class="text-info">📚 History Approval (7 Hari Terakhir)</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="table-approval-history">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Siswa</th>
                                    <th>Status Diminta</th>
                                    <th>Keputusan</th>
                                    <th>Catatan Admin</th>
                                    <th>Diproses Oleh</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables akan mengisi ini -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal Request Approval untuk Tanggal Tertentu --}}
    <div class="modal fade" id="requestApprovalDateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">📝 Request Perubahan Status Alpa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="requestApprovalDateForm" method="POST" action="{{ route('presensi.request.approval.date') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tanggal_presensi" id="requestTanggal">

                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Anda akan meminta perubahan status <span class="badge bg-danger">Alpa</span>
                            untuk tanggal <strong id="displayTanggal"></strong>
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
                            <textarea name="keterangan" class="form-control" rows="4"
                                placeholder="Jelaskan alasan kenapa tidak bisa presensi..." minlength="20" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bukti Pendukung (Foto)</label>
                            <input type="file" name="bukti_foto" class="form-control" accept="image/*">
                            <small class="text-muted">Upload foto sebagai bukti (surat dokter, surat izin, dll)</small>
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

    {{-- Modal Approval Notes --}}
    <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Approval</h5>
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
                            <label class="form-label">Catatan Admin (opsional)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Batal
                            </button>
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
                    <h5 class="modal-title">Bukti Foto</h5>
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
        let currentPresensiId = null;

        $(function() {
            // Debug: Pastikan jQuery dan DataTables loaded
            console.log('Initializing DataTables...');

            // DataTable untuk Hari Ini
            $('#table-hari-ini').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('presensi.data.hari_ini') }}',
                    error: function(xhr, error, code) {
                        console.error('Ajax error hari ini:', xhr.responseText);
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
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
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'bukti_foto',
                        name: 'bukti_foto',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // DataTable untuk Semua
            $('#table-semua').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('presensi.data.semua') }}',
                    error: function(xhr, error, code) {
                        console.error('Ajax error semua:', xhr.responseText);
                        alert('Error loading data. Check console for details.');
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'nama',
                        name: 'user.name',
                        title: 'Nama'
                    },
                    {
                        data: 'sekolah',
                        name: 'user.sekolah.nama',
                        title: 'Sekolah'
                    },
                    {
                        data: 'tanggal',
                        name: 'tanggal_presensi',
                        title: 'Tanggal',
                        width: '10%'
                    },
                    {
                        data: 'status_badge',
                        name: 'status_badge',
                        orderable: false,
                        searchable: false,
                        title: 'Keterangan',
                        width: '12%'

                    },
                    {
                        data: 'detail_sesi',
                        name: 'detail_sesi',
                        orderable: false,
                        searchable: false,
                        title: 'Detail Sesi',
                        width: '20%'
                    },
                    {
                        data: 'bukti_foto',
                        name: 'bukti_foto',
                        orderable: false,
                        searchable: false,
                        title: 'Bukti Foto',
                        width: '10%'
                    }
                ],
                order: [
                    [3, 'desc']
                ], // Sort by tanggal descending
                pageLength: 25,
                language: {
                    processing: "Memuat data...",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    zeroRecords: "Data tidak ditemukan",
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
                }
            });

            // DataTable untuk Approval (hanya jika admin)
            @if (Auth::user()->group_id == 2)
                $('#table-approval').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('presensi.approval.data') }}',
                        error: function(xhr, error, code) {
                            console.error('Ajax error approval:', xhr.responseText);
                        }
                    },
                    columns: [{
                            data: 'tanggal',
                            name: 'tanggal_presensi'
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
                            data: 'sesi',
                            name: 'sesi'
                        },
                        {
                            data: 'status_awal',
                            name: 'status_awal',
                            orderable: false
                        },
                        {
                            data: 'requested_status',
                            name: 'requested_status'
                        },
                        {
                            data: 'keterangan',
                            name: 'keterangan'
                        },
                        {
                            data: 'bukti_foto',
                            name: 'bukti_foto',
                            orderable: false
                        },
                        {
                            data: 'waktu_request',
                            name: 'updated_at'
                        },
                        {
                            data: 'aksi',
                            name: 'aksi',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    order: [
                        [8, 'desc']
                    ], // Sort by waktu request descending
                    pageLength: 10
                });

                // DataTable untuk Approval History
                $('#table-approval-history').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('presensi.approval.history') }}',
                        error: function(xhr, error, code) {
                            console.error('Ajax error approval history:', xhr.responseText);
                        }
                    },
                    columns: [{
                            data: 'tanggal',
                            name: 'tanggal_presensi'
                        },
                        {
                            data: 'nama',
                            name: 'user.name'
                        },
                        {
                            data: 'requested_status',
                            name: 'requested_status'
                        },
                        {
                            data: 'approval_status',
                            name: 'approval_status'
                        },
                        {
                            data: 'approval_notes',
                            name: 'approval_notes'
                        },
                        {
                            data: 'approved_by',
                            name: 'approvedBy.name'
                        },
                        {
                            data: 'approved_at',
                            name: 'approved_at'
                        }
                    ],
                    order: [
                        [6, 'desc']
                    ], // Sort by approved_at descending
                    pageLength: 10
                });
            @endif

            // Handle form submissions

            $('#approvalForm').on('submit', function(e) {
                e.preventDefault();

                var formData = $(this).serialize();
                var actionUrl = $(this).attr('action');

                $.ajax({
                    url: actionUrl,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#approvalModal').modal('hide');
                        $('#table-approval').DataTable().ajax.reload();
                        $('#table-approval-history').DataTable().ajax.reload();
                        // Update pending count badge
                        location.reload();
                    },
                    error: function(xhr) {
                        alert('Gagal memproses approval: ' + xhr.responseJSON.message);
                    }
                });
            });
        });

        // Modal Functions
        function showPresensiModal() {
            var modal = new bootstrap.Modal(document.getElementById('presensiModal'));
            modal.show();
        }

        function showIzinModal() {
            var modal = new bootstrap.Modal(document.getElementById('izinModal'));
            modal.show();
        }

        function requestApprovalForDate(tanggal) {
            document.getElementById('requestTanggal').value = tanggal;
            document.getElementById('displayTanggal').textContent = new Date(tanggal).toLocaleDateString('id-ID');
            document.getElementById('requestApprovalDateForm').reset();
            document.getElementById('requestTanggal').value = tanggal; // Set lagi setelah reset

            new bootstrap.Modal(document.getElementById('requestApprovalDateModal')).show();
        }

        // Quick approve/reject functions
        function processApproval(presensiId, action) {
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

        // Show approval modal with details
        function showApprovalModal(presensiId, studentName, requestedStatus) {
            currentPresensiId = presensiId;

            document.getElementById('studentName').value = studentName;
            document.getElementById('requestedStatus').value = requestedStatus;
            document.getElementById('approvalForm').action = `/presensi/approval/${presensiId}`;

            new bootstrap.Modal(document.getElementById('approvalModal')).show();
        }

        // Submit approval with notes
        function submitApproval(action) {
            document.getElementById('approvalAction').value = action;
            document.getElementById('approvalForm').submit();
        }

        // Generate Alpa function
        function generateAlpa() {
            if (!confirm(
                    'Yakin ingin generate presensi Alpa untuk siswa yang belum presensi hari ini?\n\nIni akan membuat status Alpa otomatis untuk semua siswa yang belum melakukan presensi pagi dan sore.'
                )) {
                return;
            }

            // Show loading
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            btn.disabled = true;

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('presensi.generate.alpa') }}';
            form.innerHTML = '@csrf';

            // Add hidden form to body and submit
            document.body.appendChild(form);
            form.submit();
        }
    </script>
    @include('administrator.presensi.partials.camera_script')
@endsection
