@extends('layout.main')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        .filter-card {
            margin-bottom: 1.5rem;
        }

        .address-cell {
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* CSS untuk modal detail yang lebih rapi */
        .swal-clean-popup {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            border-radius: 12px !important;
        }

        .swal-clean-content {
            margin: 0 !important;
            padding: 0 !important;
        }

        .swal2-title {
            padding: 0 !important;
            margin: 0 0 15px 0 !important;
        }

        #dataTable thead th.no-sort::before,
        #dataTable thead th.no-sort::after {
            display: none !important;
        }

        .flatpickr-calendar.monthSelect {
            max-width: 260px !important;
        }
    </style>
@endsection

@section('content')
    @if (in_array(auth()->user()->group_id, [1, 2, 4, 3, 5, 6, 7]))
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Collect Data</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Collect Data</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-auto d-flex gap-2">
                @if (auth()->user()->group_id == 4)
                    <a href="{{ route('admin.colect_data.create') }}" class="btn btn-success">
                        <i class="fa fa-plus me-1"></i> Tambah Collect Data
                    </a>
                @endif
                @if (auth()->user()->group_id == 2)
                    <button type="button" id="export-btn" class="btn btn-primary">
                        <i class="fa fa-file-excel me-1"></i> Export Excel
                    </button>
                @endif
            </div>
        </div>

        {{-- BARU: Modifikasi Kartu Filter untuk Collapse --}}
        <div class="card mb-4">
            {{-- BARU: Card Header sebagai Tombol Toggle --}}
            <div class="card-header" id="filterCardHeader"
                style="cursor: pointer; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <h5 class="card-title mb-0">
                    <a href="#" class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse"
                        style="text-decoration: none; color: inherit;">
                        <span>
                            <i class="fa fa-filter me-2"></i>
                            Opsi Filter
                        </span>
                        {{-- Icon diatur tertutup (chevron-up) by default --}}
                        <i class="fa fa-chevron-up toggle-icon"></i>
                    </a>
                </h5>
            </div>

            {{-- BARU: Wrapper untuk Collapse (tanpa 'show' agar tertutup by default) --}}
            <div class="collapse" id="filterCollapse">
                <div class="card-body">
                    {{-- Judul <h5 class="card-title">Filter Data</h5> dihapus karena sudah ada di header --}}
                    <div class="row align-items-end">
                        {{-- Filter Bulan/Tahun --}}
                        <div class="col-md-3">
                            <label for="filter_bulan" class="form-label">Filter Bulan/Tahun:</label>
                            <input type="month" id="filter_bulan" class="form-control form-control-sm">
                        </div>

                        {{-- Filter Cari Nama Siswa --}}
                        @if (auth()->user()->group_id != 4)
                            <div class="col-md-3">
                                <label for="filter_nama_siswa" class="form-label">Cari Nama Siswa:</label>
                                <input type="text" id="filter_nama_siswa" class="form-control form-control-sm"
                                    placeholder="Ketik nama siswa...">
                            </div>
                        @endif
                    </div>
                </div>
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
                                <th scope="col" width="50">No</th>
                                <th scope="col">Collector</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Nama Customer</th>
                                <th scope="col">No. Telepon</th>
                                <th scope="col" width="220">Alamat</th>
                                <th scope="col" width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <form id="form-destroy" action="" method="post" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endsection

    @section('js')
        {{-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script> --}}

        <script>
            function showPhotoModal(fotoUrl, customerName) {
                Swal.fire({
                    title: `Foto untuk ${customerName}`,
                    imageUrl: fotoUrl,
                    imageWidth: 600,
                    imageAlt: 'Foto Survey',
                    confirmButtonText: 'Tutup'
                });
            }

            $(document).ready(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOut'
                    },
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });

                {{-- BARU: Logika JavaScript untuk mengubah icon chevron --}}
                var filterCollapse = document.getElementById('filterCollapse');
                var toggleIcon = document.querySelector('[data-bs-target="#filterCollapse"] .toggle-icon');

                if (filterCollapse && toggleIcon) {
                    // Event saat collapse mulai ditampilkan
                    filterCollapse.addEventListener('show.bs.collapse', function() {
                        toggleIcon.classList.remove('fa-chevron-up');
                        toggleIcon.classList.add('fa-chevron-down');
                    });

                    // Event saat collapse mulai disembunyikan
                    filterCollapse.addEventListener('hide.bs.collapse', function() {
                        toggleIcon.classList.remove('fa-chevron-down');
                        toggleIcon.classList.add('fa-chevron-up');
                    });
                }
                {{-- AKHIR BARU --}}


                //     const monthPicker = flatpickr("#filter_bulan", {
                //     // Opsi untuk menampilkan input alternatif yang lebih ramah pengguna
                //     altInput: true,
                //     altFormat: "F Y", // Format yang dilihat pengguna, contoh: "September 2025"
                //     dateFormat: "Y-m", // Format yang dikirim ke server, contoh: "2025-09"
                //     locale: "id",      // Menggunakan bahasa Indonesia untuk nama bulan

                //     // Plugin utama untuk memilih bulan
                //     plugins: [
                //         new monthSelectPlugin({
                //             shorthand: true, // Menampilkan nama bulan singkatan (Jan, Feb, etc.)
                //             dateFormat: "Y-m",
                //             altFormat: "F Y"
                //         })
                //     ],

                //     // Fungsi yang dijalankan saat kalender siap untuk menambahkan tombol kustom
                //     onReady: function(selectedDates, dateStr, instance) {
                //         // Membuat wadah untuk tombol
                //         const buttonsContainer = document.createElement('div');
                //         buttonsContainer.style.cssText = "display: flex; justify-content: space-between; padding: 0 12px 12px;";

                //         // Membuat tombol "Clear"
                //         const clearBtn = document.createElement('button');
                //         clearBtn.type = 'button';
                //         clearBtn.className = 'btn btn-sm btn-light'; // Class untuk styling
                //         clearBtn.textContent = 'Clear';
                //         clearBtn.addEventListener('click', (e) => {
                //             instance.clear(); // Fungsi untuk membersihkan input
                //             instance.close(); // Menutup kalender
                //             e.stopPropagation();
                //         });

                //         // Membuat tombol "This month"
                //         const thisMonthBtn = document.createElement('button');
                //         thisMonthBtn.type = 'button';
                //         thisMonthBtn.className = 'btn btn-sm btn-primary'; // Class untuk styling
                //         thisMonthBtn.textContent = 'This month';
                //         thisMonthBtn.addEventListener('click', (e) => {
                //             instance.setDate(new Date(), true); // Fungsi untuk set ke bulan ini
                //             instance.close(); // Menutup kalender
                //             e.stopPropagation();
                //         });

                //         // Menambahkan tombol ke dalam wadah
                //         buttonsContainer.appendChild(clearBtn);
                //         buttonsContainer.appendChild(thisMonthBtn);

                //         // Menambahkan wadah tombol ke dalam pop-up kalender
                //         instance.calendarContainer.appendChild(buttonsContainer);
                //     }
                // });
                function setDefaultMonth() {
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = (now.getMonth() + 1).toString().padStart(2, '0');
                    $('#filter_bulan').val(`${year}-${month}`);
                }
                setDefaultMonth();
                var dataTable = $('#dataTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('admin.colect_data.fetch') }}",
                        type: "POST",
                        data: function(d) {
                            d.filter_bulan = $('#filter_bulan').val();
                            d.filter_nama_siswa = $('#filter_nama_siswa').val();
                            return d;
                        }
                    },
                    order: [
                        [2, 'desc']
                    ],
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
                            className: 'text-center no-sort'
                        },
                        {
                            data: 'user.name',
                            name: 'user.name',
                            orderable: false,
                            className: 'no-sort'
                        },
                        {
                            data: 'tanggal',
                            name: 'tanggal',
                            orderable: false,
                            className: 'no-sort'
                        },
                        {
                            data: 'nama_cus',
                            name: 'nama_cus',
                            orderable: false,
                            className: 'no-sort'
                        },
                        {
                            data: 'no_telp',
                            name: 'no_telp',
                            orderable: false,
                            className: 'no-sort'
                        },
                        {
                            data: 'alamat_cus',
                            name: 'alamat_cus',
                            orderable: false,
                            className: 'no-sort',
                            render: (data) => `<div class="address-cell" title="${data}">${data}</div>`
                        },
                        {
                            data: 'aksi',
                            name: 'aksi',
                            orderable: false,
                            searchable: false,
                            className: 'text-center no-sort'
                        }
                    ],
                    createdRow: function(row, data) {
                        // [MODIFIKASI] Logika hapus sekarang menggunakan AJAX dan Toast
                        $(row).on('click', '.action-hapus', function() {
                            const url = $(this).data('url');
                            Swal.fire({
                                title: 'Konfirmasi Hapus',
                                text: "Apakah Anda yakin ingin menghapus data ini?",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Ya, Hapus!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: url,
                                        type: 'POST',
                                        data: {
                                            '_token': '{{ csrf_token() }}',
                                            '_method': 'DELETE'
                                        },
                                        success: function(response) {
                                            Toast.fire({
                                                icon: 'success',
                                                title: response.success
                                            });
                                            dataTable.ajax.reload();
                                        },
                                        error: function(xhr) {
                                            Toast.fire({
                                                icon: 'error',
                                                title: 'Gagal menghapus data.'
                                            });
                                        }
                                    });
                                }
                            });
                        });

                        // Tombol Detail - STYLING YANG SUDAH DIPERBAIKI
                        $(row).on('click', '.action-detail', function() {
                            const provider = data.provider_sekarang || '-';
                            const kelebihan = data.kelebihan || '-';
                            const kekurangan = data.kekurangan || '-';
                            const serlok = data.serlok || '-';
                            const fotoUrl = data.gambar_foto ?
                                `{{ asset('uploads/colect_data_gambar_foto') }}/${data.gambar_foto}` :
                                null;

                            let fotoHtml = fotoUrl ?
                                `<img src="${fotoUrl}" alt="Foto Dokumentasi" style="width: 100%; height: 250px; border-radius: 8px; cursor:pointer; object-fit: cover; border: 1px solid #dee2e6;" onclick="showPhotoModal('${fotoUrl}', '${data.nama_cus}')" />` :
                                '<div style="display: flex; align-items: center; justify-content: center; height: 250px; background-color: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px; color: #6c757d; font-size: 14px;"><span><i class="fa fa-image me-2"></i>Tidak ada foto</span></div>';

                            let serlokHtml = serlok.startsWith('http') ?
                                `<button onclick="window.open('${serlok}', '_blank')" style="background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; display: inline-flex; align-items: center; text-decoration: none;"><i class="fa fa-map-marker-alt me-2"></i>Lihat Lokasi</button>` :
                                `<span style="color: #6c757d; font-style: italic;">${serlok}</span>`;

                            Swal.fire({
                                title: '<div style="color: #333; font-size: 18px; font-weight: 600; margin-bottom: 10px;">Detail Collect Data</div>',
                                html: `
                                <div style="display: flex; gap: 25px; text-align: left; min-height: 320px; padding: 10px;">
                                    <div style="flex: 1; min-width: 0;">
                                        <h6 style="color: #495057; font-size: 14px; font-weight: 600; margin-bottom: 15px; border-bottom: 2px solid #e9ecef; padding-bottom: 8px;">
                                            <i class="fa fa-info-circle me-2" style="color: #007bff;"></i>Informasi Detail
                                        </h6>
                                        
                                        <div style="margin-bottom: 18px;">
                                            <div style="font-weight: 600; color: #495057; margin-bottom: 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Provider Saat Ini:</div>
                                            <div style="background: linear-gradient(135deg, #f8f9ff 0%, #e3f2fd 100%); padding: 12px 15px; border-radius: 8px; border-left: 4px solid #007bff; font-size: 14px; color: #495057; box-shadow: 0 2px 4px rgba(0,123,255,0.1);">
                                                ${provider}
                                            </div>
                                        </div>
                                        
                                        <div style="margin-bottom: 18px;">
                                            <div style="font-weight: 600; color: #495057; margin-bottom: 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Kelebihan:</div>
                                            <div style="background: linear-gradient(135deg, #f0fff4 0%, #e8f5e8 100%); padding: 12px 15px; border-radius: 8px; border-left: 4px solid #28a745; font-size: 14px; color: #495057; line-height: 1.5; box-shadow: 0 2px 4px rgba(40,167,69,0.1);">
                                                ${kelebihan}
                                            </div>
                                        </div>
                                        
                                        <div style="margin-bottom: 18px;">
                                            <div style="font-weight: 600; color: #495057; margin-bottom: 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Kekurangan:</div>
                                            <div style="background: linear-gradient(135deg, #fffbf0 0%, #fff3cd 100%); padding: 12px 15px; border-radius: 8px; border-left: 4px solid #ffc107; font-size: 14px; color: #495057; line-height: 1.5; box-shadow: 0 2px 4px rgba(255,193,7,0.1);">
                                                ${kekurangan}
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <div style="font-weight: 600; color: #495057; margin-bottom: 8px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Share Lokasi:</div>
                                            <div style="padding-top: 4px;">
                                                ${serlokHtml}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div style="flex: 1; min-width: 0;">
                                        <h6 style="color: #495057; font-size: 14px; font-weight: 600; margin-bottom: 15px; border-bottom: 2px solid #e9ecef; padding-bottom: 8px;">
                                            <i class="fa fa-camera me-2" style="color: #28a745;"></i>Foto Dokumentasi
                                        </h6>
                                        <div style="background: #ffffff; border: 1px solid #dee2e6; border-radius: 10px; padding: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                                            ${fotoHtml}
                                        </div>
                                    </div>
                                </div>
                            `,
                                width: '750px',
                                showCloseButton: true,
                                showConfirmButton: true,
                                confirmButtonText: '<i class="fa fa-times me-1"></i>Tutup',
                                confirmButtonColor: '#6c757d',
                                customClass: {
                                    popup: 'swal-clean-popup',
                                    htmlContainer: 'swal-clean-content'
                                },
                                didOpen: () => {
                                    // Add custom styles when modal opens
                                    const style = document.createElement('style');
                                    style.textContent = `
                                    .swal-clean-popup {
                                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                                        border-radius: 12px !important;
                                    }
                                    .swal-clean-content {
                                        margin: 0 !important;
                                        padding: 0 !important;
                                    }
                                    .swal2-title {
                                        padding: 0 !important;
                                        margin: 0 0 15px 0 !important;
                                    }
                                `;
                                    document.head.appendChild(style);
                                }
                            });
                        });
                    }
                });

                $('#export-btn').on('click', function() {
                    let bulan = $('#filter_bulan').val();
                    let namaSiswa = $('#filter_nama_siswa').val();
                    let baseUrl = "{{ route('admin.colect_data.export') }}";
                    let queryParams = [];
                    if (bulan) queryParams.push('filter_bulan=' + bulan);
                    if (namaSiswa) queryParams.push('filter_nama_siswa=' + encodeURIComponent(
                        namaSiswa));
                    let finalUrl = baseUrl + (queryParams.length ? '?' + queryParams.join('&') : '');
                    window.location.href = finalUrl;
                });

                // Filter bulan langsung reload
                $('#filter_bulan').on('change', function() {
                    $('#dataTable').DataTable().ajax.reload();
                });

                // Debounce untuk filter nama siswa
                let namaSiswaTimeout;
                $('#filter_nama_siswa').on('keyup', function() {
                    clearTimeout(namaSiswaTimeout);
                    namaSiswaTimeout = setTimeout(function() {
                        $('#dataTable').DataTable().ajax.reload();
                    }, 700);
                });
                @if (session('success'))
                    Toast.fire({
                        icon: 'success',
                        title: '{{ session('success') }}'
                    });
                @elseif (session('error'))
                    Toast.fire({
                        icon: 'error',
                        title: '{{ session('error') }}'
                    });
                @endif
            });
        </script>
    @endif
@endsection
