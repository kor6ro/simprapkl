@extends('layout.main')
@section('css')
    <style>

    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Laporan Gambar</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="#">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Edit Laporan Gambar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-primary">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Edit Laporan Gambar</h4>
            <form action="{{ route('laporan_gambar.update', $laporan_gambar->id) }}" method="post"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="gambar" class="form-label">Gambar</label>
                            <input class="form-control" type="text" name="gambar" id="gambar"
                                value="{{ old('gambar') ? old('gambar') : $laporan_gambar->gambar }}">
                            @error('gambar')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xl-auto">
                        <div class="form-group">
                            <label for="laporan_id" class="form-label">Laporan Id</label>
                            <select class="form-select" name="laporan_id" id="laporan_id">
                                <option value="">-- Pilih Laporan Id --</option>

                            </select>
                            @error('laporan_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="button-navigate mt-3">
                    <a href="{{ route('laporan_gambar.index') }}" class="btn btn-secondary">
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
