@extends('layout.main')

@section('css')
{{-- Dependency CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<style>
    /* Styling Esensial */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .section-title {
        font-weight: bold;
        font-size: 1.3rem;
    }
    .anggota-list {
        max-height: 100px;
        overflow-y: auto;
        white-space: normal;
        word-wrap: break-word;
        scrollbar-width: thin;
    }
    .badge-sales { background-color: #28a745; }
    .badge-teknisi { background-color: #17a2b8; }
</style>
@endsection

@section('content')
@if(isRole('Admin'))
<h4 class="fw-bold mb-4">Setting Anggota Tim</h4>
@endif

<div class="card">
    <div class="card-body">
        <div class="section-header mb-3">
            <div class="section-title">📋 DAFTAR TIM HARI INI</div>
            @if(isRole('Admin'))
            <a href="{{ route('admin.setting_tugas.create') }}" class="btn btn-success btn-sm">
                ➕ Tambah Tim
            </a>
            @endif
        </div>

        {{-- PERUBAHAN: Form filter dibuat lebih ringkas dan tombol Reset dihapus --}}
        <form id="filter-form" class="row g-2 align-items-center mb-3 border-top pt-3">
            <div class="col-md-auto">
                <label for="filter-ketua" class="form-label">Ketua Tim:</label>
                <select id="filter-ketua" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    @foreach($karyawan as $ketua)
                        <option value="{{ $ketua->id }}">{{ $ketua->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <label for="filter-divisi" class="form-label">Jenis Tim:</label>
                <select id="filter-divisi" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    <option value="sales">SALES</option>
                    <option value="teknisi">TEKNISI</option>
                </select>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped" id="teams-table" style="width:100%">
                <thead class="table-bordered">
                    <tr>
                        <th>Ketua Tim</th>
                        <th>Jenis Tim</th>
                        <th>Anggota Tim</th>
                        <th>Created At</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- DataTables will populate this --}}
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('js')
{{-- Dependency JS --}}
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Inisialisasi DataTable
    const teamsDataTable = $('#teams-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.setting_tugas.data") }}',
            data: function (d) {
                d.ketua_id = $('#filter-ketua').val();
                d.divisi = $('#filter-divisi').val();
            }
        },
        columns: [
            { data: 'ketua_name', name: 'ketua_name' },
            {
                data: 'divisi',
                name: 'divisi',
                render: data => `<span class="badge ${data === 'sales' ? 'badge-sales' : 'badge-teknisi'} text-white px-2 py-1">${data.toUpperCase()}</span>`
            },
            {
                data: 'anggota_names',
                name: 'anggota_names',
                orderable: false,
                render: data => {
                    if (data && data.length > 0) {
                        const anggotaString = data.join(', ');
                        return `<div class="anggota-list" title="${anggotaString}">${anggotaString}</div>`;
                    }
                    return '<div class="text-muted">Tidak ada anggota</div>';
                }
            },
            {
                data: 'created_at',
                name: 'created_at',
                render: data => `<small class="text-muted">${data}</small>`
            },
            {
                data: 'id',
                name: 'actions',
                orderable: false,
                searchable: false,
                render: (data, type, row) => `
                    <div class="btn-group" role="group">
                        <a href="{{ url('admin/setting-tugas') }}/${data}/edit" class="btn btn-info btn-sm" title="Edit Tim">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteTeam(${data})" title="Hapus Tim">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>`
            }
        ],
        order: [[3, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
        responsive: true
    });

    // Event handler untuk perubahan pada dropdown filter
    $('#filter-ketua, #filter-divisi').on('change', function() {
        teamsDataTable.ajax.reload(); // Muat ulang tabel setiap kali filter diubah
    });

    // PERUBAHAN: Event handler untuk tombol reset dihapus

    // Fungsi Hapus Tim
    window.deleteTeam = function(teamId) {
        Swal.fire({
            icon: 'warning',
            title: 'Hapus Tim?',
            text: 'Yakin ingin menghapus tim ini?',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('admin/setting-tugas') }}/${teamId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', 'Berhasil!', data.message);
                        teamsDataTable.ajax.reload();
                    } else {
                        showAlert('error', 'Oops...', data.message);
                    }
                })
                .catch(() => showAlert('error', 'Error', 'Gagal menghapus tim.'));
            }
        });
    };

    // Tampilkan notifikasi dari session
    @if(session('success'))
        showAlert('success', 'Berhasil!', '{{ session('success') }}');
    @endif
    @if(session('error'))
        showAlert('error', 'Oops...', '{{ session('error') }}');
    @endif
});

// Helper untuk SweetAlert
function showAlert(icon, title, text, callback = null) {
    Swal.fire({ icon, title, text }).then(result => {
        if (callback && typeof callback === 'function') {
            callback();
        }
    });
}
</script>
@endsection
