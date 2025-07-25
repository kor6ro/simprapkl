@extends('layout.main')

@section('css')
    <style> </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Task Breakdown</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Task Breakdown</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if (isRole('Admin'))
        <div class="row mb-3">
            <div class="col-auto">
                <a href="{{ route('task_break_down.create') }}" class="btn btn-success">
                    <i class="fa fa-plus me-1"></i> Tambah
                </a>
            </div>
        </div>
    @endif

    <div class="card d-none d-md-block">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>File Upload</th>
                        <th>Created At</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="d-block d-md-none mt-3">
        @forelse ($tugasHariIni as $tugas)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">{{ $tugas->nama }}</h5>
                    <p class="mb-2">
                        <strong>File:</strong><br>
                        @if ($tugas->file_upload)
                            <a href="{{ asset('uploads/task_break_down_file_upload/' . $tugas->file_upload) }}"
                                class="btn btn-sm btn-info" target="_blank">
                                <i class="fa fa-download me-1"></i> File
                            </a>
                        @else
                            <span class="text-muted">Tidak ada file</span>
                        @endif
                    </p>
                    <p class="text-muted mb-2">
                        <small>Dibuat: {{ \Carbon\Carbon::parse($tugas->created_at)->format('d-m-Y H:i') }}</small>
                    </p>

                    @if (isRole('Admin'))
                        <div class="d-flex gap-2">
                            <a href="{{ route('task_break_down.edit', $tugas->id) }}" class="btn btn-sm btn-warning">
                                <i class="fa fa-edit me-1"></i> Edit
                            </a>
                            <form class="form-delete" data-id="{{ $tugas->id }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash me-1"></i> Hapus
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="alert alert-warning">Tidak ada tugas hari ini.</div>
        @endforelse
    </div>

    {{-- Form delete global --}}
    <div class="d-none">
        <form id="form-destroy" method="POST">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection

@section('js')
    <script>
        const userRole = "{{ Auth::user()->group->nama }}";

        $('table').DataTable({
            fixedHeader: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: baseUrl('/task_break_down/fetch'),
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
                    className: 'text-center',
                    width: '50px',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'nama'
                },
                {
                    data: 'file_upload',
                    render: function(data) {
                        const url = assetUrl('uploads/task_break_down_file_upload/') + data;
                        const btn = document.createElement("a");
                        btn.className = "btn btn-info " + (data == null ? "disabled" : "");
                        btn.innerHTML = '<i class="fa fa-download me-1"></i> File';
                        btn.href = url;
                        btn.target = "_blank";
                        return btn.outerHTML;
                    }
                },
                {
                    data: 'created_at',
                    render: function(data) {
                        return data ? new Date(data).toLocaleString() : '';
                    }
                },
                {
                    data: 'id',
                    render: function(data) {
                        const div = document.createElement("div");
                        div.className = "row-action";

                        if (userRole === 'Admin') {
                            const editBtn = document.createElement("button");
                            editBtn.className = "btn btn-warning btn-action mx-1 action-edit";
                            editBtn.innerHTML = '<i class="icon fa fa-edit"></i>';
                            div.append(editBtn);

                            const delBtn = document.createElement("button");
                            delBtn.className = "btn btn-danger btn-action mx-1 action-hapus";
                            delBtn.innerHTML = '<i class="icon fa fa-trash-alt"></i>';
                            div.append(delBtn);
                        }

                        return div.outerHTML;
                    },
                    width: "150px",
                    orderable: false
                }
            ],
            createdRow: function(row, data) {
                $(".action-edit", row).click(function() {
                    const url = baseUrl('/task_break_down/' + data.id + '/edit');
                    window.location.href = url;
                });

                $(".action-hapus", row).click(function(e) {
                    e.preventDefault();
                    Swal.fire({
                        icon: "warning",
                        title: "Warning",
                        text: "Yakin ingin menghapus data ini?",
                        showCancelButton: true,
                        confirmButtonText: "Hapus",
                        cancelButtonText: "Batal",
                    }).then((result) => {
                        if (result.value) {
                            $('#form-destroy').attr('action', baseUrl('/task_break_down/' + data
                                .id)).submit();
                        }
                    });
                });
            }
        });

        // Hapus untuk mobile
        $(document).on('submit', '.form-delete', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            Swal.fire({
                icon: "warning",
                title: "Warning",
                text: "Yakin ingin menghapus data ini?",
                showCancelButton: true,
                confirmButtonText: "Hapus",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.value) {
                    $('#form-destroy').attr('action', baseUrl('/task_break_down/' + id)).submit();
                }
            });
        });
    </script>

    @if (session()->has('dataSaved'))
        <script>
            Swal.fire({
                icon: '{{ session()->get('dataSaved') ? 'success' : 'error' }}',
                title: '{{ session()->get('dataSaved') ? 'Success' : 'Error' }}',
                text: '{{ session('message') }}'
            });
        </script>
    @endif
@endsection
