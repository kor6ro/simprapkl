@extends('layout.main')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Edit Tim</h4>
    <a href="{{ route('admin.tim.index') }}" class="btn btn-secondary btn-sm">🔙 Kembali</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.tim.update', $team->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Ketua Tim <span class="text-danger">*</span></label>
                    <select class="form-select" name="ketua_id" required>
                        @foreach($availableAdmins as $karyawan)
                            <option value="{{ $karyawan->id }}" {{ old('ketua_id', $team->ketua_id) == $karyawan->id ? 'selected' : '' }}>
                                {{ $karyawan->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('ketua_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Jenis Tim <span class="text-danger">*</span></label>
                    <select class="form-select" name="divisi_id" required>
                        @foreach($daftarDivisi as $divisi)
                            <option value="{{ $divisi->id }}" {{ old('divisi_id', $team->divisi_id) == $divisi->id ? 'selected' : '' }}>
                                {{ $divisi->nama_divisi }}
                            </option>
                        @endforeach
                    </select>
                    @error('divisi_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">Anggota Tim <span class="text-danger">*</span></label>
                    <select id="anggota-select" name="anggota[]" multiple required>
                        @php
                            $currentMemberIds = old('anggota', $team->anggota->pluck('id')->toArray());
                        @endphp
                        @foreach($availableSiswa as $siswa)
                            <option value="{{ $siswa->id }}" {{ in_array($siswa->id, $currentMemberIds) ? 'selected' : '' }}>
                                {{ $siswa->name }}
                            </option>
                        @endforeach
                    </select>
                     @error('anggota') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    new TomSelect('#anggota-select', { 
        plugins: ['remove_button'], 
        placeholder: 'Pilih satu atau lebih anggota tim...',
    });
</script>
@endsection