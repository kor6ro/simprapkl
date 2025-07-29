@extends('layout.main')
@section('content')
    <div class="row">
        <div class="col-12">
            <h4 class="mb-3">Tambah Jenis Presensi</h4>
            <form action="{{ route('presensi_jenis.store') }}" method="POST">
                @csrf

                {{-- Nama --}}
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" name="nama" id="nama" class="form-control" value="{{ old('nama') }}">
                    @error('nama')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Butuh Bukti --}}
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="butuh_bukti" value="0">
                    <input class="form-check-input" type="checkbox" name="butuh_bukti" id="butuh_bukti" value="1"
                        {{ old('butuh_bukti') ? 'checked' : '' }}>
                    <label class="form-check-label" for="butuh_bukti">Butuh Bukti</label>
                </div>

                {{-- Otomatis --}}
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="otomatis" value="0">
                    <input class="form-check-input" type="checkbox" name="otomatis" id="otomatis" value="1"
                        {{ old('otomatis') ? 'checked' : '' }}>
                    <label class="form-check-label" for="otomatis">Otomatis</label>
                </div>

                <a href="{{ route('presensi_jenis.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
@endsection
