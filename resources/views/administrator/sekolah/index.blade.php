@extends('layout.main')
@section('css')
    <style>
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Sekolah</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Sekolah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-auto">
            <a href="{{ route('admin.sekolah.create') }}" class="btn btn-success">
                <i class="fa fa-plus me-1"></i> Tambah
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table id="sekolah-table" class="table table-striped table-bordered nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nama Sekolah</th>
                        <th scope="col">Logo Sekolah</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-none">
        <form id="form-destroy" action="" method="post">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection
@section('js')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#sekolah-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.sekolah.fetch') }}",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    // PERBAIKAN: Hapus fungsi data yang duplikat CSRF token
                    error: function(xhr, error, code) {
                        console.log('AJAX Error Details:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            error: error,
                            code: code
                        });

                        let errorMessage = 'Gagal memuat data sekolah.';
                        if (xhr.status === 419) {
                            errorMessage = 'Session expired. Silakan refresh halaman dan coba lagi.';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error ' + xhr.status,
                            text: errorMessage,
                            footer: 'Periksa console untuk detail error.'
                        });
                    }
                },
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '50px'
                    },
                    {
                        data: 'nama',
                        name: 'nama',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'logo',
                        name: 'logo',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if (data) {
                                return `<img src="/uploads/sekolah_logo/${data}" alt="Logo Sekolah" style="max-width:40px;">`;
                            }
                            return '<span class="badge bg-secondary">Tidak ada</span>';
                        }
                    },
                    {
                        data: 'id',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '150px',
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-warning btn-sm btn-edit" 
                                            data-id="${data}" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm btn-delete" 
                                            data-id="${data}" title="Hapus">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [
                    [1, 'asc']
                ],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                language: {
                    processing: "Memproses...",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    zeroRecords: "Data tidak ditemukan",
                    emptyTable: "Tidak ada data yang tersedia",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    search: "Cari:",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            // Edit button click handler
            $('#sekolah-table').on('click', '.btn-edit', function() {
                var id = $(this).data('id');
                var editUrl = `/admin/sekolah/${id}/edit`;
                window.location.href = editUrl;
                window.location.href = editUrl;
            });

            // Delete button click handler
            $('#sekolah-table').on('click', '.btn-delete', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: 'Apakah Anda yakin ingin menghapus data ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var deleteUrl = `/admin/sekolah/${id}`;
                        $('#form-destroy').attr('action', deleteUrl).submit();
                        $('#form-destroy').attr('action', deleteUrl);
                        $('#form-destroy').submit();
                    }
                });
            });
        });
    </script>

    <!-- Success Message -->
    @if (session('dataSaved') && session('dataSaved') == true)
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('message') }}',
                timer: 3000,
                showConfirmButton: false
            });
        </script>
    @endif

    <!-- Error Message -->
    @if (session('dataSaved') && session('dataSaved') == false)
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('message') }}',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
@endsection
