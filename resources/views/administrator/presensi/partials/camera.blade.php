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
                    <div>{{ ucfirst($statusPresensi['current_session']) }} - {{ auth()->user()->name }}</div>
                </div>
            </div>

            <div class="camera-controls">
                <button id="startCamera" class="btn btn-primary btn-camera">
                    <i class="fas fa-video me-1"></i> Aktifkan Kamera
                </button>
                <button id="capturePhoto" class="btn btn-success btn-camera" disabled>
                    <i class="fas fa-camera me-1"></i> Ambil Foto
                </button>
                <button id="flipCamera" class="btn btn-secondary btn-camera" disabled style="display: none;">
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

    @if (auth()->user()->sekolah && auth()->user()->sekolah->logo)
        <img id="schoolLogo" class="hidden-logo"
            src="{{ asset('uploads/sekolah_logo/' . auth()->user()->sekolah->logo) }}" crossorigin="anonymous"
            alt="Logo Sekolah">
    @endif
</div>
