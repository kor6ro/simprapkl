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
