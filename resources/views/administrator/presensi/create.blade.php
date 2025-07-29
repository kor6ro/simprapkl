@extends('layout.main')
@section('content')
    <div class="row">
        <div class="col-12">
            <h4 class="mb-3">Tambah Presensi</h4>
            <form action="{{ route('presensi.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                <p class="form-control-plaintext">{{ auth()->user()->name }}</p>

                <div class="mb-3">
                    <label for="presensi_jenis_id">Jenis Presensi</label>
                    <select name="presensi_jenis_id" class="form-select @error('presensi_jenis_id') is-invalid @enderror"
                        required>
                        <option value="">-- Pilih Jenis --</option>
                        @foreach ($jenisPresensi as $jenis)
                            <option value="{{ $jenis->id }}"
                                {{ old('presensi_jenis_id') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('presensi_jenis_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="sesi">Sesi</label>
                    <select name="sesi" class="form-select @error('sesi') is-invalid @enderror" required>
                        <option value="pagi" {{ old('sesi') == 'pagi' ? 'selected' : '' }}>Pagi</option>
                        <option value="sore" {{ old('sesi') == 'sore' ? 'selected' : '' }}>Sore</option>
                    </select>
                    @error('sesi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="bukti">Upload Bukti (jika ada)</label>
                    <input type="file" class="form-control @error('bukti') is-invalid @enderror" name="bukti"
                        accept="image/*">
                    @error('bukti')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="keterangan">Keterangan</label>
                    <textarea class="form-control @error('keterangan') is-invalid @enderror" name="keterangan" rows="2">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
@endsection
