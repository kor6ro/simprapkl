@extends('layout.main')

@section('css')
    <style>
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
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
            font-size: 1rem;
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
            transition: all 0.3s ease;
            position: relative;
        }

        .preview-container.has-image {
            border-color: #28a745;
            background-color: #f8fff9;
        }

        .preview-image {
            max-width: 100%;
            max-height: 100px;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .preview-info {
            font-size: 0.875rem;
            color: #495057;
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
            position: absolute;
            top: 5px;
            right: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .remove-preview:hover {
            background: #c82333;
        }

        .button-navigate {
            padding-top: 1rem;
            margin-top: 1.5rem;
            border-top: 1px solid #dee2e6;
            display: flex;
            gap: 0.5rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .is-invalid {
            border-color: #dc3545;
        }

        .text-danger {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Drag and drop styling */
        .preview-container.drag-over {
            border-color: #0d6efd;
            background-color: #e7f3ff;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .section-header {
                margin-left: -1rem;
                margin-right: -1rem;
                font-size: 0.9rem;
            }
            
            .button-navigate {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .form-control, 
            .form-select {
                font-size: 16px; /* Prevent zoom on iOS */
                padding: 12px 15px; /* Extra padding for better UX */
            }

            .col-md-6 {
                margin-bottom: 1rem;
            }

            /* Fix placeholder truncation on mobile */
            .form-control::placeholder {
                font-size: 14px;
                color: #6c757d;
                opacity: 1;
            }

            /* Specific fix for phone number field */
            #no_telp {
                min-width: 0; /* Allow flex shrinking */
                width: 100%;
            }

            #no_telp::placeholder {
                font-size: 13px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }

        /* Loading state */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }

        .btn-loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                            <a href="{{ route('dashboard') }}">Home</a>
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

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fa fa-plus-circle me-2"></i>Tambah Data Survey
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('colect_data.store') }}" method="post" enctype="multipart/form-data" id="collectDataForm">
                @csrf

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
                                   value="{{ old('tanggal', date('Y-m-d')) }}" 
                                   required>
                            @error('tanggal')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
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
                                   value="{{ old('nama_cus') }}"
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
                                   value="{{ old('no_telp') }}"
                                   placeholder="Isi Nomer Atau Alasan">
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
                                      required>{{ old('alamat_cus') }}</textarea>
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
                                   value="{{ old('provider_sekarang') }}"
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
                                   value="{{ old('serlok') }}" 
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
                                      placeholder="Jelaskan kelebihan provider yang digunakan saat ini">{{ old('kelebihan') }}</textarea>
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
                                      placeholder="Jelaskan kekurangan atau keluhan terhadap provider saat ini">{{ old('kekurangan') }}</textarea>
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
                                   accept="image/jpeg,image/png,image/gif,image/jpg">
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
                            <div class="preview-container" id="preview-container">
                                <i class="fa fa-image fa-3x text-muted" id="preview-icon"></i>
                                <p class="text-muted mt-2 mb-0" id="preview-text">
                                    Pilih file gambar untuk diupload<br>
                                    <small>JPG, PNG, GIF - Max 2MB</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="button-navigate">
                    <a href="{{ route('colect_data.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
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
            const $form = $('#collectDataForm');
            const $fileInput = $('#gambar_foto');
            const $previewContainer = $('#preview-container');
            const $submitBtn = $('#submitBtn');

            // No automatic formatting - let user input freely
            // $('#no_telp').on('input', function() {
            //     // Removed automatic phone formatting
            // });

            // Handle file input change for photo preview
            $fileInput.on('change', function(e) {
                handleFileSelect(e.target.files[0]);
            });

            // Drag and drop functionality
            $previewContainer.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('drag-over');
            });

            $previewContainer.on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('drag-over');
            });

            $previewContainer.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('drag-over');
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelect(files[0]);
                    // Update file input
                    const dt = new DataTransfer();
                    dt.items.add(files[0]);
                    $fileInput[0].files = dt.files;
                }
            });

            // Handle file selection
            function handleFileSelect(file) {
                if (!file) {
                    resetPreview();
                    return;
                }

                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    showAlert('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.', 'danger');
                    $fileInput.val('');
                    resetPreview();
                    return;
                }

                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showAlert('Ukuran file terlalu besar. Maksimal 2MB.', 'danger');
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
                    <div class="preview-info">
                        <strong>${file.name}</strong><br>
                        <small>${(file.size / 1024).toFixed(1)} KB</small>
                    </div>
                    <button type="button" class="remove-preview" onclick="removePreview()">
                        <i class="fa fa-times"></i>
                    </button>
                `);
            }

            // Reset preview to default state
            function resetPreview() {
                $previewContainer.removeClass('has-image').html(`
                    <i class="fa fa-image fa-3x text-muted" id="preview-icon"></i>
                    <p class="text-muted mt-2 mb-0" id="preview-text">
                        Pilih file gambar untuk diupload<br>
                        <small>JPG, PNG, GIF - Max 2MB</small>
                    </p>
                `);
            }

            // Make functions available globally
            window.resetPreview = resetPreview;
            window.removePreview = function() {
                $fileInput.val('');
                resetPreview();
            };

            // Form validation and submission
            $form.on('submit', function(e) {
                e.preventDefault();
                
                if (!validateForm()) {
                    return false;
                }

                // Show loading state
                $submitBtn.addClass('btn-loading').prop('disabled', true);
                $submitBtn.find('i').removeClass('fa-save').addClass('fa-spinner fa-spin');

                // Submit form
                this.submit();
            });

            // Form validation function
            function validateForm() {
                let isValid = true;
                const requiredFields = [
                    { name: 'tanggal', label: 'Tanggal Survey' },
                    { name: 'nama_cus', label: 'Nama Customer' },
                    { name: 'alamat_cus', label: 'Alamat Customer' },
                    { name: 'provider_sekarang', label: 'Provider Saat Ini' }
                ];
                
                // Clear previous validation states
                $('.form-control, .form-select').removeClass('is-invalid');
                $('.text-danger').not('.error-message').remove();

                requiredFields.forEach(function(field) {
                    const $input = $(`[name="${field.name}"]`);
                    const value = $input.val().trim();
                    
                    if (!value) {
                        $input.addClass('is-invalid');
                        if (!$input.siblings('.text-danger').length) {
                            $input.after(`<div class="text-danger error-message">${field.label} wajib diisi.</div>`);
                        }
                        isValid = false;
                    }
                });

                // Simple phone validation - just check if not empty and reasonable length
                const phoneValue = $('#no_telp').val().trim();
                if (phoneValue) {
                    // If it contains numbers and is too short, show warning
                    if (/\d/.test(phoneValue) && phoneValue.replace(/\D/g, '').length < 8) {
                        $('#no_telp').addClass('is-invalid');
                        if (!$('#no_telp').siblings('.text-danger').length) {
                            $('#no_telp').after('<div class="text-danger error-message">Nomor telepon terlalu pendek.</div>');
                        }
                        isValid = false;
                    }
                }

                if (!isValid) {
                    showAlert('Mohon lengkapi semua field yang wajib diisi dengan benar.', 'danger');
                    // Scroll to first error
                    const firstError = $('.is-invalid').first();
                    if (firstError.length) {
                        $('html, body').animate({
                            scrollTop: firstError.offset().top - 100
                        }, 300);
                    }
                }

                return isValid;
            }

            // Real-time validation
            $('.form-control, .form-select').on('input change', function() {
                $(this).removeClass('is-invalid');
                $(this).siblings('.error-message').remove();
            });

            // Show alert function
            function showAlert(message, type = 'info') {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        <i class="fa fa-${type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                
                $('.card').before(alertHtml);
                
                // Auto dismiss after 5 seconds
                setTimeout(function() {
                    $('.alert').not('.alert-danger').fadeOut();
                }, 5000);
            }

            // Auto-resize textareas
            $('textarea').each(function() {
                this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
            }).on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });
    </script>
@endsection