@extends('layout.main')
@section('content')
    <div class="row">
        <div class="col-12">
            <h4 class="mb-3">Edit Jenis Presensi</h4>
            <form action="{{ route('presensi_status.update', $jenis->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" name="nama" id="nama" class="form-control"
                        value="{{ old('nama', $jenis->nama) }}">
                    @error('nama')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Hidden value untuk handle unchecked checkbox --}}
                <input type="hidden" name="butuh_bukti" value="0">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="butuh_bukti" id="butuh_bukti" value="1"
                        {{ old('butuh_bukti', $jenis->butuh_bukti) ? 'checked' : '' }}>
                    <label class="form-check-label" for="butuh_bukti">Butuh Bukti</label>
                </div>

                <input type="hidden" name="otomatis" value="0">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="otomatis" id="otomatis" value="1"
                        {{ old('otomatis', $jenis->otomatis) ? 'checked' : '' }}>
                    <label class="form-check-label" for="otomatis">Otomatis</label>
                </div>

                <a href="{{ route('presensi_status.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
@endsection
