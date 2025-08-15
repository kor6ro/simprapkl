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
                this.updateStatus('Gagal mengakses kamera: ' + error.message, 'error');
            }
        }

        async flipCamera() {
            if (!this.isMobile) return;
            try {
                this.currentFacingMode = this.currentFacingMode === 'user' ? 'environment' : 'user';
                if (this.stream) this.stream.getTracks().forEach(track => track.stop());
                await this.startCamera();
            } catch {
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

            const now = new Date();
            const timestampText = now.toLocaleString('id-ID');
            const locationText = '{{ auth()->user()->sekolah->nama ?? 'SMKN 1 Pacitan' }} - SimPraPKL';
            const userText = '{{ auth()->user()->name }}';
            const sessionText = 'Sesi: {{ $statusPresensi['current_session'] ?? 'Auto' }}';
            ctx.fillStyle = 'rgba(0, 0, 0, 0.8)';
            ctx.fillRect(10, minDimension - 120, 400, 110);
            ctx.fillStyle = 'white';
            ctx.font = 'bold 14px Arial';
            ctx.fillText(timestampText, 20, minDimension - 95);
            ctx.fillText(locationText, 20, minDimension - 75);
            ctx.fillText(`User: ${userText}`, 20, minDimension - 55);
            ctx.fillText(sessionText, 20, minDimension - 35);

            if (this.schoolLogo && this.schoolLogo.complete) {
                const logoSize = Math.min(100, minDimension * 0.25);
                ctx.drawImage(this.schoolLogo, minDimension - logoSize - 25, 25, logoSize, logoSize);
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
            if (this.flipBtn) this.flipBtn.disabled = true;
            this.updateStatus('Kamera dimatikan', 'ready');
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
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    throw new Error(result.message || 'Server error');
                }
            } catch (error) {
                this.updateStatus('❌ Gagal: ' + error.message, 'error');
            }
        }

        updateStatus(message, type) {
            if (this.status) {
                this.status.textContent = message;
                this.status.className = `status-indicator status-${type}`;
            }
        }
    }

    let cameraInstance = null;

    document.getElementById('presensiModal').addEventListener('shown.bs.modal', function() {
        cameraInstance = new PresensiCamera();
    });
    document.getElementById('presensiModal').addEventListener('hidden.bs.modal', function() {
        if (cameraInstance) {
            cameraInstance.stopCamera();
            cameraInstance = null;
        }
    });
</script>
