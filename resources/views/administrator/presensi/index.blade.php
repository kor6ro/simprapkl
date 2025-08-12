@extends('layout.main')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
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
            object-position: center;
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
            align-items: center;
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

        .approval-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .edit-request-form {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
        }

        .hidden-logo {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">📱 Presensi Digital</h4>

                @if (auth()->user()->group_id === 2)
                    <form method="POST" action="{{ route('presensi.generate.alpa') }}" class="d-inline">
                        @csrf
                        <button class="btn btn-danger btn-sm"
                            onclick="return confirm('Generate siswa alpa untuk hari ini?')">
                            Generate Siswa Alpa
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Status Presensi Hari Ini --}}
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
                            <span class="badge bg-success session-badge">
                                ✓ {{ $statusPresensi['pagi_status'] }}
                            </span>
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
                            <span class="badge bg-success session-badge">
                                ✓ {{ $statusPresensi['sore_status'] }}
                            </span>
                            @if ($statusPresensi['sore_jam'])
                                <br><small class="text-muted">Jam: {{ $statusPresensi['sore_jam'] }}</small>
                            @endif
                        @else
                            <span class="badge bg-secondary session-badge">Belum Presensi</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h6>Status Saat Ini</h6>
                        <div class="alert alert-info mb-0 py-2">
                            {{ $statusPresensi['message'] }}
                        </div>
                        @if ($statusPresensi['current_session'])
                            <small class="text-muted">Sesi: {{ ucfirst($statusPresensi['current_session']) }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Izin/Sakit --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">🏥 Form Izin/Sakit</h5>
        </div>
        <div class="card-body">
            <div class="izin-sakit-form">
                <p class="mb-3"><strong>Jika Anda tidak dapat hadir karena sakit atau ada keperluan:</strong></p>
                <form action="{{ route('presensi.izin-sakit') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Jenis</label>
                                <select name="jenis" class="form-control" required>
                                    <option value="">Pilih</option>
                                    <option value="Izin">Izin</option>
                                    <option value="Sakit">Sakit</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Jelaskan alasan (minimal 10 karakter)"
                                    required></textarea>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Bukti (opsional)</label>
                                <input type="file" name="bukti_foto" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane me-1"></i> Submit Izin/Sakit
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Camera Presensi Section --}}
    @if ($statusPresensi['can_presensi'])
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">📷 Presensi Otomatis dengan Kamera</h5>
            </div>
            <div class="card-body">
                <div class="camera-container">
                    <div id="cameraStatus" class="status-indicator status-ready">
                        Klik "Aktifkan Kamera" untuk presensi {{ $statusPresensi['current_session'] }}
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="video-container" style="display: none;" id="videoContainer">
                                <video id="video" autoplay muted playsinline></video>
                                <div class="camera-overlay">
                                    <div id="timestamp">Loading...</div>
                                    <div>{{ ucfirst($statusPresensi['current_session']) }} - {{ auth()->user()->name }}
                                    </div>
                                </div>
                            </div>

                            <div class="camera-controls">
                                <button id="startCamera" class="btn btn-primary btn-camera">
                                    <i class="fas fa-video me-1"></i> Aktifkan Kamera
                                </button>
                                <button id="capturePhoto" class="btn btn-success btn-camera" disabled>
                                    <i class="fas fa-camera me-1"></i> Ambil Foto
                                </button>
                                <button id="flipCamera" class="btn btn-secondary btn-camera" disabled
                                    style="display: none;">
                                    <i class="fas fa-sync-alt me-1"></i> Flip
                                </button>
                                <button id="stopCamera" class="btn btn-secondary btn-camera" disabled>
                                    <i class="fas fa-stop me-1"></i> Stop
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <form id="presensiForm" style="display: none;">
                                <div class="mb-3" id="previewContainer" style="display: none;">
                                    <label class="form-label">Preview Foto:</label><br>
                                    <img id="preview" class="preview-image" alt="Preview foto">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Keterangan (opsional):</label>
                                    <textarea id="keterangan" class="form-control" rows="2" placeholder="Tambahkan keterangan jika diperlukan"></textarea>
                                </div>

                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-paper-plane me-1"></i>
                                    Submit Presensi {{ ucfirst($statusPresensi['current_session']) }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <canvas id="canvas" style="display: none;"></canvas>

                    {{-- Hidden logo for canvas overlay --}}
                    @if (auth()->user()->sekolah && auth()->user()->sekolah->logo)
                        <img id="schoolLogo" class="hidden-logo"
                            src="{{ asset('uploads/sekolah_logo/' . auth()->user()->sekolah->logo) }}"
                            crossorigin="anonymous" alt="Logo Sekolah">
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Data Presensi Hari Ini --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">📊 Data Presensi Hari Ini</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Sekolah</th>
                            <th>Sesi</th>
                            <th>Jam</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Bukti</th>
                            @if (auth()->user()->group_id === 2)
                                <th>Approval</th>
                            @endif
                            @if (auth()->user()->group_id === 4)
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $item)
                            <tr>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->user->sekolah->nama ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->sesi === 'pagi' ? 'info' : 'warning' }}">
                                        {{ ucfirst($item->sesi) }}
                                    </span>
                                </td>
                                <td>{{ $item->jam_presensi ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->status_color }}">
                                        {{ $item->status_display }}
                                    </span>
                                    @if ($item->approval_status)
                                        <br><span
                                            class="badge bg-{{ $item->approval_status === 'pending' ? 'warning' : ($item->approval_status === 'approved' ? 'success' : 'danger') }} approval-badge">
                                            {{ ucfirst($item->approval_status) }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $item->keterangan ?? '-' }}</td>
                                <td>
                                    @if ($item->bukti_foto)
                                        <button class="btn btn-sm btn-outline-primary"
                                            onclick="showImage('{{ asset('storage/' . $item->bukti_foto) }}')">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                {{-- Admin Approval Column --}}
                                @if (auth()->user()->group_id === 2 && $item->approval_status === 'pending')
                                    <td>
                                        <form action="{{ route('presensi.approval', $item->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-success" title="Setujui">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('presensi.approval', $item->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Tolak">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </td>
                                @elseif(auth()->user()->group_id === 2)
                                    <td>-</td>
                                @endif

                                {{-- Student Edit Request Column --}}
                                @if (auth()->user()->group_id === 4 && $item->user_id === auth()->id())
                                    <td>
                                        @if ($item->status === 'Alpa' && !$item->approval_status)
                                            <button class="btn btn-sm btn-warning"
                                                onclick="showEditRequest({{ $item->id }})">
                                                <i class="fas fa-edit"></i> Ubah ke Izin/Sakit
                                            </button>
                                        @elseif($item->approval_status === 'pending')
                                            <span class="text-warning">Menunggu Approval</span>
                                        @elseif($item->approval_status === 'rejected')
                                            <button class="btn btn-sm btn-warning"
                                                onclick="showEditRequest({{ $item->id }})">
                                                <i class="fas fa-edit"></i> Ajukan Lagi
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @elseif(auth()->user()->group_id === 4)
                                    <td>-</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->group_id === 2 ? '8' : (auth()->user()->group_id === 4 ? '8' : '7') }}"
                                    class="text-center text-muted">
                                    Belum ada data presensi hari ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal untuk melihat gambar --}}
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bukti Foto Presensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" alt="Bukti presensi">
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Edit Request --}}
    <div class="modal fade" id="editRequestModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Status Alpa ke Izin/Sakit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editRequestForm" action="{{ route('presensi.request-edit') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="presensi_id" id="editPresensiId">

                        <div class="mb-3">
                            <label class="form-label">Ubah Status Menjadi</label>
                            <select name="new_status" class="form-control" required>
                                <option value="">Pilih Status Baru</option>
                                <option value="Izin">Izin</option>
                                <option value="Sakit">Sakit</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alasan Perubahan</label>
                            <textarea name="keterangan" class="form-control" rows="3"
                                placeholder="Jelaskan mengapa Anda tidak hadir pada hari tersebut (minimal 10 karakter)" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bukti Pendukung</label>
                            <input type="file" name="bukti_foto" class="form-control" accept="image/*">
                            <small class="text-muted">Upload surat keterangan dokter untuk sakit, atau surat izin untuk
                                izin</small>
                        </div>

                        <div class="alert alert-warning">
                            <strong>Catatan:</strong> Permintaan perubahan status akan dikirim ke admin untuk disetujui.
                            Pastikan alasan dan bukti yang Anda berikan valid.
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-1"></i> Ajukan Perubahan Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        class PresensiCamera {
            constructor() {
                this.video = document.getElementById('video');
                this.canvas = document.getElementById('canvas');
                this.preview = document.getElementById('preview');
                this.startBtn = document.getElementById('startCamera');
                this.captureBtn = document.getElementById('capturePhoto');
                this.stopBtn = document.getElementById('stopCamera');
                this.form = document.getElementById('presensiForm');
                this.status = document.getElementById('cameraStatus');
                this.timestamp = document.getElementById('timestamp');
                this.videoContainer = document.getElementById('videoContainer');
                this.previewContainer = document.getElementById('previewContainer');
                this.flipBtn = document.getElementById('flipCamera');
                this.schoolLogo = document.getElementById('schoolLogo');

                this.currentFacingMode = 'user';
                this.isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator
                    .userAgent);
                this.stream = null;
                this.capturedImage = null;

                if (this.startBtn) {
                    this.initEventListeners();
                    this.updateTimestamp();
                    setInterval(() => this.updateTimestamp(), 1000);
                }
            }

            initEventListeners() {
                this.startBtn.addEventListener('click', () => this.startCamera());
                this.captureBtn.addEventListener('click', () => this.capturePhoto());
                this.stopBtn.addEventListener('click', () => this.stopCamera());

                if (this.flipBtn && this.isMobile) {
                    this.flipBtn.addEventListener('click', () => this.flipCamera());
                    this.flipBtn.style.display = 'block';
                }

                this.form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.submitPresensi();
                });
            }

            updateTimestamp() {
                if (!this.timestamp) return;
                const now = new Date();
                this.timestamp.textContent = now.toLocaleString('id-ID', {
                    weekday: 'short',
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            }

            async startCamera() {
                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: this.currentFacingMode,
                            width: {
                                ideal: 720
                            },
                            height: {
                                ideal: 720
                            },
                            aspectRatio: 1.0
                        }
                    });

                    this.video.srcObject = this.stream;
                    this.videoContainer.style.display = 'block';

                    this.startBtn.disabled = true;
                    this.captureBtn.disabled = false;
                    this.stopBtn.disabled = false;

                    if (this.flipBtn && this.isMobile) {
                        this.flipBtn.disabled = false;
                    }

                    this.updateStatus('Kamera aktif - Ambil foto untuk presensi otomatis', 'ready');

                } catch (error) {
                    console.error('Camera error:', error);
                    this.updateStatus('Gagal mengakses kamera: ' + error.message, 'error');
                }
            }

            async flipCamera() {
                if (!this.isMobile) return;

                try {
                    this.currentFacingMode = this.currentFacingMode === 'user' ? 'environment' : 'user';

                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                    }

                    await this.startCamera();
                } catch (error) {
                    console.error('Flip camera error:', error);
                    this.currentFacingMode = this.currentFacingMode === 'user' ? 'environment' : 'user';
                    this.updateStatus('Gagal mengganti kamera', 'error');
                }
            }

            capturePhoto() {
                if (!this.stream || !this.video.videoWidth) {
                    this.updateStatus('Kamera belum siap', 'warning');
                    return;
                }

                const minDimension = Math.min(this.video.videoWidth, this.video.videoHeight);
                this.canvas.width = minDimension;
                this.canvas.height = minDimension;

                const ctx = this.canvas.getContext('2d');
                const cropX = (this.video.videoWidth - minDimension) / 2;
                const cropY = (this.video.videoHeight - minDimension) / 2;

                ctx.drawImage(this.video, cropX, cropY, minDimension, minDimension, 0, 0, minDimension, minDimension);

                // Add overlay
                const now = new Date();
                const timestampText = now.toLocaleString('id-ID');
                const locationText = '{{ auth()->user()->sekolah->nama ?? 'SMKN 1 Pacitan' }} - SimPraPKL';
                const userText = '{{ auth()->user()->name }}';
                const sessionText = 'Sesi: {{ $statusPresensi['current_session'] ?? 'Auto' }}';

                const overlayHeight = Math.min(120, minDimension * 0.3);
                const overlayWidth = Math.min(400, minDimension - 20);

                ctx.fillStyle = 'rgba(0, 0, 0, 0.8)';
                ctx.fillRect(10, minDimension - overlayHeight - 10, overlayWidth, overlayHeight);

                const fontSize = Math.max(12, Math.min(14, minDimension / 50));
                ctx.fillStyle = 'white';
                ctx.font = `bold ${fontSize}px Arial`;
                ctx.textAlign = 'left';

                const textY = minDimension - overlayHeight + 15;
                const lineHeight = fontSize + 6;
                ctx.fillText(timestampText, 20, textY);
                ctx.fillText(locationText, 20, textY + lineHeight);
                ctx.fillText(`User: ${userText}`, 20, textY + (lineHeight * 2));
                ctx.fillText(sessionText, 20, textY + (lineHeight * 3));

                // Add school logo
                if (this.schoolLogo && this.schoolLogo.complete) {
                    try {
                        const logoSize = Math.min(100, minDimension * 0.25);
                        const logoX = minDimension - logoSize - 25;
                        const logoY = 25;
                        ctx.drawImage(this.schoolLogo, logoX, logoY, logoSize, logoSize);
                    } catch (e) {
                        console.error('Logo error:', e);
                    }
                }

                this.capturedImage = this.canvas.toDataURL('image/jpeg', 0.8);
                this.preview.src = this.capturedImage;
                this.previewContainer.style.display = 'block';
                this.form.style.display = 'block';

                this.updateStatus('Foto siap - Submit untuk presensi otomatis', 'ready');
            }

            stopCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.stream = null;
                }

                this.videoContainer.style.display = 'none';
                this.form.style.display = 'none';
                this.previewContainer.style.display = 'none';

                this.startBtn.disabled = false;
                this.captureBtn.disabled = true;
                this.stopBtn.disabled = true;

                if (this.flipBtn) {
                    this.flipBtn.disabled = true;
                }

                this.updateStatus('Kamera dimatikan', 'ready');
                this.resetForm();
            }

            async submitPresensi() {
                if (!this.capturedImage) {
                    this.updateStatus('Belum ada foto', 'error');
                    return;
                }

                this.updateStatus('Mengirim presensi...', 'warning');

                try {
                    const payload = {
                        image_data: this.capturedImage,
                        keterangan: document.getElementById('keterangan').value || ''
                    };

                    const response = await fetch('{{ route('presensi.camera') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify(payload)
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        this.updateStatus('✅ ' + result.message, 'ready');
                        this.stopCamera();
                        this.showAlert('success', result.message);

                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        throw new Error(result.message || 'Server error');
                    }

                } catch (error) {
                    console.error('Submit error:', error);
                    this.updateStatus('❌ Gagal: ' + error.message, 'error');
                }
            }

            updateStatus(message, type) {
                if (this.status) {
                    this.status.textContent = message;
                    this.status.className = `status-indicator status-${type}`;
                }
            }

            resetForm() {
                if (this.form) {
                    this.form.reset();
                }
                this.capturedImage = null;
            }

            showAlert(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
                alertDiv.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                const titleBox = document.querySelector('.page-title-box').parentElement.parentElement;
                titleBox.insertAdjacentElement('afterend', alertDiv);

                setTimeout(() => {
                    if (alertDiv.parentElement) {
                        alertDiv.remove();
                    }
                }, 5000);
            }
        }

        // Initialize camera when DOM loaded
        document.addEventListener('DOMContentLoaded', () => {
            new PresensiCamera();
        });

        // Function to show image in modal
        function showImage(src) {
            document.getElementById('modalImage').src = src;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }

        // Function to show edit request modal
        function showEditRequest(presensiId) {
            document.getElementById('editPresensiId').value = presensiId;
            new bootstrap.Modal(document.getElementById('editRequestModal')).show();
        }
    </script>
@endsection
