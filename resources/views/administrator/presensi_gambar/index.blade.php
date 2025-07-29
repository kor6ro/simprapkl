@extends('layout.main')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('css')
    <style>
        .bukti-preview img {
            cursor: pointer;
            transition: transform 0.2s;
        }

        .bukti-preview img:hover {
            transform: scale(1.1);
        }

        .modal-body img {
            width: 100%;
            height: auto;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Bukti Presensi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Bukti Presensi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-auto">
            <a href="{{ route('presensi_gambar.create') }}" class="btn btn-success">
                <i class="fa fa-plus me-1"></i> Tambah Bukti
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama User</th>
                        <th>Jenis Presensi</th>
                        <th>Tanggal</th>
                        <th>Sesi</th>
                        <th>Bukti</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Modal untuk preview gambar -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Preview Bukti Presensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Bukti Presensi" class="img-fluid">
                </div>
            </div>
        </div>
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
        $('table').DataTable({
            fixedHeader: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: baseUrl('/presensi_gambar/fetch'),
                headers: {
                    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN')
                },
                dataSrc: "data",
                type: "POST"
            },
            order: [
                [3, 'desc'] // Order by tanggal
            ],
            columns: [{
                    data: 'DT_RowIndex',
                    className: 'text-center',
                    width: '50px',
                    searchable: false,
                    orderable: false,
                },
                {
                    data: 'nama_user',
                    name: 'nama_user',
                    searchable: true
                },
                {
                    data: 'jenis_presensi',
                    name: 'jenis_presensi',
                    searchable: true
                },
                {
                    data: 'tanggal_presensi',
                    name: 'tanggal_presensi',
                    searchable: true
                },
                {
                    data: 'sesi',
                    name: 'sesi',
                    searchable: true
                },
                {
                    data: 'bukti_preview',
                    name: 'bukti_preview',
                    orderable: false,
                    searchable: false,
                    className: 'bukti-preview'
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return `
                            <div class="row-action">
                                <button class="btn btn-info btn-action mx-1 action-view" title="Lihat Detail">
                                    <i class="icon fa fa-eye"></i>
                                </button>
                                <button class="btn btn-warning btn-action mx-1 action-edit" title="Edit">
                                    <i class="icon fa fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-action mx-1 action-hapus" title="Hapus">
                                    <i class="icon fa fa-trash-alt"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            createdRow: function(row, data) {
                // Handler untuk preview gambar
                $(row).find('.bukti-preview img').click(function() {
                    const imgSrc = $(this).attr('src');
                    $('#modalImage').attr('src', imgSrc);
                    $('#imageModal').modal('show');
                });

                // Handler untuk view detail
                $(".action-view", row).click(function() {
                    const url = baseUrl('/presensi_gambar/' + data.id);
                    window.location.href = url;
                });

                // Handler untuk edit
                $(".action-edit", row).click(function() {
                    const url = baseUrl('/presensi_gambar/' + data.id + '/edit');
                    window.location.href = url;
                });

                // Handler untuk hapus
                $(".action-hapus", row).click(function(e) {
                    e.preventDefault();
                    Swal.fire({
                        icon: "warning",
                        title: "Warning",
                        text: "Yakin ingin menghapus bukti presensi ini?",
                        showCancelButton: true,
                        confirmButtonText: "Hapus",
                        cancelButtonText: "Batal",
                        confirmButtonColor: '#dc3545'
                    }).then((result) => {
                        if (result.value) {
                            $('#form-destroy').attr('action', baseUrl('/presensi_gambar/' + data
                                    .id))
                                .submit();
                        }
                    });
                });
            }
        });

        @if (session()->has('dataSaved'))
            Swal.fire({
                icon: '{{ session()->get('dataSaved') ? 'success' : 'error' }}',
                title: '{{ session()->get('dataSaved') ? 'Success' : 'Error' }}',
                text: '{{ session('message') }}'
            });
        @endif
    </script>
@endsection
