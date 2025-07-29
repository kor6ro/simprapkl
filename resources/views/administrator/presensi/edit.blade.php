@extends('layout.main')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Edit Presensi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('presensi.index') }}">Presensi</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('presensi.update', $presensi->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="user_id" class="form-label">Siswa</label>
                            <select class="form-control" name="user_id" id="user_id" required>
                                <option value="">Pilih Siswa</option>
                                @foreach ($user as $u)
                                    <option value="{{ $u->id }}"
                                        {{ $presensi->user_id == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} (Siswa PKL)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hanya siswa PKL yang dapat melakukan presensi</small>
                            @error('user_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="presensi_jenis_id" class="form-label">Jenis Presensi</label>
                            <select class="form-control" name="presensi_jenis_id" id="presensi_jenis_id" required>
                                <option value="">Pilih Jenis Presensi</option>
                                @foreach ($jenisPresensi as $jenis)
                                    <option value="{{ $jenis->id }}"
                                        {{ $presensi->presensi_jenis_id == $jenis->id ? 'selected' : '' }}>
                                        {{ ucfirst($jenis->nama) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('presensi_jenis_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tanggal_presensi" class="form-label">Tanggal Presensi</label>
                                    <input type="date" name="tanggal_presensi" class="form-control"
                                        value="{{ old('tanggal_presensi', $presensi->tanggal_presensi) }}" required>
                                    @error('tanggal_presensi')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jam_presensi" class="form-label">Jam Presensi</label>
                                    <input type="time" name="jam_presensi" class="form-control"
                                        value="{{ old('jam_presensi', $presensi->jam_presensi) }}" required>
                                    @error('jam_presensi')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sesi" class="form-label">Sesi</label>
                            <select class="form-control" name="sesi" id="sesi" required>
                                <option value="">Pilih Sesi</option>
                                <option value="pagi" {{ $presensi->sesi == 'pagi' ? 'selected' : '' }}>Pagi</option>
                                <option value="sore" {{ $presensi->sesi == 'sore' ? 'selected' : '' }}>Sore</option>
                            </select>
                            @error('sesi')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bukti" class="form-label">Upload Bukti (Opsional)</label>
                            <input type="file" name="bukti" class="form-control" accept="image/*">
                            <small class="text-muted">Upload bukti baru jika ingin mengganti (JPG, PNG, GIF, max
                                2MB)</small>
                            @error('bukti')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        @if ($presensi->gambar && $presensi->gambar->bukti)
                            <div class="mb-3">
                                <label class="form-label">Bukti Saat Ini:</label><br>
                                <img src="{{ asset('storage/' . $presensi->gambar->bukti) }}" alt="Bukti"
                                    class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="catatan_verifikasi" class="form-label">Catatan Verifikasi</label>
                            <textarea name="catatan_verifikasi" class="form-control" rows="3">{{ old('catatan_verifikasi', $presensi->catatan_verifikasi) }}</textarea>
                            @error('catatan_verifikasi')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('presensi.index') }}" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
