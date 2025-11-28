@props(['statusPresensi', 'id' => 'presensiModal'])

<div class="modal fade" id="{{ $id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📸 Presensi Kamera</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                {{-- Container Utama untuk Kamera --}}
                <div class="camera-wrapper mx-auto mb-3"
                    style="width: 100%; max-width: 480px; aspect-ratio: 1 / 1; position: relative;">

                    {{-- Video Stream --}}
                    <video id="video" autoplay muted playsinline
                        style="width: 100%; height: 100%; border-radius: 8px; object-fit: cover; transform: scaleX(-1);"></video>

                    {{-- Canvas untuk preview, awalnya disembunyikan --}}
                    <canvas id="canvas" class="d-none"
                        style="width: 100%; height: 100%; border-radius: 8px;"></canvas>

                    {{-- Placeholder saat kamera tidak aktif --}}
                    <div id="cameraPlaceholder" class="placeholder-wrapper">
                        <i class="fas fa-camera fa-3x"></i>
                        <p id="cameraStatusText" class="mt-2 mb-0">Klik "Aktifkan Kamera"</p>
                    </div>

                    {{-- Kontainer Overlay Tunggal --}}
                    <div id="liveOverlayContainer" class="live-overlay-container">

                        {{-- Overlay Logo --}}
                        @if (auth()->user()->sekolah && auth()->user()->sekolah->logo)
                            <img id="liveLogoOverlay"
                                src="{{ asset('uploads/sekolah_logo/' . auth()->user()->sekolah->logo) }}"
                                crossorigin="anonymous" alt="Logo Sekolah" class="live-logo-overlay">
                        @endif

                        {{-- Overlay Info Teks --}}
                        <div id="cameraOverlay" class="camera-overlay-info">
                            <div id="timestamp" class="timestamp">Memuat...</div>
                            <div class="user-info">
                                User: {{ auth()->user()->name }}
                            </div>
                            <div class="user-info user-session">
                                Sesi: {{ $statusPresensi['current_session'] ?? 'Auto' }}
                            </div>
                            {{-- Tampilan Lokasi Live --}}
                            <div class="user-info user-location" style="font-size: 0.68rem; line-height: 1.2;">
                                Lokasi: <span id="locationText">...</span>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Form dan Tombol Kontrol --}}
                <form id="presensiForm">
                    <input type="hidden" id="imageData" name="image_data">
                    {{-- Input Latitude & Longitude --}}
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">

                    {{-- Grup Tombol --}}
                    <div class="d-grid gap-2 mt-3">
                        <button type="button" id="startCameraBtn" class="btn btn-primary btn-lg"><i
                                class="fas fa-video me-2"></i>Aktifkan Kamera</button>
                        <button type="button" id="captureBtn" class="btn btn-success btn-lg d-none"><i
                                class="fas fa-camera me-2"></i>Ambil Foto</button>

                        {{-- Wadah untuk tombol setelah foto diambil --}}
                        <div id="afterCaptureControls" class="d-none">
                            <button type="button" id="retakeBtn" class="btn btn-secondary btn-lg w-100 mb-2"><i
                                    class="fas fa-sync-alt me-2"></i>Ambil Ulang</button>
                            <button type="submit" id="submitBtn" class="btn btn-danger btn-lg w-100"><i
                                    class="fas fa-paper-plane me-2"></i>Kirim Presensi</button>
                        </div>
                    </div>
                </form>

                {{-- Logo sekolah untuk digambar di canvas, disembunyikan dari tampilan --}}
                @if (auth()->user()->sekolah && auth()->user()->sekolah->logo)
                    <img id="schoolLogo" src="{{ asset('uploads/sekolah_logo/' . auth()->user()->sekolah->logo) }}"
                        crossorigin="anonymous" alt="Logo Sekolah" style="display:none;">
                @endif
            </div>
        </div>
    </div>
</div>

