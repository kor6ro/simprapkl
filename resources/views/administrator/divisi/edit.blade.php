@extends('layout.main') @section('css')
    <style>
    </style>
@endsection
@section('content')
    {{-- Judul Halaman dan Breadcrumb --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Edit Divisi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.divisi.index') }}">Divisi</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Kartu Form Utama --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.divisi.update', $divisi->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nama_divisi" class="form-label">Nama Divisi <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="nama_divisi" id="nama_divisi"
                                value="{{ old('nama_divisi', $divisi->nama_divisi) }}" required>
                            @error('nama_divisi')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="mt-4">
                    <a href="{{ route('admin.divisi.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
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
