@extends('layout.main')
@section('css')
    <style>

    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">User</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="#">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Tambah User</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-primary">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Tambah User</h4>
            <form action="{{ route('admin.user.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="name" class="form-label">Name</label>
                            <input class="form-control" type="text" name="name" id="name"
                                value="{{ old('name') }}">
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <input class="form-control" type="text" name="username" id="username"
                                value="{{ old('username') }}">
                            @error('username')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input class="form-control" type="text" name="email" id="email"
                                value="{{ old('email') }}">
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input class="form-control" type="text" name="password" id="password"
                                value="{{ old('password') }}">
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div>
                            <label class="form-label">Validasi</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="validasi"
                                    name="validasi" {{ old('validasi') == 1 ? 'checked' : '' }}>
                                <label for="validasi" class="custom-control-label">Validasi</label>
                            </div>
                            @error('validasi')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="sekolah_id" class="form-label">Sekolah Id</label>
                            <select class="form-select" name="sekolah_id" id="sekolah_id">
                                <option value="">-- Pilih Sekolah Id --</option>
                                @foreach ($sekolah as $val)
                                    <option value="{{ $val->id }}"
                                        {{ old('sekolah_id') == $val->id ? 'selected' : '' }}>
                                        {{ $val->nama }}</option>
                                @endforeach
                            </select>
                            @error('sekolah_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="group_id" class="form-label">Group Id</label>
                            <select class="form-select" name="group_id" id="group_id">
                                <option value="">-- Pilih Group Id --</option>
                                @foreach ($group as $val)
                                    <option value="{{ $val->id }}"
                                        {{ old('group_id') == $val->id ? 'selected' : '' }}>
                                        {{ $val->nama }}</option>
                                @endforeach
                            </select>
                            @error('group_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="alamat" class="form-label">Alamat</label>
                            <input class="form-control" type="text" name="alamat" id="alamat"
                                value="{{ old('alamat') }}">
                            @error('alamat')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="button-navigate mt-3">
                    <a href="{{ route('admin.user.index') }}" class="btn btn-secondary">
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
@section('js')
    <script></script>
@endsection
