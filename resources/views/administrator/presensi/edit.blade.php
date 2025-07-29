@extends('layout.main')
@section('content')
    <div class="row">
        <div class="col-12">
            <h4 class="mb-3">Edit Presensi</h4>
            <form action="{{ route('presensi.update', $presensi->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="user_id" class="form-label">User</label>
                    <select class="form-select" name="user_id" id="user_id">
                        <option value="">-- Pilih User --</option>
                        @foreach ($user as $user)
                            <option value="{{ $user->id }}" {{ $presensi->user_id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="presensi_jenis_id" class="form-label">Jenis Presensi</label>
                    <select class="form-select" name="presensi_jenis_id" id="presensi_jenis_id">
                        <option value="">-- Pilih Jenis --</option>
                        @foreach ($jenisPresensi as $jenis)
                            <option value="{{ $jenis->id }}"
                                {{ $presensi->presensi_jenis_id == $jenis->id ? 'selected' : '' }}>
                                {{ ucfirst($jenis->nama) }}
                            </option>
                        @endforeach
                    </select>
                    @error('presensi_jenis_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="tanggal_presensi" class="form-label">Tanggal</label>
                    <input type="date" name="tanggal_presensi" class="form-control"
                        value="{{ old('tanggal_presensi', $presensi->tanggal_presensi) }}">
                    @error('tanggal_presensi')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="jam_presensi" class="form-label">Jam</label>
                    <input type="time" name="jam_presensi" class="form-control"
                        value="{{ old('jam_presensi', $presensi->jam_presensi) }}">
                    @error('jam_presensi')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="sesi" class="form-label">Sesi</label>
                    <select class="form-select" name="sesi" id="sesi">
                        <option value="">-- Pilih Sesi --</option>
                        <option value="pagi" {{ $presensi->sesi == 'pagi' ? 'selected' : '' }}>Pagi</option>
                        <option value="sore" {{ $presensi->sesi == 'sore' ? 'selected' : '' }}>Sore</option>
                    </select>
                    @error('sesi')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="bukti" class="form-label">Upload Bukti (jika ingin mengganti)</label>
                    <input type="file" name="bukti" class="form-control">
                    @error('bukti')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                @if ($presensi->gambar && $presensi->gambar->bukti)
                    <div class="mb-3">
                        <label class="form-label">Bukti Saat Ini:</label><br>
                        <img src="{{ asset('storage/' . $presensi->gambar->bukti) }}" alt="Bukti" width="200">
                    </div>
                @endif

                <div class="mb-3">
                    <label for="catatan_verifikasi" class="form-label">Catatan</label>
                    <textarea name="catatan_verifikasi" class="form-control">{{ old('catatan_verifikasi', $presensi->catatan_verifikasi) }}</textarea>
                    @error('catatan_verifikasi')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <a href="{{ route('presensi.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
@endsection
