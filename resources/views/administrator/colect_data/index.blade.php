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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .photo-info {
            margin-top: 10px;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 0.9em;
            color: #666;
        }

        /* Style for scrollable address */
        .address-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 8px;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            background-color: #f8f9fa;
        }

        .address-cell::-webkit-scrollbar {
            height: 4px;
        }

        .address-cell::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 2px;
        }

        .address-cell::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 2px;
        }

        .address-cell::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* ===== MODAL COMPACT STYLES ===== */
        /* Compact modal styles */
        .swal2-popup.detail-modal-compact {
            padding: 15px !important;
        }

        .detail-modal-content {
            max-height: 400px;
            overflow-y: auto;
        }

        /* Responsive untuk desktop */
        @media (min-width: 768px) {
            .swal2-popup.detail-modal-compact {
                width: 600px !important;
                max-width: 90vw !important;
            }
        }

        /* Responsive untuk tablet */
        @media (max-width: 767px) and (min-width: 576px) {
            .swal2-popup.detail-modal-compact {
                width: 95vw !important;
                max-width: 500px !important;
            }

            .detail-modal-content .col-md-8,
            .detail-modal-content .col-md-4 {
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
        }

        /* Responsive untuk mobile */
        @media (max-width: 575px) {
            .swal2-popup.detail-modal-compact {
                width: 95vw !important;
                margin: 10px !important;
            }

            .detail-modal-content {
                font-size: 12px !important;
            }

            .detail-modal-content .col-md-8,
            .detail-modal-content .col-md-4 {
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }

            .detail-modal-content img {
                width: 100px !important;
                height: 100px !important;
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
                        <li class="breadcrumb-item active">Collect Data</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Tombol tambah --}}
    <div class="row mb-3">
        <div class="col-auto">
            <a href="{{ route('admin.colect_data.create') }}" class="btn btn-success">
                <i class="fa fa-plus me-1"></i> Tambah Collect Data
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                @if (isRole('Siswa'))
                    Data Survey Saya
                @elseif (isRole('Pembimbing'))
                    Data Survey Siswa
                @else
                    Data Survey
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col" width="50">No</th>
                            <th scope="col">Collector</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Nama Customer</th>
                            <th scope="col">No. Telepon</th>
                            <th scope="col" width="220">Alamat</th>
                            <th scope="col" width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data akan diisi oleh DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Hidden form for delete action -->
    <form id="form-destroy" action="{{ route('admin.colect_data.store') }}" method="post" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('js')
    <script>
        // Function untuk menampilkan modal foto
        function showPhotoModal(fotoUrl, customerName, surveyDate, filename) {
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
                // Format tanggal
                const date = new Date(surveyDate);
                const formattedDate = date.toLocaleDateString('id-ID');

                const photoInfo = `
                    <div class="photo-info">
                        <strong>Customer:</strong> ${customerName}<br>
                        <strong>Tanggal Survey:</strong> ${formattedDate}<br>
                    </div>
                `;

                Swal.fire({
                    title: 'Foto Dokumentasi',
                    html: `
                        <div class="photo-container">
                            <img src="${fotoUrl}" 
                                 alt="Foto Survey ${customerName}" 
                                 class="photo-main" 
                                 style="max-width: 100%; max-height: 70vh; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);" />
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
                            window.open(fotoUrl, '_blank');
                        });
                    }
                });
            };

            img.onerror = function() {
                Swal.fire({
                    title: 'Error Loading Photo',
                    html: `
                        <div style="text-align: left;">
                            <p><strong>Gagal memuat foto.</strong></p>
                            <hr>
                            <p><strong>Info Debug:</strong></p>
                            <p><small><strong>File:</strong> ${filename}</small></p>
                            <p><small><strong>URL:</strong> ${fotoUrl}</small></p>
                            <hr>
                            <p><small><strong>Pastikan:</strong></p>
                            <p><small>1. File ada di: public/uploads/colect_data_gambar_foto/</small></p>
                            <p><small>2. Permission folder 755, file 644</small></p>
                            <p><small>3. Cek apakah file benar-benar ada</small></p>
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    width: 600
                });
            };

            // Start loading the image
            img.src = fotoUrl;
        }

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
                    url: "{{ route('admin.colect_data.fetch') }}",
                    type: "POST",
                    headers: {
                        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN')
                    },
                    dataSrc: "data"
                },
                order: [
                    [2, 'desc']
                ], // Order by tanggal descending
                columns: [{
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
                            return `<div class="address-cell" title="${data}">${data}</div>`;
                        }
                    },
                    {
                        data: 'id',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            let buttons = `<div class="row-action">`;

                            // Semua role bisa melihat detail
                            buttons += `
                                <button type="button" class="btn btn-info btn-action action-detail" 
                                        data-id="${data}" title="Detail">
                                    <i class="fa fa-info-circle"></i>
                                </button>
                            `;

                            // PERMISSION LOGIC BERDASARKAN ROLE:

                            @if (isRole('Admin'))
                                // ADMIN: Bisa edit dan hapus semua data
                                buttons += `
                                    <button type="button" class="btn btn-warning btn-action action-edit" 
                                            data-id="${data}" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-action action-delete" 
                                            data-id="${data}" title="Hapus">
                                        <i class="fa fa-trash-alt"></i>
                                    </button>
                                `;
                            @elseif (isRole('Siswa'))
                                // SISWA: Hanya bisa edit dan hapus data milik sendiri (TIDAK ADA TOMBOL HAPUS)
                                if (row.user_id == {{ auth()->id() }}) {
                                    buttons += `
                                        <button type="button" class="btn btn-warning btn-action action-edit" 
                                                data-id="${data}" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    `;
                                }
                            @elseif (isRole('Pembimbing'))
                                // PEMBIMBING: Hanya bisa melihat detail (tidak ada edit/hapus)
                                // Tidak menambahkan tombol edit atau hapus
                            @endif

                            buttons += `</div>`;
                            return buttons;
                        }
                    }
                ],
                createdRow: function(row, data, dataIndex) {
                    // Edit button handler
                    $(row).find('.action-edit').on('click', function() {
                        const id = $(this).data('id');
                        const url = "{{ url('/admin/colect-data') }}" + '/' + id + '/edit';
                        window.location.href = url;
                    });

                    // Delete button handler (hanya untuk Admin)
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

                    // Handler untuk detail button
                    $(row).find('.action-detail').on('click', function() {
                        // Ambil data dari row
                        const provider = data.provider_sekarang || '-';
                        const kelebihan = data.kelebihan || '-';
                        const kekurangan = data.kekurangan || '-';
                        const serlok = data.serlok || '-';
                        const foto = data.gambar_foto ? window.location.origin +
                            '/uploads/colect_data_gambar_foto/' + data.gambar_foto : null;

                        let fotoHtml = foto ?
                            `<img src="${foto}" alt="Foto" style="width:150px;height:150px;object-fit:cover;border-radius:8px;border:1px solid #ccc;cursor:pointer;" onclick="showPhotoModal('${foto}', '${data.nama_cus}', '${data.tanggal}', '${data.gambar_foto}')" title="Klik untuk melihat foto lebih besar" />` :
                            '<div style="width:150px;height:150px;border:1px solid #ccc;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#f8f9fa;"><span class="badge bg-secondary">Tidak ada foto</span></div>';

                        let serlokHtml = serlok.startsWith('http') ?
                            `<a href="${serlok}" target="_blank" class="badge bg-primary">Lihat Lokasi</a>` :
                            serlok;

                        Swal.fire({
                            title: 'Detail Collect Data',
                            html: `
                                <div style="display: flex; gap: 20px; text-align: left;">
                                    <div style="flex: 1;">
                                        <h6 style="margin-bottom: 15px; color: #495057; border-bottom: 1px solid #dee2e6; padding-bottom: 5px;">
                                            <i class="fa fa-info-circle me-2"></i>Informasi Detail
                                        </h6>
                                        <div style="margin-bottom: 12px;">
                                            <strong style="color: #6c757d;">Provider Saat Ini:</strong><br>
                                            <span style="background: #e3f2fd; padding: 2px 6px; border-radius: 4px; font-size: 0.9em;">${provider}</span>
                                        </div>
                                        <div style="margin-bottom: 12px;">
                                            <strong style="color: #6c757d;">Kelebihan:</strong><br>
                                            <div style="background: #f1f8e9; padding: 8px; border-radius: 4px; border-left: 3px solid #007bff; font-size: 0.9em;">${kelebihan}</div>
                                        </div>
                                        <div style="margin-bottom: 12px;">
                                            <strong style="color: #6c757d;">Kekurangan:</strong><br>
                                            <div style="background: #fff3e0; padding: 8px; border-radius: 4px; border-left: 3px solid #007bff; font-size: 0.9em;">${kekurangan}</div>
                                        </div>
                                        <div style="margin-bottom: 12px;">
                                            <strong style="color: #6c757d;">Share Lokasi:</strong><br>
                                            ${serlokHtml}
                                        </div>
                                    </div>
                                    <div style="flex: 0 0 auto;">
                                        <h6 style="margin-bottom: 15px; color: #495057; border-bottom: 1px solid #dee2e6; padding-bottom: 5px; text-align: center;">
                                            <i class="fa fa-camera me-2"></i>Foto Dokumentasi
                                        </h6>
                                        <div style="text-align: center;">
                                            ${fotoHtml}
                                        </div>
                                    </div>
                                </div>
                            `,
                            width: 400,
                            showCloseButton: true,
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#007bff'
                        });
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
