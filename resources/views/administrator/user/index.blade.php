@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">User</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">User</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-auto">
            <a href="{{ route('admin.user.create') }}" class="btn btn-success">
                <i class="fa fa-plus me-1"></i> Tambah
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            {{-- Tambahkan id pada tabel agar lebih mudah diseleksi --}}
            <table id="user-table" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Username</th>
                        <th scope="col">Email</th>
                        <th scope="col">Validasi</th>
                        <th scope="col">Sekolah</th>
                        <th scope="col">Group</th>
                        <th scope="col">ID PKL</th>
                        <th scope="col">Alamat</th>
                        <th scope="col">Created At</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="d-none">
        {{-- Form untuk hapus tetap digunakan sesuai struktur asli --}}
        <form id="form-destroy" action="{{ route('admin.user.destroy', '') }}" method="post">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            $('#user-table').DataTable({
                fixedHeader: true,
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    // 1. URL diperbaiki menggunakan route helper Laravel
                    url: "{{ route('admin.user.fetch') }}",
                    type: "POST",
                    // Mengambil CSRF token dari meta tag (lebih standar dari getCookie)
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                },
                // 2. Order diperbaiki, mengurutkan berdasarkan 'created_at' (kolom ke-9)
                order: [
                    [9, 'desc']
                ],
                columns: [{
                        data: 'DT_RowIndex',
                        sClass: 'text-center',
                        searchable: false,
                        orderable: false
                    },
                    // 3. Menambahkan properti 'name' agar server-side search/sort berfungsi
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'username',
                        name: 'username'
                    },
                    {
                        data: 'email',
                        name: 'email',
                        visible: false
                    },
                    {
                        data: 'validasi',
                        name: 'validasi',
                        render: function(data) {
                            const isValid = data == 1;
                            const text = isValid ? "Validasi" : "Belum Validasi";
                            const color = isValid ? "success" : "danger";
                            // Tampilan dibuat lebih rapi dengan badge
                            return `<span class="badge bg-${color}">${text}</span>`;
                        }
                    },
                    {
                        data: 'sekolah.nama',
                        name: 'sekolah.nama',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'group.nama',
                        name: 'group.nama',
                        orderable: false
                    },
                    {
                        data: 'id_pkl',
                        name: 'id_pkl'
                    },
                    {
                        data: 'alamat',
                        name: 'alamat',
                        visible: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        visible: false
                    },
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        width: "100px",
                        render: function(data, type, row) {
                            // 4. Render tombol dibuat lebih ringkas dengan template literals
                            return `
                                <div class="d-flex justify-content-center">
                                    <button type="button" class="btn btn-warning btn-sm mx-1 action-edit">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm mx-1 action-hapus">
                                        <i class="fa fa-trash-alt"></i>
                                    </button>
                                </div>
                            `;
                        }
                    },
                ],
                // 5. Struktur `createdRow` tetap dipertahankan
                createdRow: function(row, data) {
                    // Event untuk tombol Edit
                    $(".action-edit", row).click(function() {
                        const editUrl = "{{ route('admin.user.edit', ':id') }}".replace(':id',
                            data.id);
                        window.location.href = editUrl;
                    });

                    // Event untuk tombol Hapus (menggunakan form-destroy)
                    $(".action-hapus", row).click(function(e) {
                        e.preventDefault();
                        Swal.fire({
                            icon: "warning",
                            title: "Anda Yakin?",
                            text: "Data yang akan dihapus tidak bisa dikembalikan.",
                            showCancelButton: true,
                            confirmButtonText: "Ya, Hapus!",
                            cancelButtonText: "Batal",
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const baseUrl = "{{ route('admin.user.destroy', '') }}";
                                $('#form-destroy').attr('action', baseUrl + '/' + data
                                    .id);
                                $('#form-destroy').trigger('submit');
                            }
                        });
                    });
                },
            });
        });
    </script>

    {{-- 6. Notifikasi session dibuat lebih ringkas --}}
    @if (session()->has('message'))
        <script>
            Swal.fire({
                icon: '{{ session('dataSaved') ? 'success' : 'error' }}',
                title: '{{ session('dataSaved') ? 'Berhasil' : 'Gagal' }}',
                text: '{{ session('message') }}',
            });
        </script>
    @endif
@endsection
