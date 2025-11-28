{{-- resources/views/administrator/penilaian/edit.blade.php --}}
@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Penilaian</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.penilaian.index') }}">Penilaian</a></li>
                        <li class="breadcrumb-item active">Edit Penilaian</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Edit Penilaian</h4>
            <form action="{{ route('admin.penilaian.update', $penilaian->id) }}" method="post">
                @csrf
                @method('PUT')

                {{-- INFORMASI UMUM --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="siswa_name" class="form-label">Siswa</label>
                        {{-- MENGGANTI SELECT DENGAN INPUT READONLY --}}
                        <input class="form-control" type="text" id="siswa_name"
                            value="{{ $penilaian->siswa->name ?? 'N/A' }}" readonly>
                        {{-- HIDDEN INPUT UNTUK MENGIRIMKAN ID SISWA --}}
                        <input type="hidden" name="siswa_id" value="{{ $penilaian->siswa_id }}">
                    </div>
                    <div class="col-md-4">
                        <label for="penilai" class="form-label">Penilai</label>
                        <input class="form-control" type="text" value="{{ $penilaian->penilai->name ?? 'N/A' }}"
                            readonly>
                        <input type="hidden" name="penilai_id" value="{{ $penilaian->penilai_id }}">
                    </div>
                    <div class="col-md-4">
                        <label for="tanggal_penilaian" class="form-label">Tanggal Penilaian</label>
                        <input class="form-control" type="date" name="tanggal_penilaian" id="tanggal_penilaian"
                            value="{{ old('tanggal_penilaian', $penilaian->tanggal_penilaian->format('Y-m-d')) }}">
                        @error('tanggal_penilaian')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="tanggal-mulai-pkl" class="form-label">Tanggal Mulai PKL</label>
                        <input type="date" class="form-control" id="tanggal-mulai-pkl" name="pkl_tanggal_mulai"
                            value="{{ old('pkl_tanggal_mulai', $penilaian->pkl_tanggal_mulai ? $penilaian->pkl_tanggal_mulai->format('Y-m-d') : '') }}"
                            readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal-selesai-pkl" class="form-label">Tanggal Selesai PKL</label>
                        <input type="date" class="form-control" id="tanggal-selesai-pkl" name="pkl_tanggal_selesai"
                            value="{{ old('pkl_tanggal_selesai', $penilaian->pkl_tanggal_selesai ? $penilaian->pkl_tanggal_selesai->format('Y-m-d') : '') }}"
                            readonly>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3">Rincian Skor (0-100)</h5>

                @foreach ($kriteria as $item)
                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label">{{ $item->nama_variabel }}</label>
                        <div class="col-md-8">
                            <input class="form-control" type="number" name="nilai[{{ $item->kode_variabel }}]"
                                value="{{ old('nilai.' . $item->kode_variabel, $detailPenilaian[$item->kode_variabel] ?? '') }}"
                                min="0" max="100" placeholder="0-100" required>
                            @error('nilai.' . $item->kode_variabel)
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                @endforeach

                <hr>
                <div class="mb-3">
                    <label for="komentar_saran" class="form-label">Komentar/Saran Akhir</label>
                    <textarea name="komentar_saran" id="komentar_saran" class="form-control" rows="3">{{ old('komentar_saran', $penilaian->komentar_saran) }}</textarea>
                </div>

                <div class="button-navigate mt-4">
                    <a href="{{ route('admin.penilaian.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

{{-- MENGHAPUS BLOK SCRIPT LAMA KARENA TIDAK DIPERLUKAN LAGI --}}
@section('js')
@endsection
