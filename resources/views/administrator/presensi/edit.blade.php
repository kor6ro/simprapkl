@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">✏️ Edit Data Presensi</h4>
                <a href="{{ route('presensi.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Form Edit Presensi</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('presensi.update', $presensi->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Nama Siswa</label>
                    <input type="text" class="form-control" value="{{ $presensi->user->name }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Presensi</label>
                    <input type="date" name="tanggal_presensi" class="form-control"
                        value="{{ $presensi->tanggal_presensi }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Sesi</label>
                    <select name="sesi" class="form-control" required>
                        <option value="pagi" {{ $presensi->sesi == 'pagi' ? 'selected' : '' }}>Pagi</option>
                        <option value="sore" {{ $presensi->sesi == 'sore' ? 'selected' : '' }}>Sore</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jam Presensi</label>
                    <input type="time" name="jam_presensi" class="form-control" value="{{ $presensi->jam_presensi }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status Presensi</label>
                    <select name="presensi_status_id" class="form-control" required>
                        @foreach ($presensistatus as $status)
                            <option value="{{ $status->id }}"
                                {{ $presensi->presensi_status_id == $status->id ? 'selected' : '' }}>
                                {{ $status->status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $presensi->keterangan) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Bukti Foto</label>
                    @if ($presensi->bukti_foto)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $presensi->bukti_foto) }}" alt="Bukti Foto"
                                class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    @endif
                    <input type="file" name="bukti_foto" class="form-control" accept="image/*">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti foto.</small>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
@endsection
