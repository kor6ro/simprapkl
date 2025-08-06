@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Selamat datang, {{ auth()->user()->name }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->group_id == 4)
        <div class="container-fluid px-2">
            <div class="text-center mb-4">
                <div class="d-flex flex-column gap-2 d-sm-block">
                    <button class="btn btn-primary btn-lg w-100 w-sm-auto me-sm-2 mb-2 mb-sm-0"
                        onclick="openPresensiModal('checkin', '{{ route('presensi.checkin') }}')">
                        <i class="fas fa-clock me-1"></i> Absen Pagi
                    </button>
                    <button class="btn btn-secondary btn-lg w-100 w-sm-auto me-sm-2 mb-2 mb-sm-0"
                        onclick="openPresensiModal('checkout', '{{ route('presensi.checkout') }}')">
                        <i class="fas fa-clock me-1"></i> Absen Sore
                    </button>
                    <button class="btn btn-warning btn-lg w-100 w-sm-auto"
                        onclick="openPresensiModal('sakit', '{{ route('presensi.sakit') }}')">
                        <i class="fas fa-file-medical me-1"></i> Ajukan Izin / Sakit
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Kamera -->
        <div class="modal fade" id="presensiModal" tabindex="-1" aria-labelledby="presensiModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="presensiModalLabel">Ambil Foto Presensi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="stopCamera()"></button>
                    </div>
                    <div class="modal-body p-2 p-sm-3">
                        <!-- Camera Container -->
                        <div class="camera-container position-relative mb-3" id="cameraContainer">
                            <video id="video" autoplay playsinline muted
                                class="w-100 rounded border camera-video"></video>
                            <div class="camera-overlay">
                                <div class="camera-frame"></div>
                            </div>
                        </div>

                        <!-- Preview Container -->
                        <div class="preview-container mb-3" id="previewContainer" style="display: none;">
                            <img id="preview" class="w-100 rounded border preview-image">
                        </div>

                        <canvas id="canvas" class="d-none"></canvas>

                        <form method="POST" id="presensiForm" enctype="multipart/form-data"
                            onsubmit="return validateAndSubmit()">
                            @csrf
                            <input type="hidden" name="image" id="imageBase64">
                            <input type="hidden" name="jenis" id="izinJenis">

                            <!-- Izin/Sakit Fields -->
                            <div id="izinFields" class="d-none mb-3">
                                <select id="jenisSelect" class="form-select mb-2" onchange="updateJenis(this.value)"
                                    required>
                                    <option value="">Pilih Jenis</option>
                                    <option value="Sakit">Sakit</option>
                                    <option value="Izin">Izin</option>
                                </select>
                                <textarea name="keterangan" id="izinKeterangan" class="form-control" rows="3"
                                    placeholder="Tulis alasan izin atau sakit..." required></textarea>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary btn-lg" id="captureBtn"
                                    onclick="capturePhoto()">
                                    <i class="fas fa-camera me-1"></i> Ambil Foto
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="retakeBtn"
                                    onclick="retakePhoto()" style="display:none;">
                                    <i class="fas fa-redo me-1"></i> Foto Ulang
                                </button>
                                <button type="submit" class="btn btn-success btn-lg" id="submitBtn" style="display:none;">
                                    <i class="fas fa-check me-1"></i> Submit Presensi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .camera-container {
            aspect-ratio: 1;
            max-width: 400px;
            margin: 0 auto;
            overflow: hidden;
        }

        .camera-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .camera-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
        }

        .camera-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            height: 90%;
            border: 2px solid #fff;
            border-radius: 10px;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.3);
        }

        .preview-container {
            aspect-ratio: 1;
            max-width: 400px;
            margin: 0 auto;
            overflow: hidden;
        }

        .preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @media (max-width: 576px) {
            .modal-body {
                padding: 15px;
            }

            .camera-container,
            .preview-container {
                max-width: 100%;
            }
        }

        .btn-lg {
            padding: 12px 24px;
            font-size: 1.1rem;
        }
    </style>
@endsection

