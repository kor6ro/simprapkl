@extends('layout.main')
@section('css')
    <style>
        .profile-label {
            font-weight: 500;
            color: #6c757d;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-primary">Edit Profile</h4>
                <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Form Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="profile-label">Name</label>
                                <input type="text" class="form-control" name="name" id="name"
                                    value="{{ old('name', $profile->name) }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="username" class="profile-label">Username</label>
                                <input type="text" class="form-control" name="username" id="username"
                                    value="{{ old('username', $profile->username) }}">
                                @error('username')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="profile-label">Email</label>
                                <input type="email" class="form-control" name="email" id="email"
                                    value="{{ old('email', $profile->email) }}">
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="profile-label">Password</label>
                                <input type="password" class="form-control" name="password" id="password"
                                    value="{{ old('password', $profile->password_plain ?? '') }}">
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="validasi" class="profile-label d-block">Validasi</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="validasi" name="validasi"
                                        value="Siswa" {{ old('validasi', $profile->validasi) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="validasi">Ya</label>
                                </div>
                                @error('validasi')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="sekolah_id" class="profile-label">Sekolah</label>
                                <select name="sekolah_id" id="sekolah_id" class="form-select">
                                    <option value="">-- Pilih Sekolah --</option>
                                    @foreach ($sekolah as $val)
                                        <option value="{{ $val->id }}"
                                            {{ old('sekolah_id', $profile->sekolah_id) == $val->id ? 'selected' : '' }}>
                                            {{ $val->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sekolah_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="group_id" class="profile-label">Group</label>
                                <select name="group_id" id="group_id" class="form-select">
                                    <option value="">-- Pilih Group --</option>
                                    @foreach ($group as $val)
                                        <option value="{{ $val->id }}"
                                            {{ old('group_id', $profile->group_id) == $val->id ? 'selected' : '' }}>
                                            {{ $val->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('group_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="alamat" class="profile-label">Alamat</label>
                                <input type="text" class="form-control" name="alamat" id="alamat"
                                    value="{{ old('alamat', $profile->alamat) }}">
                                @error('alamat')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fa fa-save me-1"></i> Simpan
                            </button>
                            <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-times me-1"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        // custom JS jika dibutuhkan nanti
    </script>
@endsection
