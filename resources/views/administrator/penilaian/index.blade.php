@extends('layout.main')
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        #penilaian-table thead th.no-sort::before,
        #penilaian-table thead th.no-sort::after {
            display: none !important;
        }
    </style>
@endsection
@section('content')
    @if (in_array(auth()->user()->group_id, [1, 2, 3, 4, 5, 6, 7]))
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Penilaian</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Penilaian</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            @if (in_array(auth()->user()->group_id, [1, 2]))
                <div class="col-md-auto">
                    <button type="button" class="btn btn-info mb-2" data-bs-toggle="modal"
                        data-bs-target="#batchCreateModal">
                        <i class="fa fa-users me-1"></i> Buat Draf Penilaian
                    </button>
                </div>
            @endif

            @if (auth()->user()->group_id != 4)
                <div class="col-md-4 ms-auto">
                    <label for="periode-filter" class="form-label">Filter Periode PKL</label>
                    <select class="form-control" id="periode-filter" name="periode_id">
                        <option value="">Semua Periode</option>
                        @foreach ($periodes as $periode)
                            <option value="{{ $periode->id }}">
                                {{ \Carbon\Carbon::parse($periode->awal_periode)->locale('id')->isoFormat('D MMMM YYYY') }}
                                -
                                {{ \Carbon\Carbon::parse($periode->akhir_periode)->locale('id')->isoFormat('D MMMM YYYY') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card-body">
                <table id="penilaian-table" class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Siswa</th>
                            <th scope="col">Penilai</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Nilai Rata-Rata</th>
                            @if (in_array(auth()->user()->group_id, [1, 2, 4, 5, 6, 7]))
                                <th scope="col">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        @if (in_array(auth()->user()->group_id, [1, 2]))
            <div class="modal fade" id="batchCreateModal" tabindex="-1" aria-labelledby="batchCreateModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('admin.penilaian.batchCreate') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="batchCreateModalLabel">Buat Draf Penilaian</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="modal-periode-select" class="form-label">Pilih Periode PKL</label>
                                    <select class="form-control" id="modal-periode-select" name="periode_id" required>
                                        <option value="">-- Pilih Periode --</option>
                                        @foreach ($periodes as $periode)
                                            <option value="{{ $periode->id }}">
                                                {{ \Carbon\Carbon::parse($periode->awal_periode)->locale('id')->isoFormat('D MMMM YYYY') }}
                                                -
                                                {{ \Carbon\Carbon::parse($periode->akhir_periode)->locale('id')->isoFormat('D MMMM YYYY') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Batal</button>
                                <button type="submit" class="btn btn-primary">Buat Draf</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            const userGroupId = {{ auth()->user()->group_id }};

            const allowedManagerGroupIds = [1, 2, 6];

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOut'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            let tableColumns = [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    searchable: false,
                    orderable: false,
                    width: '50px',
                    className: 'text-center no-sort'
                },
                {
                    data: 'siswa_nama',
                    name: 'siswa.name',
                    orderable: false,
                    className: 'no-sort'
                },
                {
                    data: 'penilai_nama',
                    name: 'penilai.name',
                    orderable: false,
                    className: 'no-sort'
                },
                {
                    data: 'tanggal_penilaian',
                    name: 'tanggal_penilaian',
                    orderable: false,
                    className: 'no-sort'
                },
                {
                    data: 'nilai_rata_rata',
                    name: 'nilai_rata_rata',
                    orderable: false,
                    className: 'text-center no-sort'
                }
            ];

            if ([1, 2, 4, 5, 6, 7].includes(userGroupId)) {
                tableColumns.push({
                    data: 'id',
                    name: 'id',
                    orderable: false,
                    searchable: false,
                    width: "180px",
                    className: 'no-sort',
                    render: function(data, type, row) {
                        var div = document.createElement("div");
                        div.className = "row-action";
                        var detailUrl = "{{ route('admin.penilaian.show', ':id') }}".replace(':id',
                            data);
                        var cetakUrl = "{{ route('admin.penilaian.cetak', ':id') }}".replace(':id',
                            data);
                        var sertifikatUrl = "{{ route('admin.penilaian.sertifikat', ':id') }}".replace(
                            ':id', data);

                        let buttonsHtml = '';

                        buttonsHtml += `
                        <a href="${cetakUrl}" target="_blank" class="btn btn-info btn-action mx-1" title="Cetak Penilaian">
                            <i class="icon fa fa-print"></i>
                        </a>
                        <a href="${sertifikatUrl}" target="_blank" class="btn btn-success btn-action mx-1" title="Cetak Sertifikat">
                            <i class="icon fa fa-award"></i>
                        </a>`;

                        if (allowedManagerGroupIds.includes(userGroupId)) {
                            buttonsHtml += `
                            <a href="${detailUrl}" class="btn btn-primary btn-action mx-1" title="Detail/Isi Penilaian">
                                <i class="icon fa fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-action mx-1 action-hapus" data-id="${data}" title="Hapus">
                                <i class="icon fa fa-trash-alt"></i>
                            </button>`;
                        }

                        div.innerHTML = buttonsHtml;
                        return div.outerHTML;
                    }
                });
            }

            var table = $('#penilaian-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.penilaian.fetch') }}",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: function(d) {
                        d.periode_id = $('#periode-filter').val();
                    }
                },
                order: [],
                columns: tableColumns,
                createdRow: function(row, data) {
                    if (allowedManagerGroupIds.includes(userGroupId)) {
                        $(row).on('click', '.action-hapus', function(e) {
                            e.preventDefault();
                            let id = $(this).data('id');
                            let url = "{{ route('admin.penilaian.destroy', ':id') }}".replace(
                                ':id', id);
                            Swal.fire({
                                icon: "warning",
                                title: "Anda Yakin?",
                                text: "Data yang dihapus tidak dapat dikembalikan.",
                                showCancelButton: true,
                                confirmButtonText: "Ya, Hapus!",
                                cancelButtonText: "Batal",
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#6c757d',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: url,
                                        type: 'POST',
                                        data: {
                                            '_token': '{{ csrf_token() }}',
                                            '_method': 'DELETE'
                                        },
                                        success: function(response) {
                                            Toast.fire({
                                                icon: 'success',
                                                title: response
                                                    .success
                                            });
                                            table.ajax.reload();
                                        },
                                        error: function(xhr) {
                                            Toast.fire({
                                                icon: 'error',
                                                title: xhr
                                                    .responseJSON
                                                    .error ||
                                                    'Gagal menghapus data.'
                                            });
                                        }
                                    });
                                }
                            });
                        });
                    }
                },
            });

            $('#periode-filter').on('change', function() {
                table.ajax.reload();
            });

            @if (session('success'))
                Toast.fire({
                    icon: 'success',
                    title: '{{ session('success') }}'
                });
            @elseif (session('error'))
                Toast.fire({
                    icon: 'error',
                    title: '{{ session('error') }}'
                });
            @endif
        });
    </script>
@endsection
