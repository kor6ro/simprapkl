@extends('layout.main')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        .filter-container { display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end; }
        .row-action { display: flex; gap: 0.25rem; justify-content: center; }
    </style>
@endsection

@section('content')
    <h4 class="fw-bold mb-4">Atur Anggota Tim</h4>
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">DAFTAR TIM</h5>
                @if(isRole('Admin'))
                <a href="{{ route('admin.tim.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> Tambah Tim
                </a>
                @endif
            </div>

            <div class="border-top pt-3 mb-3">
                <div id="filter-form" class="filter-container">
                    <div class="filter-item">
                        <label for="filter-ketua" class="form-label">Ketua Tim:</label>
                        <select id="filter-ketua" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach($karyawan as $ketua)
                                <option value="{{ $ketua->id }}">{{ $ketua->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="filter-divisi" class="form-label">Jenis Tim:</label>
                        <select id="filter-divisi" class="form-select form-select-sm">
                            <option value="">Semua</option>
                             @foreach($daftar_divisi as $divisi)
                                <option value="{{ $divisi->id }}">{{ $divisi->nama_divisi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="filter-bulan" class="form-label">Bulan/Tahun:</label>
                        <input type="month" id="filter-bulan" class="form-control form-control-sm">
                    </div>
                </div>
            </div>

            <table class="table table-striped" id="teams-table" style="width:100%">
                <thead>
                    <tr>
                        <th>Ketua Tim</th>
                        <th>Jenis Tim</th>
                        <th>Anggota Tim</th>
                        <th>Dibuat Pada</th>
                        <th>Laporan Siswa</th> 
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Form Hapus Tersembunyi --}}
    <form id="form-destroy" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            function setDefaultMonth() {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                $('#filter-bulan').val(`${year}-${month}`);
            }
            setDefaultMonth();

            const teamsDataTable = $('#teams-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("admin.tim.data") }}',
                    type: 'POST',
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    data: d => {
                        d.ketua_id = $('#filter-ketua').val();
                        d.divisi_id = $('#filter-divisi').val();
                        d.bulan = $('#filter-bulan').val();
                    }
                },
                columns: [
                    { data: 'ketua_name', name: 'ketua.name' },
                    { data: 'divisi_name', name: 'divisi.nama_divisi' },
                    { data: 'anggota_names', name: 'anggota', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at', render: data => `<small>${new Date(data).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })}</small>` },
                    
                    // ### KOLOM BARU UNTUK TOMBOL DETAIL ###
                    { 
                        data: 'id', 
                        name: 'laporan', 
                        orderable: false, 
                        searchable: false,
                        render: function(data) {
                            const detailUrl = `{{ url('admin/tim') }}/${data}`;
                            return `<a href="${detailUrl}" class="btn btn-info btn-sm" title="Lihat Laporan"><i class="fas fa-eye"></i> Detail</a>`;
                        },
                        width: "120px",
                        className: "text-center",
                    },

                    { 
                        data: 'id', 
                        name: 'actions', 
                        orderable: false, 
                        searchable: false, 
                        width: "100px" 
                    }
                ],
                order: [[3, 'desc']],
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                responsive: true,
                createdRow: function(row, data) {
                    // ### PERUBAHAN UTAMA DI SINI ###
                    // Kolom Aksi sekarang ada di posisi terakhir (indeks 5)
                    const actionsCell = $(row).find('td').eq(5); 
                    
                    const editUrl = `{{ url('admin/tim') }}/${data.id}/edit`;
                    const deleteUrl = `{{ route('admin.tim.destroy', '') }}/${data.id}`;

                    // Hanya render tombol Edit dan Hapus di kolom Aksi
                    actionsCell.html(`
                        <div class="row-action">
                            <a href="${editUrl}" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-danger btn-sm action-hapus" data-url="${deleteUrl}" data-ketua="${data.ketua_name}" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `);
                }
            });

            $('#filter-ketua, #filter-divisi, #filter-bulan').on('change', () => teamsDataTable.ajax.reload());
            
            $('#teams-table tbody').on('click', '.action-hapus', function() {
                const deleteUrl = $(this).data('url');
                const ketuaName = $(this).data('ketua');
                
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Anda yakin ingin menghapus tim yang diketuai oleh "${ketuaName}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#form-destroy').attr('action', deleteUrl).submit();
                    }
                });
            });

            @if(session('success'))
                Swal.fire('Berhasil!', '{{ session('success') }}', 'success');
            @elseif(session('error'))
                Swal.fire('Gagal!', '{{ session('error') }}', 'error');
            @endif
        });
    </script>
@endsection