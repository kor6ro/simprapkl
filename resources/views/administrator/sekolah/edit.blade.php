@extends('layout.main')
@section('css')
    <style>
    
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Sekolah</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.sekolah.index') }}">Sekolah</a>
                        </li>
                        <li class="breadcrumb-item active">Edit Sekolah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Edit Sekolah</h4>

            <form action="{{ route('admin.sekolah.update', $sekolah->id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                     <!-- Form Nama Sekolah -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nama_sekolah" class="form-label">Nama Sekolah</label>
                            <input type="text" name="nama_sekolah" id="nama_sekolah"
                                class="form-control @error('nama_sekolah') is-invalid @enderror"
                                value="{{ old('nama_sekolah', $data->nama_sekolah ?? '') }}"
                                placeholder="Masukkan nama sekolah">
                                    
                            @error('nama_sekolah')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <!-- Form Upload Foto -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="logo" class="form-label">Upload Foto</label>
                            <input class="form-control @error('logo') is-invalid @enderror" type="file"
                                name="logo" id="logo" accept="image/*" style="margin-bottom: 0.5rem;">
                            @error('logo')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="button-navigate mt-3">
                    <a href="{{ route('admin.sekolah.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('js')
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const namaInput = document.getElementById('nama');
            if (!namaInput.value.trim()) {
                e.preventDefault();
                namaInput.focus();
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Nama sekolah harus diisi!',
                });
            }
        });
    </script>
@endsection
