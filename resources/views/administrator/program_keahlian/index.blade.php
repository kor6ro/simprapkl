@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Program Keahlian</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Program Keahlian</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-auto">
            <a href="{{ route('admin.program-keahlian.create') }}" class="btn btn-success">
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
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
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
        $('table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.program-keahlian.fetch') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    searchable: false,
                    orderable: false,
                    width: '50px',
                    sClass: 'text-center'
                },
                {
                    data: 'nama',
                    name: 'nama'
                },
                {
                    data: 'id',
                    name: 'id',
                    orderable: false,
                    searchable: false,
                    width: "150px",
                    render: function(data, type, row) {
                        var div = document.createElement("div");
                        div.className = "row-action";
                        var editUrl = "{{ route('admin.program-keahlian.edit', ':id') }}".replace(':id',
                            data);
                        div.innerHTML = `
                            <a href="${editUrl}" class="btn btn-warning btn-action mx-1" title="Edit"><i class="icon fa fa-edit"></i></a>
                            <button type="button" class="btn btn-danger btn-action mx-1 action-hapus" data-id="${data}" title="Hapus"><i class="icon fa fa-trash-alt"></i></button>
                        `;
                        return div.outerHTML;
                    }
                },
            ],
            createdRow: function(row, data) {
                $(row).on('click', '.action-hapus', function(e) {
                    e.preventDefault();
                    let id = $(this).data('id');
                    let url = "{{ route('admin.program-keahlian.destroy', ':id') }}".replace(':id', id);
                    Swal.fire({
                        icon: "warning",
                        title: "Anda Yakin?",
                        text: "Data yang dihapus tidak dapat dikembalikan.",
                        showCancelButton: true,
                        confirmButtonText: "Ya, Hapus!",
                        cancelButtonText: "Batal",
                    }).then((result) => {
                        if (result.value) {
                            $('#form-destroy').attr('action', url).trigger('submit');
                        }
                    });
                });
            },
        });

        @if (session()->has('dataSaved'))
            Swal.fire({
                icon: '{{ session()->get('dataSaved') ? 'success' : 'error' }}',
                title: '{{ session()->get('dataSaved') ? 'Success' : 'Error' }}',
                text: '{{ session()->get('message') }}',
            });
        @endif
    </script>
@endsection
