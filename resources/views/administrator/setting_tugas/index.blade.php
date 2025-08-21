@extends('layout.main')

@section('css')
    {{-- Dependency CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        /* CSS Tambahan untuk menyesuaikan gaya tombol */
        .row-action {
            display: flex;
            gap: 0.25rem;
            justify-content: center;
        }
        .btn-action {
            /* Gaya yang mungkin dibutuhkan jika tidak ada di app.min.css */
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
@endsection

@section('content')
    @if(isRole('Admin'))
    <h4 class="fw-bold mb-4">Setting Anggota Tim</h4>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="section-header mb-3">
                <div class="section-title"> DAFTAR TIM </div>
                @if(isRole('Admin'))
                <a href="{{ route('admin.setting_tugas.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> Tambah Tim
                </a>
                @endif
            </div>

            {{-- === FORM FILTER BARU DENGAN FILTER BULAN === --}}
            <form id="filter-form" class="border-top pt-3 mb-3">
                <div class="filter-container">
                    <div class="filter-item">
                        <label for="filter-ketua" class="form-label">Ketua Tim:</label>
                        <select id="filter-ketua" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach($karyawan as $ketua)
                                <option value="{{ $ketua->id }}">{{ $ketua->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="filter-divisi" class="form-label">Jenis Tim:</label>
                        <select id="filter-divisi" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="sales">SALES</option>
                            <option value="teknisi">TEKNISI</option>
                        </select>
                    </div>
                    {{-- Filter Bulan/Tahun yang baru ditambahkan --}}
                    <div class="filter-item">
                        <label for="filter-bulan" class="form-label">Bulan/Tahun:</label>
                        <input type="month" id="filter-bulan" class="form-control form-control-sm">
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped" id="teams-table" style="width:100%">
                    <thead class="table-bordered">
                        <tr>
                            <th>Ketua Tim</th>
                            <th>Jenis Tim</th>
                            <th>Anggota Tim</th>
                            <th>Dibuat Pada</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
    {{-- Dependency JS --}}
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // --- FUNGSI BARU: Set nilai default untuk filter bulan ---
            function setDefaultMonth() {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                $('#filter-bulan').val(`${year}-${month}`);
            }
            setDefaultMonth(); // Panggil fungsi saat halaman dimuat

            const teamsDataTable = $('#teams-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("admin.setting_tugas.data") }}',
                    data: function (d) {
                        d.ketua_id = $('#filter-ketua').val();
                        d.divisi = $('#filter-divisi').val();
                        d.bulan = $('#filter-bulan').val(); // Kirim data filter bulan ke controller
                    }
                },
                columns: [
                    { data: 'ketua_name', name: 'ketua_name' },
                    {
                        data: 'divisi',
                        name: 'divisi',
                        render: data => `<span class="badge ${data === 'sales' ? 'badge-sales' : 'badge-teknisi'} text-white px-2 py-1">${data.toUpperCase()}</span>`
                    },
                    {
                        data: 'anggota_names',
                        name: 'anggota_names',
                        orderable: false,
                        render: data => data && data.length > 0 ? `<div class="anggota-list" title="${data.join(', ')}">${data.join(', ')}</div>` : '<div class="text-muted">Tidak ada anggota</div>'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: data => `<small class="text-muted">${new Date(data).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })}</small>`
                    },
                    {
                        data: 'id',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return `
                                <div class="row-action">
                                    <button type="button" class="btn btn-warning btn-sm waves-effect waves-light w-xs action-edit" data-id="${data}" title="Edit">
                                        <i class="fas fa-edit d-block font-size-12"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm waves-effect waves-light w-xs action-hapus" data-id="${data}" title="Hapus">
                                        <i class="fas fa-trash-alt d-block font-size-12"></i> Hapus
                                    </button>
                                </div>
                            `;
                        },
                        width: "150px",
                    }
                ],
                order: [[3, 'desc']],
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                responsive: true,
                createdRow: function(row, data, dataIndex) {
                    // Handler untuk tombol Edit
                    $(row).find('.action-edit').on('click', function() {
                        const teamId = $(this).data('id');
                        window.location.href = `{{ url('admin/setting-tugas') }}/${teamId}/edit`;
                    });

                    // Handler untuk tombol Hapus
                    $(row).find('.action-hapus').on('click', function() {
                        const teamId = $(this).data('id');
                        Swal.fire({
                            title: 'Konfirmasi Hapus',
                            text: 'Apakah Anda yakin ingin menghapus tim ini?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: `{{ url('admin/setting-tugas') }}/${teamId}`,
                                    type: 'POST', // Menggunakan POST
                                    data: {
                                        _method: 'DELETE', // Method spoofing untuk DELETE
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function(response) {
                                        teamsDataTable.ajax.reload();
                                        Swal.fire('Berhasil!', response.message, 'success');
                                    },
                                    error: function(xhr) {
                                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
                                    }
                                });
                            }
                        });
                    });
                }
            });

            // Event handler untuk perubahan pada semua dropdown filter
            $('#filter-ketua, #filter-divisi, #filter-bulan').on('change', function() {
                teamsDataTable.ajax.reload();
            });

            // Notifikasi Session
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 3000
                });
            @endif
            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '{{ session('error') }}',
                    showConfirmButton: false,
                    timer: 3000
                });
            @endif
        });
    </script>
@endsection