@section('js')
    <script>
        let selectedAction = '';
        let formAction = '';
        let stream;
        let isPhotoCaptured = false;

        function openPresensiModal(action, route) {
            selectedAction = action;
            formAction = route;
            document.getElementById('presensiForm').action = route;

            // Reset form state
            resetModalState();

            // Show/hide izin fields
            const izinFields = document.getElementById('izinFields');
            if (action === 'sakit') {
                izinFields.classList.remove('d-none');
                document.getElementById('jenisSelect').required = true;
                document.getElementById('izinKeterangan').required = true;
            } else {
                izinFields.classList.add('d-none');
                document.getElementById('jenisSelect').required = false;
                document.getElementById('izinKeterangan').required = false;
                document.getElementById('izinJenis').value = '';
            }

            // Set modal title
            const titles = {
                'checkin': 'Absen Pagi',
                'checkout': 'Absen Sore',
                'sakit': 'Ajukan Izin/Sakit'
            };
            document.getElementById('presensiModalLabel').textContent = titles[action] || 'Presensi';

            // Start camera
            startCamera();
        }

        function startCamera() {
            navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user', // Front camera for selfie
                        width: {
                            ideal: 640
                        },
                        height: {
                            ideal: 640
                        }
                    }
                })
                .then(s => {
                    stream = s;
                    const video = document.getElementById('video');
                    video.srcObject = stream;

                    // Show modal after camera is ready
                    const modal = new bootstrap.Modal(document.getElementById('presensiModal'));
                    modal.show();
                })
                .catch(err => {
                    console.error('Camera error:', err);
                    alert("Gagal mengakses kamera: " + err.message);
                });
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
        }

        function resetModalState() {
            isPhotoCaptured = false;
            document.getElementById('imageBase64').value = '';
            document.getElementById('cameraContainer').style.display = 'block';
            document.getElementById('previewContainer').style.display = 'none';
            document.getElementById('captureBtn').style.display = 'block';
            document.getElementById('retakeBtn').style.display = 'none';
            document.getElementById('submitBtn').style.display = 'none';
            document.getElementById('jenisSelect').value = '';
            document.getElementById('izinKeterangan').value = '';
            document.getElementById('izinJenis').value = '';
        }

        function updateJenis(val) {
            document.getElementById('izinJenis').value = val;
        }

        function capturePhoto() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const ctx = canvas.getContext('2d');

            // Set canvas to square aspect ratio
            const size = Math.min(video.videoWidth, video.videoHeight);
            canvas.width = size;
            canvas.height = size;

            // Calculate crop position for center crop
            const startX = (video.videoWidth - size) / 2;
            const startY = (video.videoHeight - size) / 2;

            // Draw cropped video frame
            ctx.drawImage(video, startX, startY, size, size, 0, 0, size, size);

            // Add timestamp
            const timestamp = new Date().toLocaleString('id-ID');
            ctx.font = "bold 20px Arial";
            ctx.fillStyle = "rgba(0, 0, 0, 0.7)";
            ctx.fillRect(5, canvas.height - 35, ctx.measureText(timestamp).width + 10, 30);
            ctx.fillStyle = "white";
            ctx.fillText(timestamp, 10, canvas.height - 10);

            // Add user info
            const userName = "{{ auth()->user()->name }}";
            ctx.font = "bold 16px Arial";
            const userTextWidth = ctx.measureText(userName).width;
            ctx.fillStyle = "rgba(0, 0, 0, 0.7)";
            ctx.fillRect(5, 5, userTextWidth + 10, 25);
            ctx.fillStyle = "white";
            ctx.fillText(userName, 10, 25);

            // Convert to base64
            const dataUrl = canvas.toDataURL("image/jpeg", 0.8);
            document.getElementById('imageBase64').value = dataUrl;

            // Show preview
            showPreview(dataUrl);
        }

        function showPreview(dataUrl) {
            document.getElementById('preview').src = dataUrl;
            document.getElementById('cameraContainer').style.display = 'none';
            document.getElementById('previewContainer').style.display = 'block';
            document.getElementById('captureBtn').style.display = 'none';
            document.getElementById('retakeBtn').style.display = 'block';
            document.getElementById('submitBtn').style.display = 'block';
            isPhotoCaptured = true;
        }

        function retakePhoto() {
            document.getElementById('cameraContainer').style.display = 'block';
            document.getElementById('previewContainer').style.display = 'none';
            document.getElementById('captureBtn').style.display = 'block';
            document.getElementById('retakeBtn').style.display = 'none';
            document.getElementById('submitBtn').style.display = 'none';
            document.getElementById('imageBase64').value = '';
            isPhotoCaptured = false;
        }

        function validateAndSubmit() {
            if (!isPhotoCaptured || !document.getElementById('imageBase64').value) {
                alert("Silakan ambil foto terlebih dahulu.");
                return false;
            }

            if (selectedAction === 'sakit') {
                const jenis = document.getElementById('izinJenis').value;
                const keterangan = document.getElementById('izinKeterangan').value.trim();

                if (!jenis) {
                    alert("Pilih jenis izin atau sakit.");
                    return false;
                }

                if (!keterangan) {
                    alert("Masukkan alasan izin atau sakit.");
                    return false;
                }
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mengirim...';

            stopCamera();
            return true;
        }

        // Handle modal close
        document.getElementById('presensiModal').addEventListener('hidden.bs.modal', function() {
            stopCamera();
        });

        // Handle browser back button
        window.addEventListener('beforeunload', function() {
            stopCamera();
        });
    </script>
@endsection
