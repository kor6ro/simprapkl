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
            /* Force 1:1 aspect ratio */
            aspect-ratio: 1 / 1;
            max-width: 400px;
            width: 100%;
        }

        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* This will crop the video to fill the square */
            object-position: center;
            /* Center the video in the square */
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
            /* Force 1:1 aspect ratio for preview */
            aspect-ratio: 1 / 1;
            object-fit: cover;
            object-position: center;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-camera {
            min-width: 120px;
        }

        /* Camera controls styling */
        .camera-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
        }

        .flip-camera-btn {
            background: #6c757d;
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 100px;
        }

        .flip-camera-btn:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }

        .flip-camera-btn:disabled {
            background: #adb5bd;
            cursor: not-allowed;
            transform: none;
        }

        /* Responsive button layout */
        @media (max-width: 576px) {
            .camera-controls {
                flex-direction: column;
                gap: 10px;
            }

            .btn-camera,
            .flip-camera-btn {
                width: 100%;
                max-width: 200px;
                justify-content: center;
            }
        }

        @media (min-width: 577px) {
            .camera-controls {
                flex-direction: row;
            }

            .btn-camera {
                flex: 1;
                max-width: 140px;
            }

            .flip-camera-btn {
                flex: 0 0 auto;
            }
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

        /* Ensure modal image also maintains aspect ratio if needed */
        #modalImage {
            max-width: 100%;
            max-height: 80vh;
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

    {{-- Camera Presensi Section --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">📷 Presensi dengan Kamera</h5>
        </div>
        <div class="card-body">
            <div class="camera-container">
                <div id="cameraStatus" class="status-indicator status-ready">
                    Klik "Aktifkan Kamera" untuk mulai presensi
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="video-container" style="display: none;" id="videoContainer">
                            <video id="video" autoplay muted playsinline></video>
                            <div class="camera-overlay">
                                <div id="timestamp">Loading...</div>
                                <div>SimPraPKL - {{ auth()->user()->name }}</div>
                            </div>
                        </div>

                        <div class="camera-controls">
                            <button id="startCamera" class="btn btn-primary btn-camera">
                                <i class="fas fa-video me-1"></i> Aktifkan Kamera
                            </button>
                            <button id="capturePhoto" class="btn btn-success btn-camera" disabled>
                                <i class="fas fa-camera me-1"></i> Ambil Foto
                            </button>
                            <button id="flipCamera" class="flip-camera-btn" disabled title="Ganti Kamera"
                                style="display: none;">
                                <i class="fas fa-sync-alt"></i>
                                <span class="d-none d-sm-inline">Flip</span>
                            </button>
                            <button id="stopCamera" class="btn btn-secondary btn-camera" disabled>
                                <i class="fas fa-stop me-1"></i> Stop
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <form id="presensiForm" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Jenis Presensi:</label>
                                <select id="presensiType" class="form-control" required>
                                    <option value="">Pilih jenis presensi</option>
                                    <option value="masuk">📥 Absen Masuk</option>
                                    <option value="keluar">📤 Absen Keluar</option>
                                    <option value="izin">🗂️ Izin</option>
                                    <option value="sakit">🏥 Sakit</option>
                                </select>
                            </div>

                            <div class="mb-3" id="keteranganGroup" style="display: none;">
                                <label class="form-label">Keterangan:</label>
                                <textarea id="keterangan" class="form-control" rows="2"
                                    placeholder="Jelaskan alasan izin/sakit (minimal 10 karakter)"></textarea>
                            </div>

                            <div class="mb-3" id="previewContainer" style="display: none;">
                                <label class="form-label">Preview Foto:</label><br>
                                <img id="preview" class="preview-image" alt="Preview foto">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-1"></i> Submit Presensi
                            </button>
                        </form>
                    </div>
                </div>

                <canvas id="canvas" style="display: none;"></canvas>
            </div>
        </div>
    </div>

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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
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
@endsection

@section('js')
    <script>
        class PresensiCamera {
            constructor() {
                // Get DOM elements dengan error checking
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
                this.presensiType = document.getElementById('presensiType');
                this.keteranganGroup = document.getElementById('keteranganGroup');
                this.previewContainer = document.getElementById('previewContainer');
                this.flipBtn = document.getElementById('flipCamera');

                // Camera state
                this.currentFacingMode = 'user'; // 'user' for front, 'environment' for back
                this.isMobile = this.detectMobile();

                // Debug: Check if all elements exist
                const elements = {
                    video: this.video,
                    startBtn: this.startBtn,
                    captureBtn: this.captureBtn,
                    stopBtn: this.stopBtn
                };

                console.log('DOM Elements check:', elements);

                // Check for missing elements
                Object.entries(elements).forEach(([name, element]) => {
                    if (!element) {
                        console.error(`Missing element: ${name}`);
                    }
                });

                this.stream = null;
                this.capturedImage = null;

                // Only initialize if critical elements exist
                if (this.startBtn && this.captureBtn && this.stopBtn) {
                    this.initEventListeners();
                    this.updateTimestamp();

                    // Show/hide flip button based on device
                    if (this.flipBtn) {
                        this.flipBtn.style.display = this.isMobile ? 'block' : 'none';
                    }

                    // Update timestamp setiap detik
                    setInterval(() => this.updateTimestamp(), 1000);
                } else {
                    console.error('Critical DOM elements missing - camera initialization failed');
                }
            }

            initEventListeners() {
                // Event listeners untuk tombol camera
                this.startBtn.addEventListener('click', () => this.startCamera());
                this.captureBtn.addEventListener('click', () => this.capturePhoto());
                this.stopBtn.addEventListener('click', () => this.stopCamera());

                // Event listener untuk flip camera (hanya mobile)
                if (this.flipBtn) {
                    this.flipBtn.addEventListener('click', () => this.flipCamera());
                }

                // Event listener untuk dropdown jenis presensi
                this.presensiType.addEventListener('change', () => {
                    const value = this.presensiType.value;
                    if (value === 'izin' || value === 'sakit') {
                        this.keteranganGroup.style.display = 'block';
                        document.getElementById('keterangan').required = true;
                    } else {
                        this.keteranganGroup.style.display = 'none';
                        document.getElementById('keterangan').required = false;
                    }
                });

                // Event listener untuk form submit
                this.form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.submitPresensi();
                });
            }

            updateTimestamp() {
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

            detectMobile() {
                return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
                    (window.innerWidth <= 768) ||
                    ('ontouchstart' in window);
            }

            async getCameraDevices() {
                try {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const videoDevices = devices.filter(device => device.kind === 'videoinput');
                    console.log('Available video devices:', videoDevices.length);
                    return videoDevices.length > 1; // Return true if multiple cameras available
                } catch (error) {
                    console.error('Error enumerating devices:', error);
                    return false;
                }
            }

            async startCamera() {
                try {
                    console.log('Starting camera...');
                    console.log('Current facing mode:', this.currentFacingMode);
                    console.log('Is mobile device:', this.isMobile);

                    // Request camera dengan preferensi facing mode dan aspect ratio 1:1
                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: this.currentFacingMode,
                            width: {
                                ideal: 720
                            },
                            height: {
                                ideal: 720
                            },
                            aspectRatio: 1.0 // Force 1:1 aspect ratio
                        }
                    });

                    console.log('Camera stream obtained:', this.stream);

                    this.video.srcObject = this.stream;
                    this.videoContainer.style.display = 'block';

                    // Update button states
                    this.startBtn.disabled = true;
                    this.captureBtn.disabled = false;
                    this.stopBtn.disabled = false;

                    // Show flip button only on mobile and if multiple cameras available
                    if (this.isMobile && this.flipBtn) {
                        const hasMultipleCameras = await this.getCameraDevices();
                        this.flipBtn.style.display = hasMultipleCameras ? 'block' : 'none';
                    }

                    this.updateStatus('Kamera aktif - Posisikan wajah dan klik "Ambil Foto"', 'ready');

                } catch (error) {
                    console.error('Error accessing camera:', error);
                    let errorMessage = 'Gagal mengakses kamera';

                    if (error.name === 'NotAllowedError') {
                        errorMessage = 'Akses kamera ditolak. Silakan berikan izin akses kamera.';
                    } else if (error.name === 'NotFoundError') {
                        errorMessage = 'Kamera tidak ditemukan di perangkat ini.';
                    } else if (error.name === 'NotSupportedError') {
                        errorMessage = 'Browser tidak mendukung akses kamera.';
                    } else if (error.name === 'OverconstrainedError') {
                        // Fallback: try without specific facing mode
                        console.log('Overconstrained error, trying fallback...');
                        try {
                            this.stream = await navigator.mediaDevices.getUserMedia({
                                video: {
                                    width: {
                                        ideal: 720
                                    },
                                    height: {
                                        ideal: 720
                                    }
                                }
                            });

                            this.video.srcObject = this.stream;
                            this.videoContainer.style.display = 'block';
                            this.startBtn.disabled = true;
                            this.captureBtn.disabled = false;
                            this.stopBtn.disabled = false;

                            this.updateStatus('Kamera aktif - Posisikan wajah dan klik "Ambil Foto"', 'ready');
                            return;
                        } catch (fallbackError) {
                            errorMessage = 'Kamera tidak mendukung konfigurasi yang diminta.';
                        }
                    }

                    this.updateStatus(errorMessage, 'error');
                }
            }

            async flipCamera() {
                if (!this.isMobile) return;

                try {
                    console.log('Flipping camera...');

                    // Switch facing mode
                    this.currentFacingMode = this.currentFacingMode === 'user' ? 'environment' : 'user';
                    console.log('New facing mode:', this.currentFacingMode);

                    // Stop current stream
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                    }

                    // Show loading state
                    this.updateStatus('Mengganti kamera...', 'warning');

                    // Add loading animation to flip button
                    const flipIcon = this.flipBtn.querySelector('i');
                    flipIcon.className = 'fas fa-spinner fa-spin';

                    // Start new stream with new facing mode
                    await this.startCamera();

                    // Reset flip button icon
                    flipIcon.className = 'fas fa-sync-alt';

                } catch (error) {
                    console.error('Error flipping camera:', error);

                    // Reset flip button icon
                    const flipIcon = this.flipBtn.querySelector('i');
                    flipIcon.className = 'fas fa-sync-alt';

                    // Try to revert to previous facing mode
                    this.currentFacingMode = this.currentFacingMode === 'user' ? 'environment' : 'user';
                    this.updateStatus('Gagal mengganti kamera, menggunakan kamera sebelumnya', 'error');

                    try {
                        await this.startCamera();
                    } catch (revertError) {
                        this.updateStatus('Gagal mengakses kamera', 'error');
                    }
                }
            }

            capturePhoto() {
                if (!this.stream) {
                    this.updateStatus('Kamera belum aktif', 'error');
                    return;
                }

                // Pastikan video sudah ready
                if (!this.video.videoWidth || !this.video.videoHeight) {
                    this.updateStatus('Kamera belum siap, tunggu beberapa detik', 'warning');
                    return;
                }

                // Calculate square dimensions - use the smaller dimension to ensure we don't exceed bounds
                const minDimension = Math.min(this.video.videoWidth, this.video.videoHeight);

                // Set canvas to square dimensions
                this.canvas.width = minDimension;
                this.canvas.height = minDimension;

                const ctx = this.canvas.getContext('2d');

                // Calculate crop area for centering
                const cropX = (this.video.videoWidth - minDimension) / 2;
                const cropY = (this.video.videoHeight - minDimension) / 2;

                // Draw cropped and centered square from video
                ctx.drawImage(
                    this.video,
                    cropX, cropY, minDimension, minDimension, // source rectangle (crop from center)
                    0, 0, minDimension, minDimension // destination rectangle (full canvas)
                );

                // Tambah overlay informasi
                const now = new Date();
                const timestampText = now.toLocaleString('id-ID');
                const locationText = 'SMKN 1 Pacitan - SimPraPKL';
                const userText = '{{ auth()->user()->name }}';

                // Background overlay - adjust for square canvas
                const overlayHeight = Math.min(80, minDimension * 0.2);
                const overlayWidth = Math.min(400, minDimension - 20);

                ctx.fillStyle = 'rgba(0, 0, 0, 0.8)';
                ctx.fillRect(10, minDimension - overlayHeight - 10, overlayWidth, overlayHeight);

                // Text styling - adjust font size for smaller canvas if needed
                const fontSize = Math.max(12, Math.min(14, minDimension / 50));
                ctx.fillStyle = 'white';
                ctx.font = `bold ${fontSize}px Arial`;
                ctx.textAlign = 'left';

                // Draw overlay text
                const textY = minDimension - overlayHeight + 15;
                const lineHeight = fontSize + 6;
                ctx.fillText(timestampText, 20, textY);
                ctx.fillText(locationText, 20, textY + lineHeight);
                ctx.fillText(`User: ${userText}`, 20, textY + (lineHeight * 2));

                // Convert ke base64
                this.capturedImage = this.canvas.toDataURL('image/jpeg', 0.8);

                // Show preview
                this.preview.src = this.capturedImage;
                this.previewContainer.style.display = 'block';
                this.form.style.display = 'block';

                this.updateStatus('Foto berhasil diambil - Pilih jenis presensi dan submit', 'ready');
            }

            stopCamera() {
                // Stop camera stream
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.stream = null;
                    this.video.srcObject = null;
                }

                // Hide elements
                this.videoContainer.style.display = 'none';
                this.form.style.display = 'none';
                this.previewContainer.style.display = 'none';

                // Hide flip button
                if (this.flipBtn) {
                    this.flipBtn.style.display = 'none';
                }

                // Reset button states
                this.startBtn.disabled = false;
                this.captureBtn.disabled = true;
                this.stopBtn.disabled = true;

                // Reset camera facing mode to default
                this.currentFacingMode = 'user';

                this.updateStatus('Kamera dimatikan', 'ready');
                this.resetForm();
            }

            updateStatus(message, type) {
                this.status.textContent = message;
                this.status.className = `status-indicator status-${type}`;
            }

            async submitPresensi() {
                console.log('=== DEBUG SUBMIT PRESENSI START ===');

                // Validasi foto
                if (!this.capturedImage) {
                    console.error('No captured image');
                    this.updateStatus('Belum ada foto yang diambil', 'error');
                    return;
                }

                const presensiType = this.presensiType.value;
                const keterangan = document.getElementById('keterangan').value;

                console.log('Form data:', {
                    presensiType,
                    keterangan,
                    keteranganLength: keterangan.length,
                    imageDataLength: this.capturedImage.length,
                    imageDataStart: this.capturedImage.substring(0, 100)
                });

                // Validasi jenis presensi
                if (!presensiType) {
                    console.error('No presensi type selected');
                    this.updateStatus('Pilih jenis presensi terlebih dahulu', 'error');
                    return;
                }

                // Validasi keterangan untuk izin/sakit
                if ((presensiType === 'izin' || presensiType === 'sakit') && keterangan.length < 10) {
                    console.error('Keterangan too short for izin/sakit');
                    this.updateStatus('Keterangan minimal 10 karakter untuk izin/sakit', 'error');
                    return;
                }

                this.updateStatus('Mengirim presensi...', 'warning');

                try {
                    // Prepare payload - SIMPLE VERSION for debugging
                    const payload = {
                        image_data: this.capturedImage, // Send as-is first
                        jenis: presensiType,
                        keterangan: keterangan || ''
                    };

                    console.log('Payload prepared:', {
                        jenis: payload.jenis,
                        keterangan: payload.keterangan,
                        imageDataType: typeof payload.image_data,
                        imageDataLength: payload.image_data.length,
                        imageDataPrefix: payload.image_data.substring(0, 50) + '...'
                    });

                    // Send request
                    console.log('Sending fetch request...');
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

                    console.log('Response received:', {
                        status: response.status,
                        statusText: response.statusText,
                        headers: Object.fromEntries(response.headers.entries())
                    });

                    // Parse response
                    const contentType = response.headers.get('content-type');
                    console.log('Content type:', contentType);

                    let result;
                    if (contentType && contentType.includes('application/json')) {
                        result = await response.json();
                        console.log('JSON response:', result);
                    } else {
                        const text = await response.text();
                        console.error('Non-JSON response:', text);
                        console.error('Response text length:', text.length);

                        // Try to extract error from HTML if it's Laravel error page
                        const match = text.match(/<title>(.*?)<\/title>/i);
                        const title = match ? match[1] : 'Unknown error';
                        throw new Error(`Server returned HTML: ${title}`);
                    }

                    if (response.ok && result.success) {
                        console.log('Success!', result.message);
                        this.updateStatus('✅ ' + result.message, 'ready');
                        this.resetForm();
                        this.stopCamera();
                        this.showAlert('success', result.message);

                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);

                    } else {
                        console.error('Server error:', result);
                        throw new Error(result.message || `Server error: ${response.status} ${response.statusText}`);
                    }

                } catch (error) {
                    console.error('=== SUBMIT ERROR ===', error);
                    console.error('Error type:', error.constructor.name);
                    console.error('Error message:', error.message);
                    console.error('Error stack:', error.stack);

                    this.updateStatus('❌ Gagal mengirim presensi: ' + error.message, 'error');
                }

                console.log('=== DEBUG SUBMIT PRESENSI END ===');
            }

            resetForm() {
                this.form.reset();
                this.previewContainer.style.display = 'none';
                this.keteranganGroup.style.display = 'none';
                this.capturedImage = null;
            }

            showAlert(type, message) {
                // Create alert element
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
                alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

                // Insert after page title
                const titleBox = document.querySelector('.page-title-box').parentElement.parentElement;
                titleBox.insertAdjacentElement('afterend', alertDiv);

                // Auto dismiss after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentElement) {
                        alertDiv.remove();
                    }
                }, 5000);
            }
        }

        // Initialize camera when DOM loaded
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM loaded, initializing camera...');
            new PresensiCamera();
        });

        // Function to show image in modal
        function showImage(src) {
            document.getElementById('modalImage').src = src;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }

        // Handle page visibility change
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                console.log('Page hidden - camera still running');
            } else {
                console.log('Page visible - camera active');
            }
        });
    </script>
@endsection
