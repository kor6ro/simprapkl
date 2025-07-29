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
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Edit Setting Presensi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Edit Setting Presensi</h4>
            <form action="{{ route('presensi_setting.update', $presensi_setting->id) }}" method="post">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-xl-4 mb-3">
                        <label for="jam_masuk" class="form-label">Jam Masuk</label>
                        <input class="form-control" type="time" name="jam_masuk" id="jam_masuk"
                            value="{{ old('jam_masuk', $presensi_setting->jam_masuk) }}">
                        @error('jam_masuk')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-xl-4 mb-3">
                        <label for="jam_pulang" class="form-label">Jam Pulang</label>
                        <input class="form-control" type="time" name="jam_pulang" id="jam_pulang"
                            value="{{ old('jam_pulang', $presensi_setting->jam_pulang) }}">
                        @error('jam_pulang')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-xl-4 mb-3">
                        <label class="form-label d-block">Status Aktif</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                {{ $presensi_setting->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktifkan Setting Ini</label>
                        </div>
                    </div>
                </div>

                <div class="button-navigate mt-3">
                    <a href="{{ route('presensi_setting.index') }}" class="btn btn-secondary">
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
