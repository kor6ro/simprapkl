@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Manajemen User</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">User</a></li>
                        <li class="breadcrumb-item active">Tambah User</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Form Tambah User Baru</h4>
            <form action="{{ route('admin.user.store') }}" method="post">
                @csrf
                <div class="row gy-4">
                    {{-- Baris 1: Pilihan Group dan Data Personal --}}
                    <div class="col-md-3">
                        {{-- Field Group sekarang ada di posisi paling awal --}}
                        <label for="group_id" class="form-label fw-bold">Pilih Group</label>
                        <select class="form-select" name="group_id" id="group_id" required>
                            <option value="" selected disabled>Group</option>
                            @foreach ($group as $val)
                                <option value="{{ $val->id }}" data-nama="{{ strtolower($val->nama) }}"
                                    {{ old('group_id') == $val->id ? 'selected' : '' }}>
                                    {{ $val->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('group_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="name" class="form-label fw-bold">Nama Lengkap</label>
                        <input class="form-control" type="text" name="name" id="name" value="{{ old('name') }}"
                            placeholder="Masukkan nama lengkap" required>
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="username" class="form-label fw-bold">Username</label>
                        <input class="form-control" type="text" name="username" id="username"
                            value="{{ old('username') }}" placeholder="Masukkan username" required>
                        @error('username')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="email" class="form-label fw-bold">Email</label>
                        <input class="form-control" type="email" name="email" id="email"
                            value="{{ old('email') }}" placeholder="contoh@email.com" required>
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Baris 2: Data Lanjutan --}}
                    <div class="col-md-3">
                        <label for="password" class="form-label fw-bold">Password</label>
                        <input class="form-control" type="password" name="password" id="password"
                            placeholder="Minimal 6 karakter" required>
                        @error('password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="alamat" class="form-label fw-bold">Alamat</label>
                        <input class="form-control" type="text" name="alamat" id="alamat"
                            value="{{ old('alamat') }}" placeholder="Masukkan alamat" required>
                        @error('alamat')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-3 align-self-center">
                        <div class="form-check form-switch form-switch-lg mt-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="validasi" name="validasi"
                                value="1" {{ old('validasi', 1) == 1 ? 'checked' : '' }}>
                            <label for="validasi" class="form-check-label">Aktif / Tervalidasi</label>
                        </div>
                    </div>
                </div>

                {{-- Kontainer Dinamis untuk Field Siswa & Pembimbing --}}
                <div class="row gy-4 mt-1" id="additional-fields" style="display: none;">
                    <hr>
                    <h5 class="text-muted">Informasi Tambahan</h5>
                    <div class="col-md-3">
                        <label for="sekolah_id" class="form-label fw-bold">Sekolah</label>
                        <select class="form-select" name="sekolah_id" id="sekolah_id">
                            <option value="">Pilih Sekolah</option>
                            @foreach ($sekolah as $val)
                                <option value="{{ $val->id }}" {{ old('sekolah_id') == $val->id ? 'selected' : '' }}>
                                    {{ $val->nama }}</option>
                            @endforeach
                        </select>
                        @error('sekolah_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="program_keahlian_id" class="form-label fw-bold">Program Keahlian</label>
                        <select class="form-select" name="program_keahlian_id" id="program_keahlian_id">
                            <option value="">Pilih Jurusan</option>
                            @foreach ($programKeahlian as $val)
                                <option value="{{ $val->id }}"
                                    {{ old('program_keahlian_id') == $val->id ? 'selected' : '' }}>{{ $val->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('program_keahlian_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <a href="{{ route('admin.user.index') }}" class="btn btn-secondary"><i
                            class="fa fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Fungsi untuk menampilkan/menyembunyikan field tambahan
            function toggleAdditionalFields() {
                var selectedGroupName = $('#group_id').find('option:selected').data('nama');

                // Cek apakah grup adalah 'siswa' ATAU 'pembimbing'
                if (selectedGroupName === 'siswa' || selectedGroupName === 'pembimbing') {
                    $('#additional-fields').slideDown();
                } else {
                    $('#additional-fields').slideUp();
                }
            }

            // Panggil fungsi saat dropdown group berubah
            $('#group_id').on('change', function() {
                toggleAdditionalFields();
            });

            // Panggil fungsi saat halaman pertama kali dimuat
            toggleAdditionalFields();
        });
    </script>
@endsection
