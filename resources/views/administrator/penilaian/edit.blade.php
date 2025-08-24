@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Penilaian</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
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
                @csrfw
                @method('PUT')
                {{-- INFORMASI UMUM --}}
                <div class="col-md-4">
                    <label for="siswa_id" class="form-label">Siswa</label>
                    <select class="form-control" name="siswa_id" id="siswa_id" required>
                        <option value="" disabled selected>-- Pilih Siswa --</option>
                        @foreach ($siswas as $siswa)
                            <option value="{{ $siswa->id }}"
                                {{ old('siswa_id', $penilaian->siswa_id) == $siswa->id ? 'selected' : '' }}>
                                {{ $siswa->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('siswa_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                {{-- Ganti bagian input Penilai dengan ini --}}
                <div class="col-md-4">
                    <label for="penilai" class="form-label">Penilai</label>
                    {{-- Menampilkan nama penilai yang tersimpan, tidak bisa diubah --}}
                    <input class="form-control" type="text" value="{{ $penilaian->penilai->name ?? 'N/A' }}" readonly>
                    {{-- Kita tidak mengirim ulang penilai_id agar tidak bisa diubah saat edit --}}
                </div>
                <div class="col-md-4">
                    <label for="tanggal_penilaian" class="form-label">Tanggal Penilaian</label>
                    <input class="form-control" type="date" name="tanggal_penilaian" id="tanggal_penilaian"
                        value="{{ old('tanggal_penilaian', $penilaian->tanggal_penilaian) }}">
                    @error('tanggal_penilaian')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
        </div>

        <hr>
        <h5 class="mb-3">Rincian Skor (0-100)</h5>
        @php
            $kriteria = ['Performance', 'Attitude', 'Kerjasama', 'Inisiatif', 'Disiplin'];
        @endphp
        @foreach ($kriteria as $item)
            @php
                $key = strtolower($item);
            @endphp
            <div class="row mb-2">
                <label class="col-md-2 col-form-label">{{ $item }}</label>
                <div class="col-md-10">
                    {{-- Mengambil nilai dari $detailNilai yang sudah disiapkan di controller --}}
                    <input class="form-control" type="number" name="nilai[{{ $key }}]"
                        value="{{ old('nilai.' . $key, $detailNilai[$key] ?? '') }}" min="0" max="100">
                    @error('nilai.' . $key)
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        @endforeach

        <hr>
        <div class="mb-3">
            <label for="komentar" class="form-label">Komentar Akhir</label>
            <textarea name="komentar" id="komentar" class="form-control" rows="3">{{ old('komentar', $penilaian->komentar) }}</textarea>
        </div>

        <div class="button-navigate mt-4">
            <a href="{{ route('admin.penilaian.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save me-1"></i> Simpan Perubahan
            </button>
        </div>
        </form>
    </div>
    </div>
@endsection
