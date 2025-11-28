@props(['id' => 'izinModal'])

<div class="modal fade" id="{{ $id }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">✉️ Form Izin/Sakit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="izin-sakit-form">
                    <p class="mb-3"><strong>Jika Anda tidak dapat hadir karena sakit atau ada keperluan:</strong></p>
                    <form id="formIzinSakit-{{ $id }}" action="{{ route('presensi.submit_absence') }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Jenis Ketidakhadiran</label>
                            <select name="jenis" class="form-select" required id="jenisIzin">
                                <option value="">Pilih Jenis</option>
                                <option value="IZIN_TERENCANA">Izin Terencana</option>
                                <option value="IZIN_MENDESAK">Izin Mendesak</option>
                                <option value="SAKIT">Sakit</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Waktu Izin</label>
                            <select name="durasi" class="form-select" required>
                                <option value="FULL_DAY">Sehari Penuh (Pagi & Siang)</option>
                                <option value="PAGI_ONLY">Pagi Saja</option>
                                <option value="SORE_ONLY">Siang Saja</option>
                            </select>
                        </div>

                        <div id="tanggalWrapper" class="mb-3">
                            <div id="singleDateInput" style="display: none;">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal_single" class="form-control"
                                    value="{{ date('Y-m-d') }}" readonly>
                                <small class="text-muted">Untuk izin mendadak/sakit hanya berlaku hari ini</small>
                            </div>
                            <div id="dateRangeInput" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Mulai</label>
                                        <input type="date" name="tanggal_mulai" class="form-control"
                                            min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Selesai</label>
                                        <input type="date" name="tanggal_selesai" class="form-control"
                                            min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    </div>
                                </div>
                                <small class="text-muted">Izin terencana minimal diajukan H-1</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan (min. 20 karakter)</label>
                            <textarea name="keterangan" class="form-control" rows="3" required minlength="20"
                                placeholder="Jelaskan alasan ketidakhadiran Anda..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bukti (Foto/Surat)</label>
                            <input type="file" name="bukti_foto" class="form-control" accept="image/*" required>
                            <div class="form-text">Format: JPG/PNG, Max: 2MB</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Submit Pengajuan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.getElementById('{{ $id }}');
            if (modalElement) {
                const jenisIzin = modalElement.querySelector('#jenisIzin');
                const singleDateInput = modalElement.querySelector('#singleDateInput');
                const dateRangeInput = modalElement.querySelector('#dateRangeInput');
                const tanggalMulai = modalElement.querySelector('input[name="tanggal_mulai"]');
                const tanggalSelesai = modalElement.querySelector('input[name="tanggal_selesai"]');
                const formIzinSakit = modalElement.querySelector('#formIzinSakit-{{ $id }}');

                jenisIzin.addEventListener('change', function() {
                    if (this.value === 'IZIN_TERENCANA') {
                        singleDateInput.style.display = 'none';
                        dateRangeInput.style.display = 'block';
                    } else {
                        singleDateInput.style.display = 'block';
                        dateRangeInput.style.display = 'none';
                    }
                });

                tanggalMulai.addEventListener('change', function() {
                    // Pastikan tanggal selesai tidak pernah lebih awal dari tanggal mulai
                    tanggalSelesai.min = this.value;
                    if (tanggalSelesai.value && tanggalSelesai.value < this.value) {
                        tanggalSelesai.value = this.value;
                    }
                });

                formIzinSakit.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (!this.checkValidity()) {
                        this.reportValidity();
                        return;
                    }

                    // [PERBAIKAN 1] Validasi frontend yang lebih kuat
                    if (jenisIzin.value === 'IZIN_TERENCANA') {
                        if (!tanggalMulai.value || !tanggalSelesai.value) {
                            Swal.fire('Input Tidak Lengkap',
                                'Untuk Izin Terencana, mohon isi Tanggal Mulai dan Tanggal Selesai.',
                                'error');
                            return;
                        }
                        if (tanggalSelesai.value < tanggalMulai.value) {
                            Swal.fire('Tanggal Tidak Valid',
                                'Tanggal Selesai tidak boleh lebih awal dari Tanggal Mulai.', 'error');
                            return;
                        }
                    }

                    Swal.fire({
                        title: 'Konfirmasi Pengajuan',
                        text: 'Pastikan data yang Anda masukkan sudah benar',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Submit'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const formData = new FormData(formIzinSakit);
                            fetch(formIzinSakit.getAttribute('action'), {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').getAttribute(
                                            'content'),
                                        'Accept': 'application/json', // Penting untuk menerima error JSON
                                    }
                                })
                                // [PERBAIKAN 2] Penanganan error yang lebih baik
                                .then(async response => {
                                    const data = await response.json();
                                    if (!response.ok) {
                                        // Ambil pesan error validasi dari Laravel
                                        let errorMessage = data.message ||
                                            'Terjadi kesalahan.';
                                        if (data.errors) {
                                            errorMessage = Object.values(data.errors).map(
                                                e => e.join('\n')).join('\n');
                                        }
                                        throw new Error(errorMessage);
                                    }
                                    return data;
                                })
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Berhasil!', data.message ||
                                                'Pengajuan Anda berhasil dikirim.', 'success')
                                            .then(() => window.location.reload());
                                    }
                                })
                                .catch(error => {
                                    Swal.fire('Gagal!', error.message, 'error');
                                });
                        }
                    });
                });
            }
        });
    </script>
@endpush
