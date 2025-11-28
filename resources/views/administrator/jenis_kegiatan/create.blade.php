@extends('layout.main')

@section('css')
    <style>
        /* CSS kustom dapat ditambahkan di sini jika diperlukan */
    </style>
@endsection

@section('content')
    {{-- Judul Halaman dan Breadcrumb --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Tambah Jenis Kegiatan</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.jenis_kegiatan.index') }}">Jenis Kegiatan</a>
                        </li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Kartu Form Utama --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.jenis_kegiatan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Konten Form --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="nama_kegiatan" id="nama_kegiatan"
                                value="{{ old('nama_kegiatan') }}" required>
                            @error('nama_kegiatan')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="mt-4">
                    <a href="{{ route('admin.jenis_kegiatan.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        // JS kustom dapat ditambahkan di sini jika diperlukan
    </script>
@endsection
