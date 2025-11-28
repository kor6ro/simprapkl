@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Perbaiki Pengajuan Izin/Sakit</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('presensi.index') }}">Presensi</a></li>
                        <li class="breadcrumb-item active">Edit Pengajuan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            {{-- Form ini akan mengirim ke method updateAbsenceRequest --}}
            <form action="{{ route('presensi.update_absence', $presensi->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @if ($presensi->approval_status == 'rejected')
                    <div class="alert alert-danger">
                        <strong>Pengajuan Anda Ditolak.</strong> Silakan perbaiki keterangan atau bukti Anda dan ajukan
                        kembali.
                    </div>
                @endif

                {{-- Data ini tidak bisa diubah oleh siswa --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Pengajuan</label>
                        <input type="text" class="form-control"
                            value="{{ optional($presensi->presensi_at)->translatedFormat('l, d F Y') }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jenis Pengajuan</label>
                        <input type="text" class="form-control" value="{{ $presensi->status }}" readonly>
                    </div>
                </div>

                {{-- Siswa hanya bisa edit field ini --}}
                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan (min. 20 karakter) <span
                            class="text-danger">*</span></label>
                    <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror"
                        rows="4" required minlength="20">{{ old('keterangan', $presensi->keterangan) }}</textarea>
                    @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="bukti_foto" class="form-label">Ubah Bukti (Foto/PDF)</label>
                    <input type="file" name="bukti_foto" id="bukti_foto"
                        class="form-control @error('bukti_foto') is-invalid @enderror" accept="image/*,.pdf">
                    <div class="form-text">Kosongkan jika tidak ingin mengubah file bukti.</div>
                    @if ($presensi->bukti_foto)
                        <div class="mt-2">
                            <small>Bukti saat ini: <a href="{{ asset('storage/' . $presensi->bukti_foto) }}"
                                    target="_blank">Lihat File</a></small>
                        </div>
                    @endif
                    @error('bukti_foto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4">
                    <a href="{{ route('presensi.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left me-1"></i>
                        Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Kirim Ulang
                        Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
