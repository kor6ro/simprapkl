@extends('layout.main')
@section('css')
    <style>

    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Setting Presensi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="#">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Edit Setting Presensi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-primary">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Edit Setting Presensi</h4>
            <form action="{{ route('setting_presensi.update', $setting_presensi->id) }}" method="post"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="status_presensi" class="form-label">Status Presensi</label>
                            <input class="form-control" type="text" name="status_presensi" id="status_presensi"
                                value="{{ old('status_presensi') ? old('status_presensi') : $setting_presensi->status_presensi }}">
                            @error('status_presensi')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="tanggal_presensi" class="form-label">Tanggal Presensi</label>
                            <input class="form-control" type="text" name="tanggal_presensi" id="tanggal_presensi"
                                value="{{ old('tanggal_presensi') ? old('tanggal_presensi') : $setting_presensi->tanggal_presensi }}">
                            @error('tanggal_presensi')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="user_id" class="form-label">User Id</label>
                            <select class="form-select" name="user_id" id="user_id">
                                <option value="">-- Pilih User Id --</option>

                            </select>
                            @error('user_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="button-navigate mt-3">
                    <a href="{{ route('setting_presensi.index') }}" class="btn btn-secondary">
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
