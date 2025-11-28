@extends('layout.main')

@section('css')
    {{-- Library TomSelect sudah ada, tidak perlu diubah --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Buat Tugas</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.tim.index') }}">Tugas</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Tambah Tim Baru</h4>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.tim.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6"> {{-- Lebarkan kolom agar lebih rapi --}}
                        <label class="form-label">Ketua Tim <span class="text-danger">*</span></label>
                        {{-- [UBAH] Tambahkan 'multiple' dan ubah 'name' menjadi array --}}
                        <select id="ketua-select" name="ketua_ids[]" multiple required>
                            {{-- Hapus option "Pilih Ketua" karena sudah multi-select --}}
                            @foreach ($availableAdmins as $karyawan)
                                {{-- [UBAH] Logika 'old' untuk menangani array --}}
                                <option value="{{ $karyawan->id }}"
                                    {{ in_array($karyawan->id, old('ketua_ids', [])) ? 'selected' : '' }}>
                                    {{ $karyawan->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('ketua_ids')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6"> {{-- Lebarkan kolom --}}
                        <label class="form-label">Jenis Tim <span class="text-danger">*</span></label>
                        <select class="form-select" name="divisi_id" required>
                            <option value="">Pilih Divisi</option>
                            @foreach ($daftarDivisi as $divisi)
                                <option value="{{ $divisi->id }}" {{ old('divisi_id') == $divisi->id ? 'selected' : '' }}>
                                    {{ $divisi->nama_divisi }}</option>
                            @endforeach
                        </select>
                        @error('divisi_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Anggota Tim <span class="text-danger">*</span></label>
                        <select id="anggota-select" name="anggota[]" multiple required>
                            @foreach ($availableSiswa as $siswa)
                                <option value="{{ $siswa->id }}">{{ $siswa->name }}</option>
                            @endforeach
                        </select>
                        @error('anggota')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('admin.tim.index') }}" class="btn btn-secondary"><i
                            class="fa fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan Tim</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        // [TAMBAH] Inisialisasi TomSelect untuk Ketua Tim
        new TomSelect('#ketua-select', {
            plugins: ['remove_button'],
            placeholder: 'Pilih satu atau lebih ketua tim...',
            items: @json(old('ketua_ids', []))
        });

        // Inisialisasi TomSelect untuk Anggota Tim (sudah ada)
        new TomSelect('#anggota-select', {
            plugins: ['remove_button'],
            placeholder: 'Pilih satu atau lebih anggota tim...',
            items: @json(old('anggota', []))
        });
    </script>
@endsection