{{-- CSS untuk styling dan rasio 1:1 --}}
<style>
    .camera-wrapper {
        background-color: #e9ecef;
        border-radius: 8px;
        overflow: hidden;
    }

    .placeholder-wrapper {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: #6c757d;
        position: absolute;
        top: 0;
        left: 0;
        z-index: 1;
    }

    /* Kontainer overlay utama */
    .live-overlay-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 10;
        opacity: 0;
        /* <-- [PERUBAHAN 1] Diubah ke 0 agar tersembunyi default */
        transition: opacity 0.3s;
    }

    /* Style untuk logo live */
    .live-logo-overlay {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 70px;
        height: 70px;
        object-fit: contain;
    }

    /* Info overlay */
    .camera-overlay-info {
        position: absolute;
        bottom: 10px;
        left: 10px;
        color: white;
        padding: 7px 11px;
        border-radius: 5px;
        font-size: 0.78rem;
        text-align: left;
        min-width: 210px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
    }

    .timestamp {
        font-weight: bold;
        font-size: 0.82rem;
        margin-bottom: 3px;
        line-height: 1.2;
    }

    .user-info {
        font-size: 0.72rem;
        margin-bottom: 2px;
        line-height: 1.2;
    }

    .user-session {
        font-size: 0.68rem;
        line-height: 1.2;
    }
