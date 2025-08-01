@extends('layout.main')

@section('css')
    <style>
        table th {
            width: 30%;
        }

        video {
            object-fit: cover;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <h4 class="mb-4">Presensi</h4>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Tombol Modal Presensi Kamera -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalPresensiKamera">
            + Ambil Presensi dengan Kamera
        </button>

        <!-- Form Presensi Sakit/Izin -->
        <form action="{{ route('presensi.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <table class="table table-bordered">
                <tr>
                    <th>Nama</th>
                    <td>{{ auth()->user()->name }}</td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td>
                        <input type="date" name="tanggal_presensi" class="form-control" value="{{ date('Y-m-d') }}"
                            readonly>
                    </td>
                </tr>
                <tr>
                    <th>Tidak Dapat Hadir</th>
                    <td>
                        <select name="presensi_status_id" class="form-control" required>
                            <option value="">Pilih Alasan</option>
                            @foreach ($presensistatus as $status)
                                @if (in_array(strtolower($status->status), ['izin', 'sakit']))
                                    <option value="{{ $status->id }}">{{ $status->status }}</option>
                                @endif
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <td>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Isi jika diperlukan..."></textarea>
                    </td>
                </tr>
                <tr>
                    <th>Upload Bukti</th>
                    <td>
                        <input type="file" name="bukti" class="form-control">
                        <small class="text-muted">Format: .jpg, .jpeg, .png, max 2MB</small>
                    </td>
                </tr>
            </table>

            <div class="mt-3">
                <button type="submit" class="btn btn-success">Kirim</button>
                <a href="{{ route('presensi.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>

    <!-- Modal Presensi Kamera -->
    <div class="modal fade" id="modalPresensiKamera" tabindex="-1" aria-labelledby="modalPresensiKameraLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Form Presensi Kamera</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @elseif(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('presensi.store') }}">
                        @csrf

                        <!-- Default status hadir (bisa ubah sesuai ID) -->
                        <input type="hidden" name="presensi_status_id"
                            value="{{ $presensistatus->firstWhere('status', 'hadir')->id ?? 1 }}">

                        <div class="mb-3">
                            <label class="form-label">Preview Webcam</label><br>
                            <video id="video" width="100%" height="240" class="border" autoplay></video>
                            <canvas id="canvas" style="display:none;"></canvas>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" id="snap">Ambil Foto</button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hasil Foto:</label><br>
                            <img id="preview" src="" class="img-thumbnail" style="max-width: 100%; height: auto;">
                        </div>

                        <input type="hidden" name="image" id="image">

                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Masuk pagi ini."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Submit Presensi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Logo sekolah tersembunyi -->
    <img id="logo" src="{{ asset('logo/smkn1pacitan.png') }}" style="display:none;" />
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('modalPresensiKamera');
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const snap = document.getElementById('snap');
            const imageInput = document.getElementById('image');
            const preview = document.getElementById('preview');
            const logo = document.getElementById('logo');

            let stream = null;

            // Saat modal dibuka, aktifkan kamera
            modal.addEventListener('shown.bs.modal', () => {
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({
                            video: true
                        })
                        .then(s => {
                            stream = s;
                            video.srcObject = stream;
                            video.play();
                        })
                        .catch(err => {
                            alert("Gagal mengakses kamera: " + err.message);
                        });
                }
            });

            // Saat modal ditutup, matikan kamera
            modal.addEventListener('hidden.bs.modal', () => {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                    video.srcObject = null;
                    preview.src = '';
                    imageInput.value = '';
                }
            });

            // Ambil foto
            snap.addEventListener('click', () => {
                if (!video.videoWidth || !video.videoHeight) {
                    alert("Kamera belum siap, silakan tunggu beberapa detik.");
                    return;
                }

                // Set ukuran canvas sesuai video
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Tambah timestamp
                const timestamp = new Date().toLocaleString();
                ctx.fillStyle = "white";
                ctx.font = "20px Arial";
                ctx.fillText(`Presensi: ${timestamp}`, 10, canvas.height - 10);

                // Tambah logo
                const logoSize = 50;
                ctx.drawImage(logo, canvas.width - logoSize - 10, 10, logoSize, logoSize);

                // Hasil base64
                const imageData = canvas.toDataURL('image/png');
                imageInput.value = imageData;
                preview.src = imageData;
            });
        });
    </script>
@endsection
