@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Tambah Penilaian</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.penilaian.index') }}">Penilaian</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary">
        <div class="card-body">
            <form action="{{ route('admin.penilaian.store') }}" method="post">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="siswa-select" class="form-label">Siswa <span class="text-danger">*</span></label>
                        <select class="form-control" name="siswa_id" id="siswa-select" required>
                            <option value="">-- Pilih Siswa --</option>
                            @foreach ($siswas as $siswa)
                                <option value="{{ $siswa->id }}" {{ old('siswa_id') == $siswa->id ? 'selected' : '' }}>
                                    {{ $siswa->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Penilai</label>
                        <input class="form-control" type="text" value="{{ Auth::user()->name }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label for="tanggal_penilaian" class="form-label">Tanggal Penilaian <span
                                class="text-danger">*</span></label>
                        <input class="form-control" type="date" name="tanggal_penilaian"
                            value="{{ old('tanggal_penilaian', date('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="tanggal-mulai-pkl" class="form-label">Tanggal Mulai PKL</label>
                        <input type="date" class="form-control" id="tanggal-mulai-pkl" name="pkl_tanggal_mulai"
                            value="{{ old('pkl_tanggal_mulai') }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal-selesai-pkl" class="form-label">Tanggal Selesai PKL</label>
                        <input type="date" class="form-control" id="tanggal-selesai-pkl" name="pkl_tanggal_selesai"
                            value="{{ old('pkl_tanggal_selesai') }}" readonly>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3">Rincian Skor (0-100)</h5>
                @foreach ($kriteria as $item)
                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label">{{ $item->nama_variabel }}</label>
                        <div class="col-md-8">
                            <input class="form-control" type="number" name="nilai[{{ $item->kode_variabel }}]"
                                value="{{ old('nilai.' . $item->kode_variabel) }}" min="0" max="100"
                                placeholder="0-100" required>
                        </div>
                    </div>
                @endforeach

                <hr>
                <div class="mb-3">
                    <label for="komentar_saran" class="form-label">Komentar/Saran Akhir</label>
                    <textarea name="komentar_saran" class="form-control" rows="3">{{ old('komentar_saran') }}</textarea>
                </div>

                <div class="button-navigate mt-4">
                    <a href="{{ route('admin.penilaian.index') }}" class="btn btn-secondary"><i
                            class="fa fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#siswa-select').on('change', function() {
                let siswaId = $(this).val();
                let mulaiInput = $('#tanggal-mulai-pkl');
                let selesaiInput = $('#tanggal-selesai-pkl');

                if (!siswaId) {
                    mulaiInput.val('').attr('readonly', true);
                    selesaiInput.val('').attr('readonly', true);
                    return;
                }

                let url = "{{ route('admin.penilaian.get_periode', ['user' => ':id']) }}";
                url = url.replace(':id', siswaId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    beforeSend: function() {
                        mulaiInput.val('Memuat...');
                        selesaiInput.val('Memuat...');
                    },
                    success: function(response) {
                        if (response.success) {
                            mulaiInput.val(response.awal_periode);
                            selesaiInput.val(response.akhir_periode);
                        } else {
                            mulaiInput.val('').attr('readonly', false);
                            selesaiInput.val('').attr('readonly', false);
                            Swal.fire({
                                icon: 'warning',
                                title: 'Peringatan',
                                text: 'Siswa ini tidak terdaftar dalam periode PKL manapun. Silakan isi tanggal manual.'
                            });
                        }
                    },
                    error: function() {
                        mulaiInput.val('').attr('readonly', false);
                        selesaiInput.val('').attr('readonly', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal mengambil data periode dari server.'
                        });
                    }
                });
            });

            // Memicu event change jika ada data lama (misalnya saat terjadi error validasi)
            if ($('#siswa-select').val()) {
                $('#siswa-select').trigger('change');
            }
        });
    </script>
@endsection
