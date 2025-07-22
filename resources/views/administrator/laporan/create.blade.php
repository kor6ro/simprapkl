@extends('layout.main')
@section('css')
    <style>

    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Laporan</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="#">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Tambah Laporan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-primary">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Tambah Laporan</h4>
            <form action="{{ route('laporan.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="jenis_kegiatan" class="form-label">Jenis Kegiatan</label>
                            <input class="form-control" type="text" name="jenis_kegiatan" id="jenis_kegiatan"
                                value="{{ old('jenis_kegiatan') }}">
                            @error('jenis_kegiatan')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <input class="form-control" type="text" name="lokasi" id="lokasi"
                                value="{{ old('lokasi') }}">
                            @error('lokasi')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="homepass" class="form-label">Homepass</label>
                            <input class="form-control" type="text" name="homepass" id="homepass"
                                value="{{ old('homepass') }}">
                            @error('homepass')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="jml_orang_ditemui" class="form-label">Jml Orang Ditemui</label>
                            <input class="form-control" type="number" name="jml_orang_ditemui" id="jml_orang_ditemui"
                                value="{{ old('jml_orang_ditemui') }}">
                            @error('jml_orang_ditemui')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="detail_pekerjaan" class="form-label">Detail Pekerjaan</label>
                            <input class="form-control" type="text" name="detail_pekerjaan" id="detail_pekerjaan"
                                value="{{ old('detail_pekerjaan') }}">
                            @error('detail_pekerjaan')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="hasil_capaian" class="form-label">Hasil Capaian</label>
                            <input class="form-control" type="text" name="hasil_capaian" id="hasil_capaian"
                                value="{{ old('hasil_capaian') }}">
                            @error('hasil_capaian')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="user_id" class="form-label">User Id</label>
                            <select class="form-select" name="user_id" id="user_id">
                                <option value="">-- Pilih User Id --</option>
                                @foreach ($user as $val)
                                    <option value="{{ $val->id }}"
                                        {{ old('user_id') == $val->id ? 'selected' : '' }}>
                                        {{ $val->name }}</option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="jenis_laporan_id" class="form-label">Jenis Laporan Id</label>
                            <select class="form-select" name="jenis_laporan_id" id="jenis_laporan_id">
                                <option value="">-- Pilih Jenis Laporan Id --</option>
                                @foreach ($jenis_laporan as $val)
                                    <option value="{{ $val->id }}"
                                        {{ old('jenis_laporan_id') == $val->id ? 'selected' : '' }}>
                                        {{ $val->nama }}</option>
                                @endforeach
                            </select>
                            @error('jenis_laporan_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="laporan_gambar_id" class="form-label">Laporan Gambar Id</label>
                            <select class="form-select" name="laporan_gambar_id" id="laporan_gambar_id">
                                <option value="">-- Pilih Laporan Gambar Id --</option>
                                @foreach ($laporan_gambar as $val)
                                    <option value="{{ $val->id }}"
                                        {{ old('laporan_gambar_id') == $val->id ? 'selected' : '' }}>
                                        {{ $val->gambar }}</option>
                                @endforeach
                            </select>
                            @error('laporan_gambar_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="button-navigate mt-3">
                    <a href="{{ route('laporan.index') }}" class="btn btn-secondary">
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
