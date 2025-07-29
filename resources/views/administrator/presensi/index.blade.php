@extends('layout.main')
@section('css')
    <style>
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Presensi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Presensi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        @if (canInputPresensi())
            <div class="col-auto">
                <a href="{{ route('presensi.create') }}" class="btn btn-success">
                    <i class="fa fa-plus me-1"></i> Tambah
                </a>
            </div>
        @endif
        @if (canValidatePresensi())
            <div class="col-auto">
                <button type="button" class="btn btn-warning" id="btn-check-automatic">
                    <i class="fa fa-clock me-1"></i> Cek Presensi Otomatis
                </button>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Jenis Presensi</th>
                        <th>Sesi</th>
                        <th>Jam Presensi</th>
                        <th>Tanggal</th>
                        <th>Status Verifikasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="d-none">
        <form id="form-destroy" action="{{ route('presensi.store') }}" method="post">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection

@section('js')
    <script>
        $('table').DataTable({
            fixedHeader: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: baseUrl('/presensi/fetch'),
                headers: {
                    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN')
                },
                dataSrc: "data",
                type: "POST"
            },
            order: [
                [5, 'desc']
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
                    searchable: true,
                    orderable: true,
                },
                {
                    data: 'presensi_jenis',
                    searchable: true,
                    orderable: true,
                },
                {
                    data: 'sesi',
                    searchable: true,
                    orderable: true,
                },
                {
                    data: 'jam_presensi',
                    searchable: true,
                    orderable: true,
                },
                {
                    data: 'tanggal_presensi',
                    searchable: true,
                    orderable: true,
                },
                {
                    data: 'status_verifikasi',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'id',
                    name: 'id',
                    render: function(data) {
                        var div = document.createElement("div");
                        div.className = "row-action";

                        var btn = document.createElement("button");
                        btn.className = "btn btn-warning btn-action mx-1 action-edit";
                        btn.innerHTML = '<i class="icon fa fa-edit"></i>';
                        div.append(btn);

                        var btn = document.createElement("button");
                        btn.className = "btn btn-danger btn-action mx-1 action-hapus";
                        btn.innerHTML = '<i class="icon fa fa-trash-alt"></i>';
                        div.append(btn);

                        return div.outerHTML;
                    },
                    width: "150px",
                    orderable: false,
                },
            ],
            createdRow: function(row, data) {
                $(".action-edit", row).click(function() {
                    const url = baseUrl('/presensi/' + data.id + '/edit');
                    window.location.replace(url);
                });

                $(".action-hapus", row).click(function(e) {
                    e.preventDefault();
                    Swal.fire({
                        icon: "warning",
                        title: "Warning",
                        text: "Anda yakin akan menghapus data ini ??",
                        showCancelButton: true,
                        confirmButtonText: "Hapus",
                        cancelButtonText: "Batal",
                    }).then((result) => {
                        if (result.value) {
                            const url = $('#form-destroy').attr('action');
                            $('#form-destroy').attr('action', url + '/' + data.id).trigger(
                                'submit');
                        }
                    });
                });
            },
        });

        // Handle automatic presensi check (only for admin/developer)
        $('#btn-check-automatic').click(function() {
            Swal.fire({
                icon: "warning",
                title: "⚠️ PERHATIAN - Fitur Admin",
                html: `
                    <div class="text-left">
                        <p><strong>Fitur ini akan:</strong></p>
                        <ul class="text-left">
                            <li>✅ Membuat presensi "bolos" untuk sesi yang kosong</li>
                            <li>✅ Mengubah presensi terlambat menjadi "telat"</li>
                            <li>⚠️ <strong>Mengubah data presensi yang sudah ada!</strong></li>
                        </ul>
                        <p class="text-danger mt-2"><strong>Apakah Anda yakin ingin melanjutkan?</strong></p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: "Ya, Jalankan",
                cancelButtonText: "Batal",
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
            }).then((result) => {
                if (result.value) {
                    // Show loading
                    Swal.fire({
                        title: 'Menjalankan Pengecekan...',
                        html: 'Mohon tunggu, sistem sedang memproses...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: baseUrl('/presensi/check-automatic'),
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    html: `
                                        <p>${response.message}</p>
                                        <p class="text-info">Data presensi telah diperbarui secara otomatis.</p>
                                    `,
                                }).then(() => {
                                    $('table').DataTable().ajax.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message,
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Terjadi kesalahan saat menjalankan pengecekan otomatis',
                            });
                        }
                    });
                }
            });
        });
    </script>

    @if (session()->has('dataSaved') && session()->get('dataSaved') == true)
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session()->get('message') }}',
            });
        </script>
    @endif

    @if (session()->has('dataSaved') && session()->get('dataSaved') == false)
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session()->get('message') }}',
            });
        </script>
    @endif
@endsection
