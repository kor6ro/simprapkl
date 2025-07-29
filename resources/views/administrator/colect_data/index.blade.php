@extends('layout.main')

@section('css')
<style>
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
    
    .btn-action {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .row-action {
        display: flex;
        gap: 0.25rem;
        justify-content: center;
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    /* Custom style for photo preview */
    .photo-preview {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .photo-preview:hover {
        border-color: #007bff;
        transform: scale(1.05);
    }

    /* Custom SweetAlert styles for photo modal */
    .swal2-popup.photo-modal {
        padding: 10px !important;
    }
    
    .photo-container {
        position: relative;
        text-align: center;
    }
    
    .photo-main {
        max-width: 100%;
        max-height: 70vh;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .photo-info {
        margin-top: 10px;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 4px;
        font-size: 0.9em;
        color: #666;
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
                    <li class="breadcrumb-item active">Collect Data</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-auto">
        <a href="{{ route('colect_data.create') }}" class="btn btn-success">
            <i class="fa fa-plus me-1"></i> Tambah Data
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Data Survey</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="dataTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col" width="50">#</th>
                        <th scope="col">Surveyor</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Nama Customer</th>
                        <th scope="col">No. Telepon</th>
                        <th scope="col">Alamat</th>
                        <th scope="col">Provider Saat Ini</th>
                        <th scope="col">Kelebihan</th>
                        <th scope="col">Kekurangan</th>
                        <th scope="col">Serial/Lokasi</th>
                        <th scope="col">Foto</th>
                        <th scope="col" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data akan dimuat melalui DataTables -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Hidden form for delete action -->
<form id="form-destroy" action="{{ route('colect_data.store') }}" method="post" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        language: {
            processing: "Memuat data...",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },
        ajax: {
            url: baseUrl('/colect_data/fetch'),
            type: "POST",
            headers: {
                'X-XSRF-TOKEN': getCookie('XSRF-TOKEN')
            },
            dataSrc: "data"
        },
        order: [[2, 'desc']], // Order by tanggal descending
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'user.name',
                name: 'user.name',
                orderable: false,
                searchable: false
            },
            {
                data: 'tanggal',
                name: 'tanggal',
                render: function(data) {
                    if (!data) return "";
                    const date = new Date(data);
                    return date.toLocaleDateString('id-ID');
                }
            },
            {
                data: 'nama_cus',
                name: 'nama_cus'
            },
            {
                data: 'no_telp',
                name: 'no_telp'
            },
            {
                data: 'alamat_cus',
                name: 'alamat_cus',
                render: function(data, type, row) {
                    if (!data) return "";
                    // Tampilkan tombol Detail saja
                    return `<a href="#" class="alamat-detail" data-alamat="${encodeURIComponent(data)}">
                                <span class="badge bg-info">Detail</span>
                            </a>`;
                }
            },
            {
                data: 'provider_sekarang',
                name: 'provider_sekarang'
            },
            {
                data: 'kelebihan',
                name: 'kelebihan',
                render: function(data) {
                    return data && data.length > 30 ? 
                        data.substring(0, 30) + '...' : data;
                }
            },
            {
                data: 'kekurangan',
                name: 'kekurangan',
                render: function(data) {
                    return data && data.length > 30 ? 
                        data.substring(0, 30) + '...' : data;
                }
            },
            {
                data: 'serlok',
                name: 'serlok',
                render: function(data) {
                    if (!data) return "";
                    // Jika data sudah berupa link, langsung pakai
                    // Jika hanya koordinat/link Google Maps, buat link
                    return `<a href="${data}" target="_blank" rel="noopener" class="badge bg-primary">Lihat Lokasi</a>`;
                }
            },
            {
                data: 'gambar_foto',
                name: 'gambar_foto',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (data) {
                        // Clean the path - remove any unexpected segments
                        let cleanPath = data;
                        
                        // Remove 'document' if exists (seems to be error in path)
                        cleanPath = cleanPath.replace(/^document[\/\\]?/i, '');
                        
                        // Ensure we have the right path structure
                        if (!cleanPath.startsWith('dokumentasi/')) {
                            cleanPath = 'dokumentasi/' + cleanPath;
                        }
                        
                        // Build URL - check if we're in admin context
                        const currentPath = window.location.pathname;
                        const isAdminContext = currentPath.includes('/admin/');
                        
                        // For admin context, use root storage path
                        const storageBase = isAdminContext ? '/storage/' : baseUrl('/storage/');
                        const url = (storageBase + cleanPath).replace(/\/+/g, '/'); // Remove double slashes
                        
                        // Fix URL to use absolute path from domain root
                        const finalUrl = url.startsWith('http') ? url : 
                                        window.location.origin + (url.startsWith('/') ? url : '/' + url);
                        
                        const customerName = row.nama_cus || 'Customer';
                        const surveyDate = row.tanggal ? new Date(row.tanggal).toLocaleDateString('id-ID') : '';
                        
                        console.log('Debug Info:', {
                            originalData: data,
                            cleanPath: cleanPath,
                            finalUrl: finalUrl,
                            isAdminContext: isAdminContext
                        });
                        
                        return `<a href="#" class="foto-detail" 
                                   data-url="${finalUrl}" 
                                   data-customer="${customerName}"
                                   data-date="${surveyDate}"
                                   data-filename="${data}"
                                   data-clean-path="${cleanPath}"
                                   title="Klik untuk melihat foto">
                                    <img src="${finalUrl}" 
                                         alt="Foto Survey ${customerName}" 
                                         class="photo-preview"
                                         style="max-width:40px;max-height:40px;border-radius:4px;border:1px solid #ccc;"
                                         onerror="handleImageError(this)" />
                                </a>`;
                    } else {
                        return '<span class="badge bg-secondary">Tidak ada</span>';
                    }
                }
            },
            {
                data: 'id',
                name: 'actions',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <div class="row-action">
                            <button type="button" class="btn btn-warning btn-action action-edit" 
                                    data-id="${data}" title="Edit">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-action action-delete" 
                                    data-id="${data}" title="Hapus">
                                <i class="fa fa-trash-alt"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        createdRow: function(row, data, dataIndex) {
            // Edit button handler
            $(row).find('.action-edit').on('click', function() {
                const id = $(this).data('id');
                const url = baseUrl('/colect_data/' + id + '/edit');
                window.location.href = url;
            });

            // Delete button handler
            $(row).find('.action-delete').on('click', function() {
                const id = $(this).data('id');
                
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: "Apakah Anda yakin ingin menghapus data ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $('#form-destroy');
                        const baseAction = form.attr('action');
                        form.attr('action', baseAction + '/' + id);
                        form.submit();
                    }
                });
            });

            // Handler klik alamat detail
            $(row).find('.alamat-detail').on('click', function(e) {
                e.preventDefault();
                const alamat = decodeURIComponent($(this).data('alamat'));
                Swal.fire({
                    title: 'Detail Alamat',
                    html: `<div style="text-align:left;">${alamat}</div>`,
                    icon: 'info',
                    confirmButtonText: 'Tutup'
                });
            });

            // Handler untuk foto detail - UPDATED
            $(row).find('.foto-detail').on('click', function(e) {
                e.preventDefault();
                
                const url = $(this).data('url');
                const customerName = $(this).data('customer');
                const surveyDate = $(this).data('date');
                const filename = $(this).data('filename');
                
                // Show loading first
                Swal.fire({
                    title: 'Memuat foto...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Create image element to preload
                const img = new Image();
                
                img.onload = function() {
                    // Image loaded successfully
                    const photoInfo = `
                        <div class="photo-info">
                            <strong>Customer:</strong> ${customerName}<br>
                            <strong>Tanggal Survey:</strong> ${surveyDate}<br>
                            <strong>File:</strong> ${filename}
                        </div>
                    `;
                    
                    Swal.fire({
                        title: 'Foto Dokumentasi Survey',
                        html: `
                            <div class="photo-container">
                                <img src="${url}" 
                                     alt="Foto Survey ${customerName}" 
                                     class="photo-main" />
                                ${photoInfo}
                            </div>
                        `,
                        showCloseButton: true,
                        showConfirmButton: true,
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#6c757d',
                        width: 'auto',
                        customClass: {
                            popup: 'photo-modal'
                        },
                        didOpen: () => {
                            // Add click handler to image for full-screen view
                            const modalImg = Swal.getPopup().querySelector('.photo-main');
                            modalImg.style.cursor = 'pointer';
                            modalImg.title = 'Klik untuk melihat ukuran penuh';
                            
                            modalImg.addEventListener('click', function() {
                                // Open in new tab for full-screen view
                                window.open(url, '_blank');
                            });
                        }
                    });
                };
                
                img.onerror = function() {
                    // Image failed to load - show detailed error info
                    const testUrls = [
                        url,
                        window.location.origin + '/storage/' + cleanPath,
                        window.location.origin + '/storage/dokumentasi/' + filename.replace(/^dokumentasi\//, ''),
                        baseUrl('/storage/dokumentasi/' + filename.replace(/^dokumentasi\//, ''))
                    ];
                    
                    Swal.fire({
                        title: 'Error Loading Photo',
                        html: `
                            <div style="text-align: left;">
                                <p><strong>Gagal memuat foto.</strong></p>
                                <hr>
                                <p><strong>Info Debug:</strong></p>
                                <p><small><strong>File Database:</strong> ${filename}</small></p>
                                <p><small><strong>Clean Path:</strong> ${cleanPath}</small></p>
                                <p><small><strong>Current URL:</strong> ${url}</small></p>
                                <hr>
                                <p><strong>Coba URL berikut di browser:</strong></p>
                                ${testUrls.map((testUrl, index) => 
                                    `<p><small>${index + 1}. <a href="${testUrl}" target="_blank">${testUrl}</a></small></p>`
                                ).join('')}
                                <hr>
                                <p><small><strong>Pastikan:</strong></p>
                                <p><small>1. File ada di: storage/app/public/dokumentasi/</small></p>
                                <p><small>2. Symlink dibuat: php artisan storage:link</small></p>
                                <p><small>3. Permission folder 755, file 644</small></p>
                            </div>
                        `,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        width: 600
                    });
                };
                
                // Start loading the image
                img.src = url;
            });
        }
    });
});

// Function to handle image errors globally
function handleImageError(img) {
    img.style.display = 'none';
    const parent = img.parentNode;
    parent.innerHTML = '<span class="badge bg-danger" title="File foto tidak ditemukan">Error</span>';
}
</script>

<!-- Success/Error Messages -->
@if (session()->has('dataSaved'))
    <script>
        $(document).ready(function() {
            @if (session()->get('dataSaved') == true)
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session()->get('message') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @else
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{{ session()->get('message') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif
        });
    </script>
@endif
@endsection