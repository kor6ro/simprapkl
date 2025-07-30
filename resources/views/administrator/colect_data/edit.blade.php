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

        .preview-container {
            border: 2px dashed #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            text-align: center;
            background-color: #f8f9fa;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .current-image {
            max-width: 200px;
            max-height: 150px;
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
        }

        .alert {
            margin-bottom: 1rem;
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
                        <li class="breadcrumb-item active">Edit Data</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-triangle me-2"></i>
            <strong>Terdapat kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fa fa-edit me-2"></i>Edit Data Survey
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('colect_data.update', $colect_data->id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')

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
                            <input class="form-control @error('tanggal') is-invalid @enderror" 
                                   type="date" 
                                   name="tanggal"
                                   id="tanggal" 
                                   value="{{ old('tanggal', $colect_data->tanggal) }}" 
                                   required>
                            @error('tanggal')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="user_id" class="form-label">
                                Surveyor <span class="required">*</span>
                            </label>
                            <select class="form-select @error('user_id') is-invalid @enderror" 
                                    name="user_id" 
                                    id="user_id"
                                    required>
                                <option value="">-- Pilih Surveyor --</option>
                                @foreach ($user as $val)
                                    <option value="{{ $val->id }}" 
                                            {{ old('user_id', $colect_data->user_id) == $val->id ? 'selected' : '' }}>
                                        {{ $val->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
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
                            <input class="form-control @error('nama_cus') is-invalid @enderror" 
                                   type="text"
                                   name="nama_cus" 
                                   id="nama_cus" 
                                   value="{{ old('nama_cus', $colect_data->nama_cus) }}"
                                   placeholder="Masukkan nama customer" 
                                   required>
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
                            <input class="form-control @error('no_telp') is-invalid @enderror" 
                                   type="tel" 
                                   name="no_telp"
                                   id="no_telp" 
                                   value="{{ old('no_telp', $colect_data->no_telp) }}" 
                                   placeholder="Contoh: 08123456789" 
                                   pattern="[0-9]*"
                                   required>
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
                            <textarea class="form-control @error('alamat_cus') is-invalid @enderror" 
                                      name="alamat_cus" 
                                      id="alamat_cus"
                                      rows="3" 
                                      placeholder="Masukkan alamat lengkap customer" 
                                      required>{{ old('alamat_cus', $colect_data->alamat_cus) }}</textarea>
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
                            <input class="form-control @error('provider_sekarang') is-invalid @enderror" 
                                   type="text"
                                   name="provider_sekarang" 
                                   id="provider_sekarang" 
                                   value="{{ old('provider_sekarang', $colect_data->provider_sekarang) }}"
                                   placeholder="Contoh: Telkom, Indihome, dll" 
                                   required>
                            @error('provider_sekarang')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="serlok" class="form-label">Serial/Lokasi</label>
                            <input class="form-control @error('serlok') is-invalid @enderror" 
                                   type="text" 
                                   name="serlok"
                                   id="serlok" 
                                   value="{{ old('serlok', $colect_data->serlok) }}" 
                                   placeholder="Serial number atau kode lokasi">
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
                            <textarea class="form-control @error('kelebihan') is-invalid @enderror" 
                                      name="kelebihan" 
                                      id="kelebihan"
                                      rows="3" 
                                      placeholder="Jelaskan kelebihan provider yang digunakan saat ini">{{ old('kelebihan', $colect_data->kelebihan) }}</textarea>
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
                            <textarea class="form-control @error('kekurangan') is-invalid @enderror" 
                                      name="kekurangan" 
                                      id="kekurangan"
                                      rows="3" 
                                      placeholder="Jelaskan kekurangan atau keluhan terhadap provider saat ini">{{ old('kekurangan', $colect_data->kekurangan) }}</textarea>
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
                            <input class="form-control @error('gambar_foto') is-invalid @enderror" 
                                   type="file"
                                   name="gambar_foto" 
                                   id="gambar_foto" 
                                   accept="image/jpeg,image/png,image/gif">
                            <small class="form-text text-muted">
                                Format yang didukung: JPG, PNG, GIF. Maksimal 2MB.
                                @if($colect_data->gambar_foto)
                                    <br><strong>File saat ini:</strong> {{ basename($colect_data->gambar_foto) }}
                                @endif
                            </small>
                            @error('gambar_foto')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                @if($colect_data->gambar_foto)
                                    Foto Saat Ini
                                @else
                                    Informasi Upload
                                @endif
                            </label>
                            <div class="preview-container">
                                @if($colect_data->gambar_foto && file_exists(public_path('storage/' . $colect_data->gambar_foto)))
                                    <img src="{{ asset('storage/' . $colect_data->gambar_foto) }}" 
                                         alt="Foto Survey" 
                                         class="current-image">
                                    <p class="text-muted mt-2 mb-0">
                                        <small>Upload file baru untuk mengganti foto ini</small>
                                    </p>
                                @elseif($colect_data->gambar_foto)
                                    <i class="fa fa-exclamation-triangle fa-2x text-warning"></i>
                                    <p class="text-warning mt-2 mb-0">
                                        File tidak ditemukan<br>
                                        <small>{{ $colect_data->gambar_foto }}</small>
                                    </p>
                                @else
                                    <i class="fa fa-image fa-3x text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">
                                        Belum ada foto<br>
                                        <small>JPG, PNG, GIF - Max 2MB</small>
                                    </p>
                                @endif
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
                        <i class="fa fa-save me-1"></i> Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection