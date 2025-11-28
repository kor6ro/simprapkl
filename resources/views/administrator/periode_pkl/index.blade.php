@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Periode PKL</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Periode PKL</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-auto">
            <a href="{{ route('admin.periode-pkl.create') }}" class="btn btn-success">
                <i class="fa fa-plus me-1"></i> Tambah
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped" id="table-periode-pkl">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Awal Periode</th>
                        <th scope="col">Akhir Periode</th>
                        <th scope="col">Jumlah Peserta</th>
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
        $('#table-periode-pkl').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.periode-pkl.fetch') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            order: [
                [1, 'desc']
            ],
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'id',
                    sClass: 'text-center',
                    width: '50px',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'awal_periode',
                    name: 'awal_periode'
                },
                {
                    data: 'akhir_periode',
                    name: 'akhir_periode'
                },
                {
                    data: 'jumlah_peserta',
                    name: 'jumlah_peserta',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'aksi',
                    name: 'aksi',
                    searchable: false,
                    orderable: false,
                    width: "150px"
                }
            ],
            createdRow: function(row, data) {
                // Tombol Hapus
                $(row).on('click', '.action-hapus', function(e) {
                    e.preventDefault();
                    let url = $(this).data('url');

                    Swal.fire({
                        icon: "warning",
                        title: "Warning",
                        text: "Anda yakin akan menghapus data ini?",
                        showCancelButton: true,
                        confirmButtonText: "Hapus",
                        cancelButtonText: "Batal",
                    }).then((result) => {
                        if (result.value) {
                            $('#form-destroy').attr('action', url).trigger('submit');
                        }
                    });
                });
            },
        });
    </script>

    {{-- Script untuk menampilkan notifikasi sukses/error --}}
    @if (session()->has('dataSaved') && session()->get('dataSaved') == true)
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session()->get('message') }}'
            });
        </script>
    @endif
    @if (session()->has('dataSaved') && session()->get('dataSaved') == false)
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session()->get('message') }}'
            });
        </script>
    @endif
@endsection
