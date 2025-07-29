@extends('layout.main')

@section('css')
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .text-danger {
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }

        .button-navigate {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .section-header {
            background-color: #f8f9fa;
            padding: 0.75rem 1rem;
            margin: 0 -1rem 1rem -1rem;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }

        .required {
            color: #dc3545;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Collect Data</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="#">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('colect_data.index') }}">Collect Data</a>
                        </li>
                        <li class="breadcrumb-item active">Tambah Data</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fa fa-plus-circle me-2"></i>Tambah Data Survey
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('colect_data.store') }}" method="post" enctype="multipart/form-data"
                id="collectDataForm">
                @csrf

                <!-- Informasi Dasar -->
                <div class="section-header">
                    <i class="fa fa-info-circle me-2"></i>Informasi Dasar
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal" class="form-label">
                                Tanggal Survey <span class="required">*</span>
                            </label>
                            <input class="form-control @error('tanggal') is-invalid @enderror" type="date" name="tanggal"
                                id="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                            @error('tanggal')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- <div class="col-md-6">
                        <div class="form-group">
                            <label for="user_id" class="form-label">
                                Surveyor <span class="required">*</span>
                            </label>
                            <select class="form-select @error('user_id') is-invalid @enderror" name="user_id" id="user_id"
                                required>
                                <option value="">-- Pilih Surveyor --</option>
                                @foreach ($user as $val)
                                    <option value="{{ $val->id }}" {{ old('user_id') == $val->id ? 'selected' : '' }}>
                                        {{ $val->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div> --}}
                </div>

                <!-- Data Customer -->
                <div class="section-header mt-4">
                    <i class="fa fa-user me-2"></i>Data Customer
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_cus" class="form-label">
                                Nama Customer <span class="required">*</span>
                            </label>
                            <input class="form-control @error('nama_cus') is-invalid @enderror" type="text"
                                name="nama_cus" id="nama_cus" value="{{ old('nama_cus') }}"
                                placeholder="Masukkan nama customer" required>
                            @error('nama_cus')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_telp" class="form-label">
                                No. Telepon <span class="required">*</span>
                            </label>
                            <input class="form-control @error('no_telp') is-invalid @enderror" type="tel" name="no_telp"
                                id="no_telp" value="{{ old('no_telp') }}" placeholder="Contoh: 08123456789" required>
                            @error('no_telp')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="alamat_cus" class="form-label">
                                Alamat Customer <span class="required">*</span>
                            </label>
                            <textarea class="form-control @error('alamat_cus') is-invalid @enderror" name="alamat_cus" id="alamat_cus"
                                rows="3" placeholder="Masukkan alamat lengkap customer" required>{{ old('alamat_cus') }}</textarea>
                            @error('alamat_cus')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Informasi Provider -->
                <div class="section-header mt-4">
                    <i class="fa fa-wifi me-2"></i>Informasi Provider
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="provider_sekarang" class="form-label">
                                Provider Saat Ini <span class="required">*</span>
                            </label>
                            <input class="form-control @error('provider_sekarang') is-invalid @enderror" type="text"
                                name="provider_sekarang" id="provider_sekarang" value="{{ old('provider_sekarang') }}"
                                placeholder="Contoh: Telkom, Indihome, dll" required>
                            @error('provider_sekarang')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="serlok" class="form-label">Serial/Lokasi</label>
                            <input class="form-control @error('serlok') is-invalid @enderror" type="text" name="serlok"
                                id="serlok" value="{{ old('serlok') }}" placeholder="Serial number atau kode lokasi">
                            @error('serlok')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kelebihan" class="form-label">
                                Kelebihan Provider Saat Ini
                            </label>
                            <textarea class="form-control @error('kelebihan') is-invalid @enderror" name="kelebihan" id="kelebihan"
                                rows="3" placeholder="Jelaskan kelebihan provider yang digunakan saat ini">{{ old('kelebihan') }}</textarea>
                            @error('kelebihan')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kekurangan" class="form-label">
                                Kekurangan Provider Saat Ini
                            </label>
                            <textarea class="form-control @error('kekurangan') is-invalid @enderror" name="kekurangan" id="kekurangan"
                                rows="3" placeholder="Jelaskan kekurangan atau keluhan terhadap provider saat ini">{{ old('kekurangan') }}</textarea>
                            @error('kekurangan')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Dokumentasi -->
                <div class="section-header mt-4">
                    <i class="fa fa-camera me-2"></i>Dokumentasi
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="gambar_foto" class="form-label">Upload Foto</label>
                            <input class="form-control @error('gambar_foto') is-invalid @enderror" type="file"
                                name="gambar_foto" id="gambar_foto" accept="image/*">
                            <small class="form-text text-muted">
                                Format yang didukung: JPG, PNG, GIF. Maksimal 2MB.
                            </small>
                            @error('gambar_foto')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Preview Foto</label>
                            <div id="imagePreview" class="border rounded p-3 text-center"
                                style="min-height: 150px; background-color: #f8f9fa;">
                                <i class="fa fa-image fa-3x text-muted"></i>
                                <p class="text-muted mt-2">Preview foto akan muncul di sini</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="button-navigate mt-4">
                    <a href="{{ route('colect_data.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Phone number formatting
            $('#no_telp').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length > 0 && !value.startsWith('0')) {
                    value = '0' + value;
                }
                $(this).val(value);
            });

            // Image preview
            $('#gambar_foto').on('change', function() {
                const file = this.files[0];
                const preview = $('#imagePreview');

                if (file) {
                    // Check file size (2MB = 2097152 bytes)
                    if (file.size > 2097152) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File Terlalu Besar',
                            text: 'Ukuran file maksimal 2MB'
                        });
                        $(this).val('');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.html(`
                    <img src="${e.target.result}" class="img-fluid rounded" style="max-height: 200px;">
                `);
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.html(`
                <i class="fa fa-image fa-3x text-muted"></i>
                <p class="text-muted mt-2">Preview foto akan muncul di sini</p>
            `);
                }
            });

            // Form validation
            $('#collectDataForm').on('submit', function(e) {
                const requiredFields = ['tanggal', 'user_id', 'nama_cus', 'no_telp', 'alamat_cus',
                    'provider_sekarang'
                ];
                let isValid = true;

                requiredFields.forEach(function(field) {
                    const input = $(`#${field}`);
                    if (!input.val().trim()) {
                        input.addClass('is-invalid');
                        isValid = false;
                    } else {
                        input.removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Form Tidak Lengkap',
                        text: 'Mohon lengkapi semua field yang wajib diisi'
                    });
                }
            });

            // Remove validation error on input
            $('.form-control, .form-select').on('input change', function() {
                $(this).removeClass('is-invalid');
            });
        });
    </script>
@endsection
