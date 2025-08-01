@extends('layout.main')

@section('css')
    <style>
        .form-group {
            margin-bottom: 0.75rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.25rem;
            display: block;
            font-size: 0.9rem;
        }

        .required {
            color: #dc3545;
        }

        .form-control, .form-select {
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
        }

        .preview-container {
            border: 2px dashed #dee2e6;
            border-radius: 0.375rem;
            padding: 0.75rem;
            text-align: center;
            background-color: #f8f9fa;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            position: relative;
        }

        .preview-container.has-image {
            border-color: #28a745;
            background-color: #f8fff9;
        }

        .preview-image, .current-image {
            max-width: 100%;
            max-height: 60px;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
        }

        .remove-preview {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 10px;
            cursor: pointer;
            position: absolute;
            top: 3px;
            right: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-body {
            padding: 1.25rem;
        }

        .text-danger {
            font-size: 0.8rem;
            margin-top: 0.2rem;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 60px;
        }

        .btn-group-form {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
        }

        /* Compact row spacing */
        .row {
            margin-bottom: 0.5rem;
        }

        .row:last-child {
            margin-bottom: 0;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }
            
            .btn-group-form {
                flex-direction: column;
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
                <h4 class="mb-sm-0 font-size-18">Edit Collect Data</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('colect_data.index') }}">Collect Data</a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
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
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li><small>{{ $error }}</small></li>
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
            <form action="{{ route('colect_data.update', $colect_data->id) }}" method="post" enctype="multipart/form-data" id="collectDataForm">
                @csrf
                @method('PUT')

                <!-- Row 1: Tanggal & Nama Customer -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tanggal" class="form-label">
                                Tanggal <span class="required">*</span>
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
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="nama_cus" class="form-label">
                                Nama Customer <span class="required">*</span>
                            </label>
                            <input class="form-control @error('nama_cus') is-invalid @enderror" 
                                   type="text"
                                   name="nama_cus" 
                                   id="nama_cus" 
                                   value="{{ old('nama_cus', $colect_data->nama_cus) }}"
                                   placeholder="Nama customer" 
                                   required>
                            @error('nama_cus')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="no_telp" class="form-label">No. Telepon</label>
                            <input class="form-control @error('no_telp') is-invalid @enderror" 
                                   type="text"
                                   name="no_telp"
                                   id="no_telp"
                                   value="{{ old('no_telp', $colect_data->no_telp) }}"
                                   placeholder="No HP/Alasan">
                            @error('no_telp')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Row 2: Alamat & Provider -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="alamat_cus" class="form-label">
                                Alamat Customer <span class="required">*</span>
                            </label>
                            <textarea class="form-control @error('alamat_cus') is-invalid @enderror" 
                                      name="alamat_cus" 
                                      id="alamat_cus"
                                      rows="2" 
                                      placeholder="Alamat lengkap customer" 
                                      required>{{ old('alamat_cus', $colect_data->alamat_cus) }}</textarea>
                            @error('alamat_cus')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="provider_sekarang" class="form-label">
                                Provider <span class="required">*</span>
                            </label>
                            <input class="form-control @error('provider_sekarang') is-invalid @enderror" 
                                   type="text"
                                   name="provider_sekarang" 
                                   id="provider_sekarang" 
                                   value="{{ old('provider_sekarang', $colect_data->provider_sekarang) }}"
                                   placeholder="Telkom, Indihome, dll" 
                                   required>
                            @error('provider_sekarang')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="serlok" class="form-label">Serial/Lokasi</label>
                            <input class="form-control @error('serlok') is-invalid @enderror" 
                                   type="text" 
                                   name="serlok"
                                   id="serlok" 
                                   value="{{ old('serlok', $colect_data->serlok) }}" 
                                   placeholder="Serial/kode lokasi">
                            @error('serlok')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Row 3: Kelebihan & Kekurangan -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="kelebihan" class="form-label">Kelebihan Provider</label>
                            <textarea class="form-control @error('kelebihan') is-invalid @enderror" 
                                      name="kelebihan" 
                                      id="kelebihan"
                                      rows="2" 
                                      placeholder="Kelebihan provider saat ini">{{ old('kelebihan', $colect_data->kelebihan) }}</textarea>
                            @error('kelebihan')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="kekurangan" class="form-label">Kekurangan Provider</label>
                            <textarea class="form-control @error('kekurangan') is-invalid @enderror" 
                                      name="kekurangan" 
                                      id="kekurangan"
                                      rows="2" 
                                      placeholder="Kekurangan/keluhan provider">{{ old('kekurangan', $colect_data->kekurangan) }}</textarea>
                            @error('kekurangan')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="gambar_foto" class="form-label">Upload Foto</label>
                            <input class="form-control @error('gambar_foto') is-invalid @enderror" 
                                   type="file"
                                   name="gambar_foto" 
                                   id="gambar_foto" 
                                   accept="image/*"
                                   style="margin-bottom: 0.5rem;">
                            
                            <!-- Preview Container -->
                            <div class="preview-container" id="preview-container">
                                @if($colect_data->gambar_foto && file_exists(public_path('uploads/colect_data_gambar_foto/' . $colect_data->gambar_foto)))
                                    <img src="{{ asset('uploads/colect_data_gambar_foto/' . $colect_data->gambar_foto) }}" 
                                         alt="Foto Survey" 
                                         class="current-image">
                                    <small class="text-success">{{ basename($colect_data->gambar_foto) }}</small>
                                @elseif($colect_data->gambar_foto)
                                    <i class="fa fa-exclamation-triangle text-warning"></i>
                                    <small class="text-warning">File tidak ditemukan</small>
                                @else
                                    <i class="fa fa-image text-muted"></i>
                                    <small class="text-muted">Preview foto</small>
                                @endif
                            </div>
                            
                            @error('gambar_foto')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                JPG, PNG, GIF - Max 2MB
                                @if($colect_data->gambar_foto)
                                    <br>Upload file baru untuk mengganti
                                @endif
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="btn-group-form">
                    <a href="{{ route('colect_data.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
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
            const $fileInput = $('#gambar_foto');
            const $previewContainer = $('#preview-container');
            const $submitBtn = $('#submitBtn');

            // Handle file input change for photo preview
            $fileInput.on('change', function(e) {
                handleFileSelect(e.target.files[0]);
            });

            // Handle file selection and preview
            function handleFileSelect(file) {
                if (!file) {
                    resetPreview();
                    return;
                }

                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.');
                    $fileInput.val('');
                    resetPreview();
                    return;
                }

                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                    $fileInput.val('');
                    resetPreview();
                    return;
                }

                // Create file reader
                const reader = new FileReader();
                reader.onload = function(e) {
                    updatePreview(e.target.result, file);
                };
                reader.readAsDataURL(file);
            }

            // Update preview display
            function updatePreview(imageSrc, file) {
                $previewContainer.addClass('has-image').html(`
                    <img src="${imageSrc}" alt="Preview" class="preview-image">
                    <small class="text-success">${file.name}</small>
                    <button type="button" class="remove-preview" onclick="removePreview()">
                        <i class="fa fa-times"></i>
                    </button>
                `);
            }

            // Reset preview to default state (keep current image if exists)
            function resetPreview() {
                @if($colect_data->gambar_foto && file_exists(public_path('uploads/colect_data_gambar_foto/' . $colect_data->gambar_foto)))
                    $previewContainer.removeClass('has-image').html(`
                        <img src="{{ asset('uploads/colect_data_gambar_foto/' . $colect_data->gambar_foto) }}" 
                             alt="Foto Survey" 
                             class="current-image">
                        <small class="text-success">{{ basename($colect_data->gambar_foto) }}</small>
                    `);
                @else
                    $previewContainer.removeClass('has-image').html(`
                        <i class="fa fa-image text-muted"></i>
                        <small class="text-muted">Preview foto</small>
                    `);
                @endif
            }

            // Make functions available globally
            window.removePreview = function() {
                $fileInput.val('');
                resetPreview();
            };

            // Form submission with loading state
            $('#collectDataForm').on('submit', function() {
                $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');
            });

            // Auto-resize textareas
            $('textarea').on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });
    </script>
@endsection