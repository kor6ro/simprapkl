@extends('layout.main')

@section('css')
    {{-- Memuat CSS khusus untuk Tom-Select --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Tambah Periode PKL</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.periode-pkl.index') }}">Periode PKL</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-primary">
        <div class="card-body">
            <form action="{{ route('admin.periode-pkl.store') }}" method="post">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="awal_periode" class="form-label">Awal Periode <span class="text-danger">*</span></label>
                        <input class="form-control" type="date" name="awal_periode" id="awal_periode"
                            value="{{ old('awal_periode') }}" required>
                        @error('awal_periode')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="akhir_periode" class="form-label">Akhir Periode <span
                                class="text-danger">*</span></label>
                        <input class="form-control" type="date" name="akhir_periode" id="akhir_periode"
                            value="{{ old('akhir_periode') }}" required>
                        @error('akhir_periode')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Dropdown untuk Siswa --}}
                    <div class="col-12 mb-3">
                        <label for="select-siswa" class="form-label">Anggota Siswa <span
                                class="text-danger">*</span></label>
                        <select name="siswa_ids[]" id="select-siswa" multiple required>
                            @foreach ($siswas as $siswa)
                                <option value="{{ $siswa->id }}">{{ $siswa->name }}</option>
                            @endforeach
                        </select>
                        @error('siswa_ids')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Dropdown untuk Pembimbing --}}
                    <div class="col-12 mb-3">
                        <label for="select-pembimbing" class="form-label">Anggota Pembimbing <span
                                class="text-danger">*</span></label>
                        <select name="pembimbing_ids[]" id="select-pembimbing" multiple required>
                            @foreach ($pembimbings as $pembimbing)
                                <option value="{{ $pembimbing->id }}">{{ $pembimbing->name }}</option>
                            @endforeach
                        </select>
                        @error('pembimbing_ids')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="button-navigate mt-3">
                    <a href="{{ route('admin.periode-pkl.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    {{-- Memuat JS khusus untuk Tom-Select --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        // Inisialisasi Tom-Select untuk Siswa
        new TomSelect('#select-siswa', {
            plugins: ['remove_button'],
            placeholder: 'Pilih satu atau lebih siswa...',
            // Memuat kembali pilihan lama jika terjadi error validasi
            items: @json(old('siswa_ids', []))
        });

        // Inisialisasi Tom-Select untuk Pembimbing
        new TomSelect('#select-pembimbing', {
            plugins: ['remove_button'],
            placeholder: 'Pilih satu atau lebih pembimbing...',
            items: @json(old('pembimbing_ids', []))
        });
    </script>
@endsection
