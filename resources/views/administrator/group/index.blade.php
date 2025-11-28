@extends('layout.main')
@section('css')
    <style>

    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Group</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Group</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        @if (auth()->user()->group_id == 1)
            <div class="col-auto">
                <a href="{{ route('admin.group.create') }}" class="btn btn-success">
                    <i class="fa fa-plus me-1"></i> Tambah
                </a>
            </div>
        @endif
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Created At</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
    <div class="d-none">
        <form id="form-destroy" action="{{ route('admin.group.store') }}" method="post">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection
@section('js')
    <script>
        $(document).ready(function() {
            // Setup AJAX untuk otomatis mengirim CSRF token di setiap request
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Inisialisasi DataTables
            var dataTable = $('table').DataTable({
                fixedHeader: true,
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                ajax: {
                    // Gunakan route() helper Laravel yang lebih aman
                    url: "{{ route('admin.group.fetch') }}",
                    type: "POST"
                    // dataSrc tidak perlu karena default-nya sudah "data"
                },
                order: [
                    [2, 'desc'] // Mengurutkan berdasarkan kolom 'created_at'
                ],
                columns: [{
                        data: 'DT_RowIndex',
                        sClass: 'text-center',
                        width: '50px',
                        searchable: false,
                        orderable: false,
                    },
                    {
                        data: 'nama',
                        name: 'nama', // Tambahkan 'name' untuk server-side searching/ordering
                        searchable: true,
                        orderable: true,
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        visible: true, // Ubah menjadi true agar bisa di-sort
                        render: function(data) {
                            if (!data) return "-";
                            // Format tanggal yang lebih rapi
                            const date = new Date(data);
                            return date.toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'long',
                                year: 'numeric'
                            });
                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        render: function(data, type, row) {
                            var editUrl = "{{ route('admin.group.edit', ':id') }}".replace(':id',
                                data);
                            var div = document.createElement("div");
                            div.className = "row-action";

                            // Tombol Edit
                            var btnEdit = document.createElement("a");
                            btnEdit.href = editUrl;
                            btnEdit.className = "btn btn-warning btn-action mx-1 action-edit";
                            btnEdit.innerHTML = '<i class="icon fa fa-edit"></i>';
                            div.append(btnEdit);

                            // Tombol Delete
                            var btnDelete = document.createElement("button");
                            btnDelete.className = "btn btn-danger btn-action mx-1 action-hapus";
                            btnDelete.innerHTML = '<i class="icon fa fa-trash-alt"></i>';
                            div.append(btnDelete);

                            return div.outerHTML;
                        },
                        width: "120px",
                        orderable: false,
                        searchable: false,
                        sClass: 'text-center'
                    },
                ],
                createdRow: function(row, data) {
                    // Hapus listener 'action-edit' karena kita sudah menggunakan <a> tag

                    // Listener untuk tombol hapus
                    $(row).on('click', '.action-hapus', function(e) {
                        e.preventDefault();
                        Swal.fire({
                            icon: "warning",
                            title: "Konfirmasi Hapus",
                            text: `Anda yakin akan menghapus grup "${data.nama}"?`,
                            showCancelButton: true,
                            confirmButtonText: "Ya, Hapus!",
                            cancelButtonText: "Batal",
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Arahkan form ke URL yang benar untuk destroy
                                const destroyUrl =
                                    "{{ route('admin.group.destroy', ':id') }}".replace(
                                        ':id', data.id);
                                $('#form-destroy').attr('action', destroyUrl).trigger(
                                    'submit');
                            }
                        });
                    });
                },
            });

            // SweetAlert untuk notifikasi session
            @if (session()->has('dataSaved') && session()->get('dataSaved') == true)
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session()->get('message') }}',
                });
            @endif
            @if (session()->has('dataSaved') && session()->get('dataSaved') == false)
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{{ session()->get('message') }}',
                });
            @endif
        });
    </script>
@endsection
