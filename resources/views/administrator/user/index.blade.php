@extends('layout.main')
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">User</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">User</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3 align-items-end">
        <div class="col-md-auto mb-2">
            <label class="form-label fw-bold">Aksi User</label>
            <div class="btn-group" role="group">
                <a href="{{ route('admin.user.batch.create') }}" class="btn btn-success">
                    <i class="fa fa-users me-2"></i>Tambah User
                </a>
            </div>
        </div>
        <div class="col-md-5 mb-2">
            <label for="periode-export" class="form-label fw-bold">Reset & Ekspor Kredensial Siswa</label>
            <div class="input-group">
                <select class="form-select" id="periode-export">
                    <option value="">-- Pilih Periode PKL --</option>
                    @foreach ($periodePkl as $periode)
                        <option value="{{ $periode->id }}">{{ $periode->nama }}
                            ({{ \Carbon\Carbon::parse($periode->awal_periode)->format('d M Y') }})
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-info" id="export-credentials-btn" disabled>
                    <i class="fa fa-file-excel me-2"></i>Ekspor
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="user-table" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Name</th>
                        <th scope="col">Username</th>
                        <th scope="col">Email</th>
                        <th scope="col">Validasi</th>
                        <th scope="col">Sekolah</th>
                        <th scope="col">Group</th>
                        <th scope="col">ID PKL</th>
                        <th scope="col">Alamat</th>
                        <th scope="col">Created At</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // 1. Inisialisasi Toast Notifikasi
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

            // 2. Inisialisasi DataTable
            var table = $('#user-table').DataTable({
                fixedHeader: true,
                processing: true,
                serverSide: true,
                responsive: true, // Tetap true
                ajax: {
                    url: "{{ route('admin.user.fetch') }}",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                },
                order: [
                    [9, 'desc'] // Kolom 'created_at'
                ],
                columns: [{
                        data: 'DT_RowIndex',
                        sClass: 'text-center',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'username',
                        name: 'username'
                    },
                    {
                        data: 'email',
                        name: 'email',
                        visible: false // Sembunyi di desktop, muncul di child row
                    },
                    {
                        data: 'validasi',
                        name: 'validasi',
                        render: function(data) {
                            const isValid = data == 1;
                            const text = isValid ? "Validasi" : "Belum Validasi";
                            const color = isValid ? "success" : "danger";
                            return `<span class="badge bg-${color}">${text}</span>`;
                        }
                    },
                    {
                        data: 'sekolah.nama',
                        name: 'sekolah.nama',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'group.nama',
                        name: 'group.nama',
                        orderable: false
                    },
                    {
                        data: 'id_pkl',
                        name: 'id_pkl'
                    },
                    {
                        data: 'alamat',
                        name: 'alamat',
                        visible: false // Sembunyi di desktop, muncul di child row
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        visible: false // Sembunyi di desktop, muncul di child row
                    },
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        width: "100px",
                        render: function(data, type, row) {
                            const editUrl = "{{ route('admin.user.edit', ':id') }}".replace(':id',
                                data);
                            const resetUrl = "{{ route('admin.user.resetpass', ':id') }}"
                                .replace(':id', data);
                            const destroyUrl = "{{ route('admin.user.destroy', ':id') }}".replace(
                                ':id', data);
                            // Tombol-tombol ini sekarang akan dirender di child row juga
                            return `
                                <div class="d-flex justify-content-center">
                                    <a href="${editUrl}" class="btn btn-warning btn-sm mx-1 action-edit" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-info btn-sm mx-1 action-reset" data-url="${resetUrl}" data-name="${row.name}" title="Reset Password">
                                    <i class="fa fa-key"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm mx-1 action-hapus" data-url="${destroyUrl}" data-name="${row.name}" title="Hapus">
                                        <i class="fa fa-trash-alt"></i>
                                    </button>
                                </div>
                            `;
                        }
                    },
                ],
                // KOSONGKAN 'createdRow' KARENA KITA PAKAI EVENT DELEGATION
                createdRow: function(row, data) {
                    // Tidak perlu event listener di sini lagi
                },

            });

            // 3. [FIX] Paksa Hitung Ulang Lebar Tabel
            // Memberi jeda 350ms agar animasi sidebar selesai
            setTimeout(function() {
                table.columns.adjust().responsive.recalc();
            }, 350);

            // Hitung ulang juga saat ukuran window berubah
            $(window).on('resize', function() {
                table.columns.adjust().responsive.recalc();
            });


            // 4. [FIX] Event Delegation untuk Tombol Aksi
            // Listener ini akan menempel di TBODY dan berfungsi
            // baik di desktop maupun di child row (mobile)

            // Listener untuk HAPUS
            $('#user-table tbody').on('click', '.action-hapus', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                const name = $(this).data('name');

                Swal.fire({
                    icon: "warning",
                    title: "Anda Yakin?",
                    html: `Data user <strong>${name}</strong> akan dihapus.`,
                    showCancelButton: true,
                    confirmButtonText: "Ya, Hapus!",
                    cancelButtonText: "Batal",
                    confirmButtonColor: '#d33',
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
                                    title: response.success
                                });
                                table.ajax.reload(null,
                                    false); // Reload tanpa reset paging
                            },
                            error: function(xhr) {
                                Toast.fire({
                                    icon: 'error',
                                    title: xhr.responseJSON
                                        .error ||
                                        'Gagal menghapus data.'
                                });
                            }
                        });
                    }
                });
            });

            // Listener untuk RESET PASSWORD
            $('#user-table tbody').on('click', '.action-reset', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                const name = $(this).data('name');

                Swal.fire({
                    icon: "warning",
                    title: "Anda Yakin?",
                    html: `Password untuk user <strong>${name}</strong> akan direset.`,
                    showCancelButton: true,
                    confirmButtonText: "Ya, Reset!",
                    cancelButtonText: "Batal",
                    confirmButtonColor: '#3085d6',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                '_token': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    html: `${response.message}<br><br>Password Baru: <code style="background-color: #e9ecef; padding: 4px 8px; border-radius: 4px;">${response.new_password}</code><br><small>Mohon segera catat dan berikan password ini kepada user.</small>`
                                });
                            },
                            error: function(xhr) {
                                Toast.fire({
                                    icon: 'error',
                                    title: xhr.responseJSON
                                        .error ||
                                        'Gagal mereset password.'
                                });
                            }
                        });
                    }
                });
            });


            // 5. Logika untuk Ekspor Kredensial
            const exportBtn = $('#export-credentials-btn');
            const periodeSelect = $('#periode-export');

            periodeSelect.on('change', function() {
                exportBtn.prop('disabled', $(this).val() === '');
            });

            exportBtn.on('click', function(e) {
                e.preventDefault();
                const periodeId = periodeSelect.val();
                if (!periodeId) {
                    alert('Silakan pilih periode terlebih dahulu.');
                    return;
                }

                let exportUrl =
                    "{{ route('admin.user.export_credentials', ['periode' => ':periodeId']) }}";
                exportUrl = exportUrl.replace(':periodeId', periodeId);

                Swal.fire({
                    title: 'Anda Yakin?',
                    text: "Aksi ini akan me-reset password siswa pada periode yang dipilih dan membuat file Excel berisi kredensial baru. Aksi ini tidak bisa dibatalkan.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Reset & Ekspor!',
                    cancelButtonText: '> Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Tampilkan loader
                        $('#loaderOverlay').show();

                        // Buat notifikasi Toast bahwa proses dimulai
                        Toast.fire({
                            icon: 'info',
                            title: 'Sedang memproses reset & ekspor... Ini mungkin perlu waktu.'
                        });

                        // Arahkan untuk memulai proses (yang akan redirect kembali)
                        window.location.href = exportUrl;
                    }
                });
            });

            // 6. Pemicu Notifikasi dari Session
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

            // 7. Pemicu Download Otomatis (setelah redirect dari ekspor)
            @if (session('download_url'))
                // Sembunyikan loader
                $('#loaderOverlay').hide();

                // Beri jeda 1 detik agar user sempat melihat notifikasi sukses
                setTimeout(function() {
                    window.location.href = '{{ session('download_url') }}';
                }, 1000);
            @else
                // Jika tidak ada download, pastikan loader tersembunyi
                $('#loaderOverlay').hide();
            @endif
        });
    </script>
@endsection
