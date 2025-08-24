@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Presensi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="#">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Tambah Presensi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-primary">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Tambah Presensi</h4>
            <form action="{{ route('presensi.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                {{-- Form fields (Siswa, Tanggal, Sesi, Status, dll.) --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Siswa</label>
                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror"
                                required>
                                <option value="">Pilih Siswa</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} - {{ $user->sekolah->nama ?? 'Belum ada sekolah' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tanggal_presensi" class="form-label">Tanggal Presensi</label>
                            <input type="date" name="tanggal_presensi" id="tanggal_presensi"
                                class="form-control @error('tanggal_presensi') is-invalid @enderror"
                                value="{{ old('tanggal_presensi', date('Y-m-d')) }}" required>
                            @error('tanggal_presensi')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sesi" class="form-label">Sesi</label>
                            <select name="sesi" id="sesi" class="form-select @error('sesi') is-invalid @enderror"
                                required>
                                <option value="pagi" {{ old('sesi') == 'pagi' ? 'selected' : '' }}>Pagi</option>
                                <option value="sore" {{ old('sesi') == 'sore' ? 'selected' : '' }}>Sore</option>
                            </select>
                            @error('sesi')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="jam_presensi" class="form-label">Jam Presensi</label>
                            <input type="time" name="jam_presensi" id="jam_presensi"
                                class="form-control @error('jam_presensi') is-invalid @enderror"
                                value="{{ old('jam_presensi') }}">
                            <small class="text-muted">Kosongkan jika Izin/Sakit/Alpa</small>
                            @error('jam_presensi')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror"
                                required>
                                <option value="">Pilih Status</option>
                                @foreach ($presensiStatus as $status)
                                    <option value="{{ $status->status }}"
                                        {{ old('status') == $status->status ? 'selected' : '' }}>
                                        {{ $status->status }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="bukti_foto" class="form-label">Upload Bukti (Opsional)</label>
                            <input type="file" name="bukti_foto"
                                class="form-control @error('bukti_foto') is-invalid @enderror" id="bukti_foto">
                            @error('bukti_foto')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" class="form-control" rows="3">{{ old('keterangan') }}</textarea>
                </div>

                {{-- Tombol navigasi --}}
                <div class="button-navigate mt-3">
                    <a href="{{ route('presensi.index') }}" class="btn btn-secondary">
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
