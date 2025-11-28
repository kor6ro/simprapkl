@extends('layout.main')

@section('css')
    {{-- TomSelect untuk dropdown yang lebih canggih --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('content')
    {{-- Judul Halaman dan Breadcrumb --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Buat Laporan Kegiatan Baru</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.laporan.index') }}">Laporan Kegiatan</a></li>
                        <li class="breadcrumb-item active">Buat Baru</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Kartu Form Utama --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.laporan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="return_context" value="{{ $return_context ?? 'sidebar' }}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tim_id" class="form-label">Pilih Tugas <span class="text-danger">*</span></label>
                        @if(!$daftarTugas->isEmpty())
                                {{-- Tampilkan dropdown TomSelect HANYA jika ada tugas --}}
                                <select class="form-select @error('tim_id') is-invalid @enderror" name="tim_id" id="tim_id" required>
                                    <option value="">-- Pilih tugas yang akan dilaporkan --</option>
                                    @foreach ($daftarTugas as $tim)
                                        <option value="{{ $tim->id }}"
                                            data-pic="PIC: {{ $tim->ketua->pluck('name')->implode(', ') }}"
                                            data-anggota="Anggota: {{ $tim->anggota->pluck('name')->implode(', ') }}"
                                            {{ old('tim_id') == $tim->id ? 'selected' : '' }}>
                                            {{ $tim->divisi->nama_divisi }} - {{ \Carbon\Carbon::parse($tim->tanggal)->format('d M Y') }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                {{-- Jika tidak ada tugas, tampilkan input yang di-disable --}}
                                <input type="text" class="form-control" value="Saat ini tidak ada tugas yang bisa dilaporkan." disabled>
                            @endif
                            {{-- [AKHIR PERUBAHAN] --}}
                            @error('tim_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="jenis_kegiatan_id" class="form-label">Jenis Kegiatan <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('jenis_kegiatan_id') is-invalid @enderror"
                                name="jenis_kegiatan_id" id="jenis_kegiatan_id" required>
                                <option value="">-- Pilih jenis kegiatan --</option>
                                @foreach ($jenis_kegiatan as $kegiatan)
                                    <option value="{{ $kegiatan->id }}"
                                        {{ old('jenis_kegiatan_id') == $kegiatan->id ? 'selected' : '' }}>
                                        {{ $kegiatan->nama_kegiatan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_kegiatan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="deskripsi_kegiatan" class="form-label">Deskripsi Kegiatan <span
                            class="text-danger">*</span></label>
                    <textarea class="form-control @error('deskripsi_kegiatan') is-invalid @enderror" name="deskripsi_kegiatan"
                        id="deskripsi_kegiatan" rows="4" required>{{ old('deskripsi_kegiatan') }}</textarea>
                    @error('deskripsi_kegiatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="bukti_foto" class="form-label">Bukti Foto (Opsional, maks. 2MB)</label>
                    <input class="form-control @error('bukti_foto') is-invalid @enderror" type="file" name="bukti_foto"
                        id="bukti_foto" accept="image/*">
                    @error('bukti_foto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tombol Aksi --}}
                <div class="mt-4">
                    <a href="{{ route('admin.laporan.index') }}" class="btn btn-secondary"><i
                            class="fa fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Kirim
                        Laporan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // [PERUBAHAN KUNCI] Jalankan TomSelect HANYA jika elemen #tim_id ada
            const timSelect = document.getElementById('tim_id');
            if (timSelect) {
                new TomSelect(timSelect, {
                    placeholder: 'Cari tugas...',
                    render: {
                        option: function(data, escape) {
                            return `<div class="py-2 px-3">
                                        <div class="fw-bold">${escape(data.text)}</div>
                                        <div class="text-muted small mt-1">${escape(data.pic)}</div>
                                        <div class="text-muted small">${escape(data.anggota)}</div>
                                    </div>`;
                        },
                        item: function(data, escape) {
                            return `<div title="${escape(data.anggota)}">${escape(data.text)}</div>`;
                        }
                    }
                });
            }
        });
    </script>
@endsection