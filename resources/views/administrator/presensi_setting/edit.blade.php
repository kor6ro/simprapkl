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
            <form action="{{ route('presensi_setting.update', $presensiSetting->id) }}" method="post">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-primary mb-3">Sesi Pagi</h5>
                        <div class="row">
                            <div class="col-xl-6 mb-3">
                                <label for="pagi_mulai" class="form-label">Jam Mulai Pagi</label>
                                <input class="form-control" type="time" name="pagi_mulai" id="pagi_mulai"
                                    value="{{ old('pagi_mulai', \Carbon\Carbon::parse($presensiSetting->pagi_mulai)->format('H:i')) }}">
                                @error('pagi_mulai')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-xl-6 mb-3">
                                <label for="pagi_selesai" class="form-label">Jam Selesai Pagi</label>
                                <input class="form-control" type="time" name="pagi_selesai" id="pagi_selesai"
                                    value="{{ old('pagi_selesai', \Carbon\Carbon::parse($presensiSetting->pagi_selesai)->format('H:i')) }}">
                                @error('pagi_selesai')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="text-primary mb-3">Sesi Sore</h5>
                        <div class="row">
                            <div class="col-xl-6 mb-3">
                                <label for="sore_mulai" class="form-label">Jam Mulai Sore</label>
                                <input class="form-control" type="time" name="sore_mulai" id="sore_mulai"
                                    value="{{ old('sore_mulai', \Carbon\Carbon::parse($presensiSetting->sore_mulai)->format('H:i')) }}">
                                @error('sore_mulai')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-xl-6 mb-3">
                                <label for="sore_selesai" class="form-label">Jam Selesai Sore</label>
                                <input class="form-control" type="time" name="sore_selesai" id="sore_selesai"
                                    value="{{ old('sore_selesai', \Carbon\Carbon::parse($presensiSetting->sore_selesai)->format('H:i')) }}">
                                @error('sore_selesai')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <label class="form-label d-block">Status Aktif</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', $presensiSetting->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktifkan Setting Ini</label>
                        </div>
                        <small class="text-muted">*Hanya satu setting yang dapat aktif pada satu waktu</small>
                    </div>
                </div>

                <div class="button-navigate mt-3">
                    <a href="{{ route('presensi_setting.index') }}" class="btn btn-secondary">
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
    <script></script>
@endsection
