@extends('layout.main')
@section('css')
    <style>
        .card-primary {
            border-top: 3px solid #007bff;
        }

        .form-group {
            margin-bottom: 1rem;
        }
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
                        <li class="breadcrumb-item active">Tambah Sekolah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Tambah Sekolah</h4>

            <form action="{{ route('admin.sekolah.store') }}" method="post">
                @csrf
                <div class="row">
                    <div class="col-xl-6 col-md-8 col-12">
                        <div class="form-group">
                            <label for="nama" class="form-label">Nama Sekolah <span class="text-danger">*</span></label>
                            <input class="form-control @error('nama') is-invalid @enderror" type="text" name="nama"
                                id="nama" value="{{ old('nama') }}" placeholder="Masukkan nama sekolah" required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="button-navigate mt-3">
                    <a href="{{ route('admin.sekolah.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
