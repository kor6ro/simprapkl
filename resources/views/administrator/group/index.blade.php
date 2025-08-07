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
                            <a href="#">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Group</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-auto">
            <a href="{{ route('admin.group.create') }}" class="btn btn-success">
                <i class="fa fa-plus me-1"></i> Tambah
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">#</th>
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
        $('table').DataTable({
            fixedHeader: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: baseUrl('/group/fetch'),
                headers: {
                    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN')
                },
                dataSrc: "data",
                type: "POST"
            },
            order: [
                [2, 'desc']
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
                    visible: true,
                },
                {
                    data: 'created_at',
                    visible: false,
                    render: function(data) {
                        if (!data) return "";

                        const date = new Date(data);
                        return date.toLocaleString();
                    }
                },
                {
                    data: 'id',
                    name: 'id',
                    render: function(data, i, row) {
                        var div = document.createElement("div");
                        div.className = "row-action";

                        // Edit
                        var btn = document.createElement("button");
                        btn.className = "btn btn-warning btn-action mx-1 action-edit";
                        btn.innerHTML = '<i class="icon fa fa-edit"></i>';
                        div.append(btn);

                        // Delete
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
                $(".action-edit", row).click(function(e) {
                    const url = baseUrl('/group/' + data.id + '/edit');
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
                            $('#form-destroy').attr('action', url + '/' + data.id)
                                .trigger('submit');
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
