@extends('layout.main')

@section('content')
    {{-- Page Title and Breadcrumb Section --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Edit Presensi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('presensi.index') }}">Presensi</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Form Card --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('presensi.update', $presensi->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Error Validation Display --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <h5 class="alert-heading">Terjadi Kesalahan Validasi</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form Content Grid --}}
                <div class="row">

                    {{-- Left Column --}}
                    <div class="col-md-6">

                        {{-- Student Name (disabled on edit page) --}}
                        <div class="mb-3">
                            <label for="user_name" class="form-label">Nama Siswa</label>
                            <input type="text" id="user_name" class="form-control"
                                value="{{ $presensi->user->name ?? 'Siswa tidak ditemukan' }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_presensi" class="form-label">Tanggal Presensi <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_presensi" name="tanggal_presensi"
                                value="{{ old('tanggal_presensi', $presensi->presensi_at?->format('Y-m-d')) }}" required>
                        </div>

                        {{-- Session Dropdown --}}
                        <div class="mb-3">
                            <label for="sesi" class="form-label">Sesi <span class="text-danger">*</span></label>
                            <select class="form-select" id="sesi" name="sesi" required>
                                <option value="pagi" {{ old('sesi', $presensi->sesi ?? '') == 'pagi' ? 'selected' : '' }}>
                                    Pagi
                                </option>
                                <option value="sore" {{ old('sesi', $presensi->sesi ?? '') == 'sore' ? 'selected' : '' }}>
                                    Sore
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="jam_presensi" class="form-label">Jam Presensi (Opsional)</label>
                            {{-- Mengambil jam dari presensi_at --}}
                            <input type="time" class="form-control" id="jam_presensi" name="jam_presensi"
                                value="{{ old('jam_presensi', $presensi->presensi_at?->format('H:i')) }}">
                            <div class="form-text">Kosongkan jika statusnya Izin, Sakit, atau Alpa.</div>
                        </div>

                    </div>
                    {{-- Right Column --}}
                    <div class="col-md-6">

                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            {{-- Ganti class 'basic-select' menjadi 'select2-init' --}}
                            <select class="form-select select2-init" id="status" name="status" required>
                                {{-- Isinya tidak perlu diubah --}}
                                <option value="">-- Pilih Status Presensi --</option>
                                @if (isset($presensiStatus) && $presensiStatus->count() > 0)
                                    @foreach ($presensiStatus as $status_item)
                                        <option value="{{ $status_item->status }}"
                                            @if (old('status', $presensi->status) == $status_item->status) selected @endif>
                                            {{ $status_item->status }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>



                        {{-- Description Textarea --}}
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3">{{ old('keterangan', $presensi->keterangan ?? '') }}</textarea>
                        </div>

                        {{-- Photo Proof File Input --}}
                        <div class="mb-3">
                            <label for="bukti_foto" class="form-label">Bukti Foto (Opsional)</label>
                            <input class="form-control" type="file" id="bukti_foto" name="bukti_foto" accept="image/*">
                            {{-- Image preview if editing and a photo exists --}}
                            @if ($presensi->bukti_foto)
                                <div class="mt-2">
                                    <p class="form-text mb-1">Foto saat ini:</p>
                                    <img src="{{ asset('storage/' . $presensi->bukti_foto) }}" alt="Bukti Foto"
                                        class="img-thumbnail" width="150" style="border-radius: 8px;">
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-4">
                    <a href="{{ route('presensi.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left me-1"></i>
                        Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan
                        Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        {{-- Memuat library Select2 jika belum ada (opsional, tapi disarankan) --}}
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            // Menunggu sampai seluruh halaman dan jQuery siap
            $(document).ready(function() {
                // Inisialisasi plugin Select2 secara manual HANYA untuk dropdown status
                $('.select2-init').select2({
                    placeholder: '-- Pilih Status Presensi --',
                    width: '100%'
                });
            });
        </script>
    @endpush
@endsection
