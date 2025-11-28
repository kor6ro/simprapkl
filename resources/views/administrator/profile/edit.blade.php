@extends('layout.main')

@section('content')
    {{-- Judul Halaman dan Breadcrumb --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Edit Profil</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('profile.index') }}">Profil</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Kartu Form Utama --}}
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Form Edit Profil</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.save') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12 text-center mb-4">
                                <img src="{{ $profile->photo_profile ? asset('uploads/profiles/' . $profile->photo_profile) : asset('assets/images/placeholder.jpg') }}"
                                    alt="Profile Picture" class="rounded-circle" width="120" height="120"
                                    style="object-fit: cover; border: 4px solid #f1f1f1;">
                            </div>

                            {{-- Setiap field input menggunakan col-md-6 agar otomatis menjadi 1 kolom di mobile --}}
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="name" id="name"
                                    value="{{ old('name', $profile->name) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="username"
                                    value="{{ old('username', $profile->username) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="email"
                                    value="{{ old('email', $profile->email) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="photo_profile" class="form-label">Ganti Foto Profil</label>
                                <input type="file" class="form-control" name="photo_profile" id="photo_profile">
                            </div>
                            <div class="col-12 mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" name="alamat" id="alamat" rows="3">{{ old('alamat', $profile->alamat) }}</textarea>
                            </div>
                        </div>

                        {{-- Tombol Aksi Responsif --}}
                        <div class="border-top pt-3 mt-3">
                            {{-- d-grid untuk mobile, d-md-flex untuk desktop. Tombol jadi full-width di mobile --}}
                            <div class="d-grid d-md-flex gap-2">
                                <a href="{{ route('profile.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-times me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
