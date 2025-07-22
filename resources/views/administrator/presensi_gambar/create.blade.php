@extends('layout.main')
@section('css')
    <style>

    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Presensi Gambar</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="#">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Tambah Presensi Gambar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-primary">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Tambah Presensi Gambar</h4>
            <form action="{{ route('presensi_gambar.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="gmbr_presensi_pagi" class="form-label">Gmbr Presensi Pagi</label>
                            <input class="form-control" type="text" name="gmbr_presensi_pagi" id="gmbr_presensi_pagi"
                                value="{{ old('gmbr_presensi_pagi') }}">
                            @error('gmbr_presensi_pagi')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="gmbr_presensi_sore" class="form-label">Gmbr Presensi Sore</label>
                            <input class="form-control" type="text" name="gmbr_presensi_sore" id="gmbr_presensi_sore"
                                value="{{ old('gmbr_presensi_sore') }}">
                            @error('gmbr_presensi_sore')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="presensi_id" class="form-label">Presensi Id</label>
                            <select class="form-select" name="presensi_id" id="presensi_id">
                                <option value="">-- Pilih Presensi Id --</option>

                            </select>
                            @error('presensi_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="button-navigate mt-3">
                    <a href="{{ route('presensi_gambar.index') }}" class="btn btn-secondary">
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
