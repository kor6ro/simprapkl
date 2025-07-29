@extends('layout.main')
@section('css')
    <style>
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Tambah Presensi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('presensi.index') }}">Presensi</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if ($activeSetting)
                        <div class="alert alert-info">
                            <h6><i class="fa fa-info-circle"></i> Setting Presensi Aktif:</h6>
                            <p class="mb-0">
                                <strong>Pagi:</strong>
                                {{ \Carbon\Carbon::parse($activeSetting->pagi_mulai)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($activeSetting->pagi_selesai)->format('H:i') }}<br>
                                <strong>Sore:</strong>
                                {{ \Carbon\Carbon::parse($activeSetting->sore_mulai)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($activeSetting->sore_selesai)->format('H:i') }}
                            </p>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> Tidak ada setting presensi yang aktif. Silakan
                            hubungi admin.
                        </div>
                    @endif

                    <form action="{{ route('presensi.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
                                    @if (isSiswa())
                                        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                                        <input type="text" class="form-control" value="{{ auth()->user()->name }}"
                                            readonly>
                                        <small class="text-muted">Anda hanya dapat input presensi untuk diri sendiri</small>
                                    @else
                                        <select class="form-control" name="user_id" id="user_id" required>
                                            <option value="">Pilih Siswa</option>
                                            @foreach ($user as $u)
                                                <option value="{{ $u->id }}"
                                                    {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                                    {{ $u->name }} (Siswa PKL)
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Hanya jenis presensi manual yang dapat dipilih. Bolos dan
                                            Telat otomatis dari sistem</small>
                                    @endif
                                    @error('user_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="presensi_jenis_id" class="form-label">Jenis Presensi <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" name="presensi_jenis_id" id="presensi_jenis_id" required>
                                        <option value="">Pilih Jenis Presensi</option>
                                        @foreach ($jenisPresensi as $jp)
                                            @if (!in_array(strtolower($jp->nama), ['bolos', 'telat']))
                                                <option value="{{ $jp->id }}"
                                                    data-butuh-bukti="{{ $jp->butuh_bukti ? 'true' : 'false' }}"
                                                    data-nama="{{ strtolower($jp->nama) }}"
                                                    {{ old('presensi_jenis_id') == $jp->id ? 'selected' : '' }}>
                                                    {{ $jp->nama }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('presensi_jenis_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sesi" class="form-label">Sesi <span class="text-danger">*</span></label>
                                    <select class="form-control" name="sesi" id="sesi" required>
                                        <option value="">Pilih Sesi</option>
                                        <option value="pagi" {{ old('sesi') == 'pagi' ? 'selected' : '' }}>Pagi</option>
                                        <option value="sore" {{ old('sesi') == 'sore' ? 'selected' : '' }}>Sore</option>
                                    </select>
                                    @error('sesi')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bukti" class="form-label">Bukti <span class="text-danger"
                                            id="bukti-required">*</span></label>
                                    <input type="file" class="form-control" name="bukti" id="bukti"
                                        accept="image/jpeg,image/png,image/gif,image/jpg">
                                    <small class="text-muted" id="bukti-help">Upload bukti (JPG, PNG, GIF, max 2MB) <span
                                            class="text-danger">*Wajib</span></small>
                                    @error('bukti')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan <span class="text-danger"
                                    id="keterangan-required" style="display: none;">*</span></label>
                            <textarea class="form-control" name="keterangan" id="keterangan" rows="3" placeholder="Masukkan keterangan...">{{ old('keterangan') }}</textarea>
                            <small class="text-muted" id="keterangan-help">Keterangan tambahan</small>
                            @error('keterangan')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('presensi.index') }}" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary" {{ !$activeSetting ? 'disabled' : '' }}>
                                <i class="fa fa-save me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Trigger change event on page load if there's old value
            if ($('#presensi_jenis_id').val()) {
                $('#presensi_jenis_id').trigger('change');
            }
        });

        // Handle bukti requirement and keterangan requirement based on jenis presensi
        $('#presensi_jenis_id').change(function() {
            var selectedOption = $(this).find('option:selected');
            var butuhBukti = selectedOption.data('butuh-bukti');
            var namaJenis = selectedOption.data('nama');

            // Bukti selalu ditampilkan, tapi required berdasarkan jenis presensi
            if (butuhBukti === 'true' || butuhBukti === true) {
                $('#bukti').prop('required', true);
                $('#bukti-required').show();
                $('#bukti-help').html(
                    'Upload bukti (JPG, PNG, GIF, max 2MB) <span class="text-danger">*Wajib</span>');
            } else {
                $('#bukti').prop('required', false);
                $('#bukti-required').hide();
                $('#bukti-help').html('Upload bukti jika diperlukan (JPG, PNG, GIF, max 2MB)');
            }

            // Keterangan wajib hanya untuk sakit dan izin
            if (namaJenis === 'sakit' || namaJenis === 'izin') {
                $('#keterangan').prop('required', true);
                $('#keterangan-required').show();
                $('#keterangan-help').html(
                    'Keterangan wajib diisi untuk jenis presensi ini <span class="text-danger">*Wajib</span>');
                $('#keterangan').attr('placeholder', 'Masukkan keterangan (wajib)...');
            } else {
                $('#keterangan').prop('required', false);
                $('#keterangan-required').hide();
                $('#keterangan-help').html('Keterangan tambahan (opsional)');
                $('#keterangan').attr('placeholder', 'Masukkan keterangan...');
            }
        });

        // Form validation before submit
        $('form').on('submit', function(e) {
            var isValid = true;
            var errorMessages = [];

            // Check required fields based on form state
            if (!$('#user_id').val() && $('#user_id').is(':visible')) {
                isValid = false;
                errorMessages.push('User wajib dipilih');
                $('#user_id').addClass('is-invalid');
            }

            if (!$('#presensi_jenis_id').val()) {
                isValid = false;
                errorMessages.push('Jenis Presensi wajib dipilih');
                $('#presensi_jenis_id').addClass('is-invalid');
            }

            if (!$('#sesi').val()) {
                isValid = false;
                errorMessages.push('Sesi wajib dipilih');
                $('#sesi').addClass('is-invalid');
            }

            // Check bukti based on jenis presensi requirement
            var selectedOption = $('#presensi_jenis_id').find('option:selected');
            var butuhBukti = selectedOption.data('butuh-bukti');
            var namaJenis = selectedOption.data('nama');

            if (butuhBukti === 'true' && !$('#bukti').val()) {
                isValid = false;
                errorMessages.push('Bukti wajib diupload untuk jenis presensi "' + selectedOption.text() + '"');
                $('#bukti').addClass('is-invalid');
            }

            // Check keterangan for sakit and izin
            if ((namaJenis === 'sakit' || namaJenis === 'izin') && !$('#keterangan').val().trim()) {
                isValid = false;
                errorMessages.push('Keterangan wajib diisi untuk jenis presensi "' + selectedOption.text() + '"');
                $('#keterangan').addClass('is-invalid');
            }

            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi field yang wajib diisi:\n\n' + errorMessages.join('\n'));
                return false;
            }

            // Additional file validation
            var fileInput = $('#bukti')[0];
            if (fileInput.files.length > 0) {
                var file = fileInput.files[0];
                var maxSize = 2 * 1024 * 1024; // 2MB
                var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];

                if (file.size > maxSize) {
                    e.preventDefault();
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                    $('#bukti').addClass('is-invalid');
                    return false;
                }

                if (!allowedTypes.includes(file.type)) {
                    e.preventDefault();
                    alert('Tipe file tidak didukung. Hanya JPG, PNG, dan GIF yang diperbolehkan.');
                    $('#bukti').addClass('is-invalid');
                    return false;
                }
            }
        });

        // Remove invalid class when user starts typing/selecting
        $('input, select, textarea').on('input change', function() {
            $(this).removeClass('is-invalid');
        });
    </script>
@endsection
