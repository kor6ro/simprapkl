@extends('layout.main')

@section('css')
    <style>
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .required {
            color: #dc3545;
        }

        .section-header {
            background-color: #f8f9fa;
            padding: 0.75rem 1rem;
            margin: 1.5rem -1rem 1rem -1rem;
            border-left: 3px solid #0d6efd;
            font-weight: 500;
        }

        .section-header:first-of-type {
            margin-top: 0;
        }

        .preview-container {
            border: 2px dashed #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            text-align: center;
            background-color: #f8f9fa;
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .preview-container.has-image {
            border-color: #28a745;
        }

        .current-image {
            max-width: 100%;
            max-height: 100px;
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
        }

        .preview-image {
            max-width: 100%;
            max-height: 100px;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
        }

        .remove-preview {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 12px;
            cursor: pointer;
            margin-top: 0.5rem;
        }

        .button-navigate {
            padding-top: 1rem;
            margin-top: 1.5rem;
            border-top: 1px solid #dee2e6;
            display: flex;
            gap: 0.5rem;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .section-header {
                margin-left: -1rem;
                margin-right: -1rem;
            }
            
            .button-navigate {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .form-control, .form-select {
                font-size: 16px; /* Prevent zoom on iOS */
            }
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

                {{-- <!-- Informasi Dasar -->
                <div class="section-header">
                    <i class="fa fa-info-circle me-2"></i>Informasi Dasar
                </div> --}}

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

                
                <!-- Data Customer -->
                <div class="section-header">
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
                            No. Telepon <small class="text-muted">(opsional)</small>
                        </label>
                        <input class="form-control @error('no_telp') is-invalid @enderror" 
                            type="text" 
                            name="no_telp"
                            id="no_telp" 
                            value="{{ old('no_telp', $colect_data->no_telp) }}"  
                            placeholder="Isi nomor atau alasan">
                        @error('no_telp')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
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
                <div class="section-header">
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
                <div class="section-header">
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
                            <label class="form-label">Preview Foto</label>
                            <div class="preview-container" id="preview-container">
                                @if($colect_data->gambar_foto && file_exists(public_path('uploads/colect_data_gambar_foto/' . $colect_data->gambar_foto)))
                                    <img src="{{ asset('uploads/colect_data_gambar_foto/' . $colect_data->gambar_foto) }}" 
                                         alt="Foto Survey" 
                                         class="current-image">
                                    <p class="text-muted mt-2 mb-0">
                                        <small>Upload file baru untuk mengganti</small>
                                    </p>
                                @elseif($colect_data->gambar_foto)
                                    <i class="fa fa-exclamation-triangle fa-2x text-warning"></i>
                                    <p class="text-warning mt-2 mb-0">
                                        File tidak ditemukan<br>
                                        <small>{{ $colect_data->gambar_foto }}</small>
                                    </p>
                                @else
                                    <i class="fa fa-image fa-3x text-muted" id="preview-icon"></i>
                                    <p class="text-muted mt-2 mb-0" id="preview-text">
                                        Belum ada foto<br>
                                        <small>JPG, PNG, GIF - Max 2MB</small>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="button-navigate">
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

@section('js')
    <script>
        $(document).ready(function() {
            // Handle file input change for photo preview
            $('#gambar_foto').on('change', function(e) {
                const file = e.target.files[0];
                const previewContainer = $('#preview-container');

                if (file) {
                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!validTypes.includes(file.type)) {
                        alert('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.');
                        $(this).val('');
                        return;
                    }

                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file terlalu besar. Maksimal 2MB.');
                        $(this).val('');
                        return;
                    }

                    // Create file reader
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Update preview container
                        previewContainer.addClass('has-image');
                        previewContainer.html(`
                            <img src="${e.target.result}" alt="Preview" class="preview-image">
                            <div class="preview-info">
                                <strong>${file.name}</strong><br>
                                <small>${(file.size / 1024).toFixed(1)} KB</small>
                            </div>
                            <button type="button" class="remove-preview" onclick="removePreview()">
                                <i class="fa fa-times"></i>
                            </button>
                        `);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        // Function to remove preview (called from button)
        function removePreview() {
            $('#gambar_foto').val('');
            const previewContainer = $('#preview-container');
            previewContainer.removeClass('has-image');
            previewContainer.html(`
                <i class="fa fa-image fa-3x text-muted"></i>
                <p class="text-muted mt-2 mb-0">
                    Pilih file gambar untuk diupload<br>
                    <small>JPG, PNG, GIF - Max 2MB</small>
                </p>
            `);
        }

        // Form validation before submit
        $('form').on('submit', function(e) {
            let isValid = true;
            const requiredFields = ['tanggal', 'user_id', 'nama_cus', 'no_telp', 'alamat_cus', 'provider_sekarang'];
            
            requiredFields.forEach(function(field) {
                const input = $(`[name="${field}"]`);
                if (!input.val().trim()) {
                    input.addClass('is-invalid');
                    isValid = false;
                } else {
                    input.removeClass('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi.');
                return false;
            }
        });

        // Remove validation error on input
        $('.form-control, .form-select').on('input change', function() {
            $(this).removeClass('is-invalid');
        });
    </script>
@endsection