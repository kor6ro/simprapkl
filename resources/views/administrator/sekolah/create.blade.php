@extends('layout.main')

@section('content')
    {{-- Judul Halaman dan Breadcrumb --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Tambah Sekolah</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.sekolah.index') }}">Sekolah</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Kartu Form Utama --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.sekolah.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    {{-- Form Nama Sekolah --}}
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nama_sekolah" class="form-label">Nama Sekolah <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="nama_sekolah" id="nama_sekolah"
                                class="form-control @error('nama_sekolah') is-invalid @enderror"
                                value="{{ old('nama_sekolah') }}" placeholder="Masukkan nama sekolah" required>
                            @error('nama_sekolah')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- Form Upload Logo --}}
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo Sekolah</label>
                            <input class="form-control @error('logo') is-invalid @enderror" type="file" name="logo"
                                id="logo" accept="image/*">
                            @error('logo')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="mt-4">
                    <a href="{{ route('admin.sekolah.index') }}" class="btn btn-secondary">
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
