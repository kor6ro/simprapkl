@props(['id' => 'alpaChangeModal'])

<div class="modal fade" id="{{ $id }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">✍️ Ajukan Perubahan Status Alpa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('presensi.request.approval.date') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="alert alert-warning">Anda akan mengajukan perubahan status untuk tanggal yang sudah
                        ditandai <strong>Alpa</strong>. Isi form di bawah ini dengan lengkap.</div>
                    <input type="hidden" name="tanggal_presensi" id="alpa_tanggal_presensi-{{ $id }}">
                    <div class="mb-3">
                        <label for="alpa_requested_status-{{ $id }}" class="form-label">Ubah Status
                            Menjadi:</label>
                        <select name="requested_status" id="alpa_requested_status-{{ $id }}"
                            class="form-select" required>
                            <option value="Izin Terencana">Izin Terencana</option>
                            <option value="Izin Mendesak">Izin Mendesak</option>
                            <option value="Sakit">Sakit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="alpa_keterangan-{{ $id }}" class="form-label">Keterangan/Alasan</label>
                        <textarea name="keterangan" id="alpa_keterangan-{{ $id }}" class="form-control" rows="3"
                            placeholder="Jelaskan alasan pengajuan perubahan status (minimal 20 karakter)" required minlength="20"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="alpa_bukti_foto-{{ $id }}" class="form-label">Upload Bukti (Surat
                            Izin/Sakit)</label>
                        <input type="file" name="bukti_foto" id="alpa_bukti_foto-{{ $id }}"
                            class="form-control" accept="image/*">
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">> Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Kirim
                            Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alpaChangeModal = document.getElementById('{{ $id }}');
            if (alpaChangeModal) {
                alpaChangeModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const tanggalInput = alpaChangeModal.querySelector(
                        '#alpa_tanggal_presensi-{{ $id }}');
                    if (button && tanggalInput) {
                        tanggalInput.value = button.getAttribute('data-tanggal');
                    }
                });
            }
        });
    </script>
@endpush
