@extends('layout.main')
@section('css')
    <style>

    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Divisi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="#">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Divisi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-auto">
            <a href="{{ route('admin.divisi.create') }}" class="btn btn-success">
                <i class="fa fa-plus me-1"></i> Tambah
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped" id="tabel-divisi">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nama Divisi</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>

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
        $('#tabel-divisi').DataTable({
            fixedHeader: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('admin.divisi.fetch') }}",
                headers: {
                    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN')
                },
                dataSrc: "data",
                type: "POST"
            },
            order: [
                [1, 'asc']
            ],
            columns: [{
                    data: 'DT_RowIndex',
                    sClass: 'text-center',
                    width: '50px',
                    searchable: false,
                    orderable: false,
                },
                {
                    data: 'nama_divisi',
                    searchable: true,
                    orderable: true,
                },
                {
                    data: 'id',
                    name: 'id',
                    render: function(data, i, row) {
                        var div = document.createElement("div");
                        div.className = "row-action";

                        var btnEdit = document.createElement("a");
                        btnEdit.href = "{{ route('admin.divisi.index') }}/" + data + '/edit';
                        btnEdit.className = "btn btn-warning btn-action mx-1";
                        btnEdit.innerHTML = '<i class="icon fa fa-edit"></i>';
                        div.append(btnEdit);

                        var btnDelete = document.createElement("button");
                        btnDelete.className = "btn btn-danger btn-action mx-1 action-hapus";
                        btnDelete.innerHTML = '<i class="icon fa fa-trash-alt"></i>';
                        div.append(btnDelete);

                        return div.outerHTML;
                    },
                    width: "150px",
                    orderable: false,
                },
            ],
            createdRow: function(row, data) {
                $(".action-hapus", row).click(function(e) {
                    e.preventDefault();
                    Swal.fire({
                        icon: "warning",
                        title: "Peringatan",
                        text: "Anda yakin akan menghapus data '" + data.nama_divisi + "'?",
                        showCancelButton: true,
                        confirmButtonText: "Hapus",
                        cancelButtonText: "Batal",
                    }).then((result) => {
                        if (result.value) {
                            const url = "{{ route('admin.divisi.index') }}/" + data.id;
                            $('#form-destroy').attr('action', url).trigger('submit');
                        }
                    });
                });
            },
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