</style>


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalId = '{{ $id }}';
            const modal = document.getElementById(modalId);
            if (!modal) return;

            // Elemen UI
            const video = modal.querySelector('#video');
            const canvas = modal.querySelector('#canvas');
            const placeholder = modal.querySelector('#cameraPlaceholder');
            const statusText = modal.querySelector('#cameraStatusText');
            const timestampEl = modal.querySelector('#timestamp');
            const schoolLogo = modal.querySelector('#schoolLogo');
            const liveOverlayContainer = modal.querySelector('#liveOverlayContainer');

            // Tombol
            const startBtn = modal.querySelector('#startCameraBtn');
            const captureBtn = modal.querySelector('#captureBtn');
            const afterCaptureControls = modal.querySelector('#afterCaptureControls');
            const retakeBtn = modal.querySelector('#retakeBtn');
            const submitBtn = modal.querySelector('#submitBtn');

            // Form
            const form = modal.querySelector('#presensiForm');
            const imageDataInput = modal.querySelector('#imageData');
            const latitudeInput = modal.querySelector('#latitude');
            const longitudeInput = modal.querySelector('#longitude');
            const locationTextEl = modal.querySelector('#locationText');


            let stream = null;
            let timestampInterval = null;

            /**
             * Fungsi untuk menggambar rounded rectangle
             */
            function drawRoundedRect(ctx, x, y, width, height, radius) {
                ctx.beginPath();
                ctx.moveTo(x + radius, y);
                ctx.lineTo(x + width - radius, y);
                ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
                ctx.lineTo(x + width, y + height - radius);
                ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
                ctx.lineTo(x + radius, y + height);
                ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
                ctx.lineTo(x, y + radius);
                ctx.quadraticCurveTo(x, y, x + radius, y);
                ctx.closePath();
                ctx.fill();
            }

            // Fungsi untuk Deteksi Lokasi & Reverse Geocoding
            const getLocation = () => {
                if (navigator.geolocation) {
                    if (locationTextEl) locationTextEl.textContent = 'Mendeteksi...';

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lon = position.coords.longitude;

                            latitudeInput.value = lat;
                            longitudeInput.value = lon;

                            // Reverse geocoding dengan detail maksimal
                            fetch(
                                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1&zoom=18&accept-language=id`
                                )
                                .then(response => response.json())
                                .then(data => {
                                    if (data && data.address) {
                                        const address = data.address;
                                        const parts = [];

                                        // Tambah nomor rumah jika ada
                                        if (address.house_number && address.road) {
                                            parts.push(`${address.road} No. ${address.house_number}`);
                                        } else if (address.road) {
                                            parts.push(address.road);
                                        } else if (address.footway) {
                                            parts.push(address.footway);
                                        } else if (address.path) {
                                            parts.push(address.path);
                                        } else if (address.hamlet) {
                                            parts.push(address.hamlet);
                                        } else if (address.neighbourhood) {
                                            parts.push(address.neighbourhood);
                                        }

                                        // RT/RW jika tersedia (jarang ada di OSM, tapi dicoba)
                                        if (address.locality) parts.push(address.locality);

                                        // Kelurahan / Desa
                                        if (address.village) {
                                            parts.push(`Kel. ${address.village}`);
                                        } else if (address.suburb) {
                                            parts.push(`Kel. ${address.suburb}`);
                                        } else if (address.quarter) {
                                            parts.push(address.quarter);
                                        }

                                        // Kecamatan
                                        if (address.county) {
                                            parts.push(`Kec. ${address.county}`);
                                        } else if (address.municipality) {
                                            parts.push(address.municipality);
                                        } else if (address.city_district) {
                                            parts.push(address.city_district);
                                        }

                                        // Kabupaten / Kota
                                        if (address.city) {
                                            parts.push(address.city);
                                        } else if (address.town) {
                                            parts.push(address.town);
                                        } else if (address.state_district) {
                                            parts.push(address.state_district);
                                        }

                                        // Provinsi
                                        if (address.state) {
                                            parts.push(address.state);
                                        }

                                        // Tambahkan postcode jika ada
                                        if (address.postcode) {
                                            parts.push(address.postcode);
                                        }

                                        const locationString = parts.length > 0 ? parts.join(', ') :
                                            data.display_name || 'Lokasi Terdeteksi';

                                        if (locationTextEl) {
                                            locationTextEl.textContent = locationString;
                                        }

                                        console.log('Full address details:', address);
                                        console.log('Display name:', data.display_name);
                                        console.log('Final location:', locationString);
                                    } else {
                                        if (locationTextEl) {
                                            locationTextEl.textContent =
                                                `${lat.toFixed(6)}, ${lon.toFixed(6)}`;
                                        }
                                    }
                                })
                                .catch(err => {
                                    console.warn('Reverse geocoding failed:', err);
                                    if (locationTextEl) {
                                        locationTextEl.textContent =
                                            `${lat.toFixed(6)}, ${lon.toFixed(6)}`;
                                    }
                                });
                        },
                        (error) => {
                            let errorMsg = 'Gagal deteksi lokasi.';
                            switch (error.code) {
                                case error.PERMISSION_DENIED:
                                    errorMsg = "Izin lokasi ditolak.";
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    errorMsg = "Info lokasi tidak tersedia.";
                                    break;
                                case error.TIMEOUT:
                                    errorMsg = "Waktu deteksi lokasi habis.";
                                    break;
                            }
                            if (locationTextEl) locationTextEl.textContent = errorMsg;
                            latitudeInput.value = 'error';
                            longitudeInput.value = errorMsg;
                        }, {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                } else {
                    if (locationTextEl) locationTextEl.textContent = 'Geolocation tidak didukung.';
                }
            };

            const updateTimestamp = () => {
                if (timestampEl) {
                    const now = new Date();
                    // [PERUBAHAN] Menambahkan hari
                    timestampEl.textContent = now.toLocaleString('id-ID', {
                        weekday: 'long', // Tambahan hari
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false
                    });
                }
            };

            const startCamera = async () => {
                getLocation();
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user',
                            width: {
                                ideal: 720
                            },
                            height: {
                                ideal: 720
                            },
                            aspectRatio: 1.0
                        }
                    });
                    video.srcObject = stream;
                    video.classList.remove('d-none');
                    placeholder.classList.add('d-none');
                    startBtn.classList.add('d-none');
                    captureBtn.classList.remove('d-none');
                    timestampInterval = setInterval(updateTimestamp, 1000);

                    // [PERUBAHAN 2] Tampilkan overlay saat kamera aktif
                    if (liveOverlayContainer) liveOverlayContainer.style.opacity = '1';

                } catch (err) {
                    statusText.textContent = `Error: ${err.name}. Pastikan izin kamera diberikan.`;
                    console.error("Error starting camera:", err);
                }
            };

            const stopCamera = () => {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
                clearInterval(timestampInterval);
                timestampInterval = null;
            };

            const capturePhoto = () => {
                const ctx = canvas.getContext('2d');

                const minDimension = Math.min(video.videoWidth, video.videoHeight);
                canvas.width = minDimension;
                canvas.height = minDimension;
                const cropX = (video.videoWidth - minDimension) / 2;
                const cropY = (video.videoHeight - minDimension) / 2;

                // Mirror canvas seperti preview video
                ctx.save();
                ctx.scale(-1, 1);
                ctx.drawImage(video, cropX, cropY, minDimension, minDimension, -minDimension, 0, minDimension,
                    minDimension);
                ctx.restore();

                // Hitung skala proporsional
                const scaleFactor = minDimension / 480;

                const now = new Date();
                // [PERUBAHAN] Menambahkan hari
                const timestampText = now.toLocaleString('id-ID', {
                    weekday: 'long', // Tambahan hari
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
                const userText = '{{ auth()->user()->name }}';
                const sessionText = 'Sesi: {{ $statusPresensi['current_session'] ?? 'Auto' }}';
                const locationText = locationTextEl ? locationTextEl.textContent : 'Lokasi tidak diketahui';

                const paddingX = 20 * scaleFactor;
                const paddingY = minDimension - 85 * scaleFactor;

                // Text shadow untuk keterbacaan tanpa background
                ctx.shadowColor = 'rgba(0, 0, 0, 0.8)';
                ctx.shadowBlur = 4 * scaleFactor;
                ctx.shadowOffsetX = 2 * scaleFactor;
                ctx.shadowOffsetY = 2 * scaleFactor;

                ctx.fillStyle = 'white';
                ctx.font = `bold ${16 * scaleFactor}px Arial`;
                ctx.fillText(timestampText, paddingX, paddingY);

                ctx.font = `${14 * scaleFactor}px Arial`;
                ctx.fillText(`User: ${userText}`, paddingX, paddingY + (22 * scaleFactor));

                ctx.font = `${13 * scaleFactor}px Arial`;
                ctx.fillText(sessionText, paddingX, paddingY + (44 * scaleFactor));

                ctx.font = `${13 * scaleFactor}px Arial`;
                ctx.fillText(`Lokasi: ${locationText}`, paddingX, paddingY + (66 * scaleFactor));

                // Reset shadow
                ctx.shadowColor = 'transparent';
                ctx.shadowBlur = 0;
                ctx.shadowOffsetX = 0;
                ctx.shadowOffsetY = 0;

                // Gambar logo di posisi kanan atas
                if (schoolLogo && schoolLogo.complete) {
                    const logoSize = 100 * scaleFactor;
                    const logoMargin = 15 * scaleFactor;
                    ctx.drawImage(schoolLogo, minDimension - logoSize - logoMargin, logoMargin, logoSize,
                        logoSize);
                }

                imageDataInput.value = canvas.toDataURL('image/jpeg', 0.85);

                video.classList.add('d-none');
                canvas.classList.remove('d-none');
                captureBtn.classList.add('d-none');
                afterCaptureControls.classList.remove('d-none');

                // Sembunyikan overlay
                if (liveOverlayContainer) liveOverlayContainer.style.opacity = '0';
            };

            const retakePhoto = () => {
                canvas.classList.add('d-none');
                video.classList.remove('d-none');
                imageDataInput.value = '';
                afterCaptureControls.classList.add('d-none');
                captureBtn.classList.remove('d-none');

                // Tampilkan kembali overlay
                if (liveOverlayContainer) liveOverlayContainer.style.opacity = '1';
            };

            const resetModalState = () => {
                stopCamera();
                video.classList.add('d-none');
                canvas.classList.add('d-none');
                placeholder.classList.remove('d-none');
                startBtn.classList.remove('d-none');
                captureBtn.classList.add('d-none');
                afterCaptureControls.classList.add('d-none');
                form.reset();
                imageDataInput.value = '';
                latitudeInput.value = '';
                longitudeInput.value = '';
                if (locationTextEl) locationTextEl.textContent = '...';
                statusText.textContent = 'Klik "Aktifkan Kamera"';

                // [PERUBAHAN 3] Reset overlay ke tersembunyi
                if (liveOverlayContainer) liveOverlayContainer.style.opacity = '0';
            };

            startBtn.addEventListener('click', startCamera);
            captureBtn.addEventListener('click', capturePhoto);
            retakeBtn.addEventListener('click', retakePhoto);

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!imageDataInput.value) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Ambil foto terlebih dahulu!',
                    });
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengirim...';

                try {
                    const response = await fetch('{{ route('presensi.camera') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            image_data: imageDataInput.value,
                            latitude: latitudeInput.value,
                            longitude: longitudeInput.value
                        })
                    });
                    const result = await response.json();

                    if (response.ok && result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: result.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });

                    } else {
                        throw new Error(result.message || 'Terjadi kesalahan di server.');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: error.message
                    });

                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Kirim Presensi';
                }
            });

            modal.addEventListener('hidden.bs.modal', resetModalState);

            modal.addEventListener('shown.bs.modal', () => {
                updateTimestamp();
            });
        });
    </script>
@endpush
