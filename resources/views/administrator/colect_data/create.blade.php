@extends('layout.main')
@section('css')
    <style>

    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Colect Data</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="#">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Tambah Colect Data</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-primary">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Tambah Colect Data</h4>
            <form action="{{ route('colect_data.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input class="form-control" type="date" name="tanggal" id="tanggal"
                                value="{{ old('tanggal') }}">
                            @error('tanggal')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="nama_cus" class="form-label">Nama Cus</label>
                            <input class="form-control" type="text" name="nama_cus" id="nama_cus"
                                value="{{ old('nama_cus') }}">
                            @error('nama_cus')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="no_telp" class="form-label">No Telp</label>
                            <input class="form-control" type="text" name="no_telp" id="no_telp"
                                value="{{ old('no_telp') }}">
                            @error('no_telp')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="alamat_cus" class="form-label">Alamat Cus</label>
                            <textarea class="form-control" name="alamat_cus" id="alamat_cus" rows="3">{{ old('alamat_cus') }}</textarea>
                            @error('alamat_cus')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="provider_sekarang" class="form-label">Provider Sekarang</label>
                            <input class="form-control" type="text" name="provider_sekarang" id="provider_sekarang"
                                value="{{ old('provider_sekarang') }}">
                            @error('provider_sekarang')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="kelebihan" class="form-label">Kelebihan</label>
                            <textarea class="form-control" name="kelebihan" id="kelebihan" rows="3">{{ old('kelebihan') }}</textarea>
                            @error('kelebihan')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="kekurangan" class="form-label">Kekurangan</label>
                            <textarea class="form-control" name="kekurangan" id="kekurangan" rows="3">{{ old('kekurangan') }}</textarea>
                            @error('kekurangan')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="serlok" class="form-label">Serlok</label>
                            <input class="form-control" type="text" name="serlok" id="serlok"
                                value="{{ old('serlok') }}">
                            @error('serlok')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="gambar_foto" class="form-label">Gambar Foto</label>
                            <input class="form-control" type="text" name="gambar_foto" id="gambar_foto"
                                value="{{ old('gambar_foto') }}">
                            @error('gambar_foto')
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
                </div>
                <div class="button-navigate mt-3">
                    <a href="{{ route('colect_data.index') }}" class="btn btn-secondary">
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
