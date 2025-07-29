@extends('layout.main')
@section('css')
    <style></style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Jenis Presensi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Jenis Presensi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-auto">
            <a href="{{ route('presensi_jenis.create') }}" class="btn btn-success">
                <i class="fa fa-plus me-1"></i> Tambah
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Butuh Bukti</th>
                        <th>Otomatis</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="d-none">
        <form id="form-destroy" action="{{ route('presensi_jenis.store') }}" method="post">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection
@section('js')
    <script>
        $('table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: baseUrl('/presensi_jenis/fetch'),
                type: 'POST',
                headers: {
                    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN')
                },
                dataSrc: 'data'
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'nama'
                },
                {
                    data: 'butuh_bukti'
                },
                {
                    data: 'otomatis'
                },
                {
                    data: 'id',
                    render: function(data, type, row) {
                        return `
                            <div class="row-action">
                                <button class="btn btn-warning btn-action mx-1 action-edit"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-danger btn-action mx-1 action-hapus"><i class="fa fa-trash-alt"></i></button>
                            </div>`;
                    },
                    orderable: false
                }
            ],
            createdRow: function(row, data) {
                $('.action-edit', row).click(function() {
                    window.location.href = baseUrl(`/presensi_jenis/${data.id}/edit`);
                });
                $('.action-hapus', row).click(function(e) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Hapus?',
                        text: 'Data akan dihapus permanen!',
                        showCancelButton: true,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Tidak'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#form-destroy').attr('action', baseUrl(
                                `/presensi_jenis/${data.id}`)).submit();
                        }
                    });
                });
            }
        });
    </script>
    @if (session('dataSaved') !== null)
        <script>
            Swal.fire({
                icon: '{{ session('dataSaved') ? 'success' : 'error' }}',
                title: '{{ session('dataSaved') ? 'Success' : 'Error' }}',
                text: '{{ session('message') }}',
            });
        </script>
    @endif
@endsection
