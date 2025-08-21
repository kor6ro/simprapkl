@extends('layout.main')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Tambah Tim Baru</h4>
    <a href="{{ route('admin.setting_tugas.index') }}" class="btn btn-secondary btn-sm">🔙 Kembali</a>
</div>

<div class="card">
    <div class="card-body">
        <form id="team-form" action="{{ route('admin.setting_tugas.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                {{-- Input Nama Tim sudah dihapus dari sini --}}
                <div class="col-md-3">
                    <label class="form-label">Ketua Tim:</label>
                    <select class="form-select" name="ketua_id" required>
                        <option value="">Pilih Ketua</option>
                        @foreach($availableAdmins as $karyawan)
                            <option value="{{ $karyawan->id }}">{{ $karyawan->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Jenis Tim:</label>
                    <select class="form-select" name="divisi" required>
                        <option value=""> Pilih Jenis</option>
                        <option value="sales">SALES</option>
                        <option value="teknisi">TEKNISI</option>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Anggota Tim:</label>
                    <select id="anggota-select" name="anggota[]" multiple required>
                        @foreach($availableSiswa as $siswa)
                            <option value="{{ $siswa->id }}">{{ $siswa->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100"> Simpan Tim</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
{{-- JavaScript tidak perlu diubah --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
$(document).ready(function() {
    new TomSelect('#anggota-select', { plugins: ['remove_button'], placeholder: 'Pilih anggota tim...' });
    $('#team-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        showLoadingAlert('Menyimpan tim...');
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                showAlert('success', 'Berhasil!', data.message, () => { window.location.href = '{{ route("admin.setting_tugas.index") }}'; });
            } else {
                showAlert('error', 'Oops...', data.message || 'Gagal menyimpan data.');
            }
        })
        .catch(() => { Swal.close(); showAlert('error', 'Error', 'Terjadi kesalahan.'); })
        .finally(() => { submitBtn.disabled = false; });
    });
});
function showAlert(icon, title, text, callback = null) { Swal.fire({ icon, title, text, confirmButtonColor: '#28a745' }).then(() => { if (callback) callback(); }); }
function showLoadingAlert(title) { Swal.fire({ title, allowOutsideClick: false, didOpen: () => Swal.showLoading() }); }
</script>
@endsection
