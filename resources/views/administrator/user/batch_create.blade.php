@extends('layout.main')

{{-- [BARU] Menambahkan CSS untuk animasi notifikasi --}}
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
@endsection

@push('styles')
    <style>
        .table-danger,
        .table-danger>th,
        .table-danger>td {
            --bs-table-bg: #f8d7da;
            --bs-table-border-color: #f5c2c7;
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: .25rem;
            font-size: .875em;
            color: var(--bs-danger-text);
        }

        .form-error {
            border-color: #dc3545;
        }

        .user-row-error {
            background-color: #f8d7da !important;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Manajemen User</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">User</a></li>
                        <li class="breadcrumb-item active">Create Batch</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <form action="{{ route('admin.user.batch.store') }}" method="POST" id="batch-create-form" novalidate>
            @csrf
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Tambah User Massal</h4>
                    <button type="button" class="btn btn-sm btn-success" id="add-user-row">
                        <i class="fa fa-plus me-2"></i>Tambah Baris
                    </button>
                </div>
                <div class="card-body">
                    {{-- Error Display (tidak diubah) --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Whoops! Ada beberapa masalah dengan input Anda:</strong><br>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            <strong>Error!</strong><br>
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row p-3 mb-4 border rounded">
                        <div class="col-12">
                            <h5 class="mb-3 font-size-16"><span class="badge bg-primary me-2">1</span>Pengaturan Umum &
                                Periode</h5>
                        </div>
                        <div class="col-lg-6 mb-3">
                            {{-- [MODIFIKASI] Label dan select untuk periode menjadi opsional --}}
                            <label for="periode_id" class="form-label fw-bold">Periode PKL (Opsional, untuk
                                Siswa/Pembimbing)</label>
                            <select class="form-select @error('periode_id') is-invalid @enderror" id="periode_id"
                                name="periode_id">
                                <option value="" selected>-- Tidak Terikat Periode --</option>
                                @foreach ($periodePkl as $periode)
                                    <option value="{{ $periode->id }}"
                                        {{ old('periode_id') == $periode->id ? 'selected' : '' }}>
                                        {{ $periode->nama ?? 'Periode' }}
                                        ({{ \Carbon\Carbon::parse($periode->awal_periode)->format('d M Y') }} -
                                        {{ \Carbon\Carbon::parse($periode->akhir_periode)->format('d M Y') }})
                                    </option>
                                @endforeach
                                <option value="new" {{ old('periode_id') == 'new' ? 'selected' : '' }}>-- Buat Periode
                                    Baru --</option>
                            </select>
                            @error('periode_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-6 row g-3 {{ old('periode_id') == 'new' ? '' : 'd-none' }}"
                            id="periode-baru-fields">
                            {{-- Field periode baru (tidak diubah) --}}
                            <div class="col-md-6">
                                <label for="awal_periode" class="form-label">Tanggal Mulai <span
                                        class="text-danger periode-required">*</span></label>
                                <input type="date" class="form-control @error('awal_periode') is-invalid @enderror"
                                    id="awal_periode" name="awal_periode" value="{{ old('awal_periode') }}">
                                @error('awal_periode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="akhir_periode" class="form-label">Tanggal Selesai <span
                                        class="text-danger periode-required">*</span></label>
                                <input type="date" class="form-control @error('akhir_periode') is-invalid @enderror"
                                    id="akhir_periode" name="akhir_periode" value="{{ old('akhir_periode') }}">
                                @error('akhir_periode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row p-3 mb-4 border rounded">
                        {{-- Pengaturan Default (tidak diubah) --}}
                        <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 font-size-16"><span class="badge bg-primary me-2">2</span>Pengaturan Default
                                (untuk Baris Baru)</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="apply-defaults-to-all">
                                <i class="fa fa-check-double me-2"></i>Terapkan ke Semua
                            </button>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label for="default_group_id" class="form-label">Role / Group</label>
                            <select class="form-select" id="default_group_id">
                                <option value="">-- Pilih Role --</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}"
                                        {{ strtolower($group->nama) == 'siswa' ? 'selected' : '' }}>{{ $group->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label for="default_sekolah_id" class="form-label">Asal Sekolah</label>
                            <select class="form-select" id="default_sekolah_id">
                                <option value="">-- Pilih Sekolah --</option>
                                @foreach ($sekolahs as $sekolah)
                                    <option value="{{ $sekolah->id }}">{{ $sekolah->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <label for="default_program_keahlian_id" class="form-label">Program Keahlian</label>
                            <select class="form-select" id="default_program_keahlian_id">
                                <option value="">-- Pilih Program --</option>
                                @foreach ($programKeahlians as $program)
                                    <option value="{{ $program->id }}">{{ $program->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 font-size-16"><span class="badge bg-primary me-2">3</span>Input Data User</h5>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalTambahSekolah"><i class="fa fa-school me-2"></i>Tambah
                                    Sekolah</button>
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                    data-bs-target="#modalTambahProgram"><i class="fa fa-book me-2"></i>Tambah
                                    Program</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table" style="min-width: 1200px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Lengkap <span class="text-danger">*</span></th>
                                        <th>Username <span class="text-danger">*</span></th>
                                        <th>Email (Opsional)</th>
                                        <th>Role <span class="text-danger">*</span></th>
                                        {{-- [MODIFIKASI] Tanda * pada header dihapus --}}
                                        <th>Sekolah</th>
                                        <th>Program</th>
                                        <th>Password (Opsional)</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="user-rows"></tbody>
                            </table>
                            <div id="no-user-placeholder" class="text-center p-5" style="display: none;">
                                <p class="text-muted">Klik "+ Tambah Baris" untuk memulai menambahkan user.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <a href="{{ route('admin.user.index') }}" class="btn btn-secondary me-2">
                        <i class="fa fa-times me-2"></i>Batal
                    </a>
                    <button type="button" class="btn btn-primary" id="btn-review">
                        <i class="fa fa-search me-2"></i>Review & Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <template id="user-row-template">
        {{-- Template baris user (tidak diubah) --}}
        <tr>
            <td>
                <input type="text" name="users[__INDEX__][name]" class="form-control" required
                    placeholder="Masukkan nama lengkap">
            </td>
            <td>
                <input type="text" name="users[__INDEX__][username]" class="form-control" required
                    placeholder="Username unik">
            </td>
            <td>
                <input type="email" name="users[__INDEX__][email]" class="form-control"
                    placeholder="username@sistem.pkl (otomatis)">
            </td>
            <td>
                <select name="users[__INDEX__][group_id]" class="form-select group-selector" required>
                    <option value="">-- Pilih Role --</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->nama }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="users[__INDEX__][sekolah_id]" class="form-select">
                    <option value="">-- Pilih Sekolah --</option>
                    @foreach ($sekolahs as $sekolah)
                        <option value="{{ $sekolah->id }}">{{ $sekolah->nama }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="users[__INDEX__][program_keahlian_id]" class="form-select">
                    <option value="">-- Pilih Program --</option>
                    @foreach ($programKeahlians as $program)
                        <option value="{{ $program->id }}">{{ $program->nama }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="password" name="users[__INDEX__][password]" class="form-control"
                    placeholder="Random 8 karakter (otomatis)">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-user-row" title="Hapus Baris">
                    <i class="fa fa-trash-alt"></i>
                </button>
            </td>
        </tr>
    </template>

    {{-- Semua Modal (tidak diubah) --}}
    <div class="modal fade" id="modalKonfirmasi" tabindex="-1" aria-labelledby="modalKonfirmasiLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalKonfirmasiLabel">Konfirmasi & Pratinjau Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan menyimpan data berikut. Mohon periksa kembali sebelum melanjutkan.</p>
                    <div id="review-summary" class="mb-3"></div>
                    <h6 class="mt-4">Daftar Nama User yang Akan Ditambahkan:</h6>
                    <div id="review-name-list" class="list-group" style="max-height: 200px; overflow-y: auto;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-final-submit">
                        <i class="fa fa-save me-2"></i>Ya, Simpan Semua Data
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalTambahSekolah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Sekolah Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-tambah-sekolah" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="nama_sekolah_baru" class="form-label">Nama Sekolah <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="nama_sekolah_baru" name="nama_sekolah" class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="logo_sekolah_baru" class="form-label">Logo Sekolah (Opsional)</label>
                            <input class="form-control" type="file" name="logo" id="logo_sekolah_baru"
                                accept="image/*">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-save-sekolah">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalTambahProgram" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Program Keahlian Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-tambah-program">
                        <div class="mb-3">
                            <label for="nama_program_baru" class="form-label">Nama Program Keahlian</label>
                            <input type="text" id="nama_program_baru" name="nama" class="form-control" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-save-program">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Definisi Notifikasi Toast dengan Animasi
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

        document.addEventListener('DOMContentLoaded', function() {
            // Pemicu Notifikasi dari Controller
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

            // Pemicu Download Otomatis dari Controller
            @if (session('download_url'))
                // Beri jeda 1 detik agar user sempat melihat notifikasi
                setTimeout(function() {
                    window.location.href = '{{ session('download_url') }}';
                }, 1000);
            @endif

            // --- DEKLARASI VARIABEL ---
            let rowIndex = 0;
            const mainForm = document.getElementById('batch-create-form');
            const addUserBtn = document.getElementById('add-user-row');
            const tableBody = document.getElementById('user-rows');
            const rowTemplate = document.getElementById('user-row-template').innerHTML;
            const noUserPlaceholder = document.getElementById('no-user-placeholder');
            const btnReview = document.getElementById('btn-review');
            const btnFinalSubmit = document.getElementById('btn-final-submit');
            const confirmationModal = new bootstrap.Modal(document.getElementById('modalKonfirmasi'));

            // --- FUNGSI-FUNGSI UTAMA ---
            const togglePlaceholder = () => {
                noUserPlaceholder.style.display = tableBody.rows.length > 0 ? 'none' : 'block';
            };

            const addRow = (data = null) => {
                let newRowContent = rowTemplate.replace(/__INDEX__/g, rowIndex);
                const newRow = tableBody.insertRow();
                newRow.innerHTML = newRowContent;

                if (data) {
                    const inputs = newRow.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        const fieldName = input.name.match(/\[(\w+)\]$/)?.[1];
                        if (fieldName && data[fieldName]) {
                            input.value = data[fieldName];
                        }
                    });
                } else {
                    const defaultGroupId = document.getElementById('default_group_id').value;
                    const defaultSekolahId = document.getElementById('default_sekolah_id').value;
                    const defaultProgramId = document.getElementById('default_program_keahlian_id').value;
                    if (defaultGroupId) newRow.querySelector(`select[name="users[${rowIndex}][group_id]"]`)
                        .value = defaultGroupId;
                    if (defaultSekolahId) newRow.querySelector(`select[name="users[${rowIndex}][sekolah_id]"]`)
                        .value = defaultSekolahId;
                    if (defaultProgramId) newRow.querySelector(
                        `select[name="users[${rowIndex}][program_keahlian_id]"]`).value = defaultProgramId;
                }

                handleGroupChange({
                    target: newRow.querySelector('select[name*="[group_id]"]')
                });
                rowIndex++;
                togglePlaceholder();
            };

            const handleGroupChange = (event) => {
                const select = event.target;
                const selectedGroupId = select.value;
                const row = select.closest('tr');
                const sekolahSelect = row.querySelector('select[name*="[sekolah_id]"]');
                const programSelect = row.querySelector('select[name*="[program_keahlian_id]"]');
                const isSiswaOrPembimbing = ['3', '4'].includes(selectedGroupId);
                const isSiswa = selectedGroupId === '4';
                sekolahSelect.required = isSiswaOrPembimbing;
                programSelect.required = isSiswa;
                if (!isSiswaOrPembimbing) {
                    sekolahSelect.value = '';
                    programSelect.value = '';
                }
            };

            const validateForm = () => {
                let isValid = true;
                let errors = [];
                document.querySelectorAll('.user-row-error').forEach(row => row.classList.remove(
                    'user-row-error'));

                const periodeId = document.getElementById('periode_id').value;
                if (periodeId === 'new') {
                    const awal = document.getElementById('awal_periode').value;
                    const akhir = document.getElementById('akhir_periode').value;
                    if (!awal || !akhir) {
                        errors.push('Tanggal awal dan akhir periode harus diisi untuk periode baru.');
                        isValid = false;
                    }
                    if (awal && akhir && new Date(awal) > new Date(akhir)) {
                        errors.push('Tanggal akhir periode harus setelah tanggal awal.');
                        isValid = false;
                    }
                }

                const userRows = tableBody.querySelectorAll('tr');
                if (userRows.length === 0) {
                    errors.push('Minimal satu user harus ditambahkan.');
                    isValid = false;
                }

                const usernames = new Set();
                const emails = new Set();
                userRows.forEach((row, index) => {
                    let rowHasError = false;
                    const name = row.querySelector('input[name*="[name]"]');
                    const username = row.querySelector('input[name*="[username]"]');
                    const group = row.querySelector('select[name*="[group_id]"]');
                    if (!name.value.trim()) {
                        errors.push(`Baris ${index + 1}: Nama lengkap wajib.`);
                        rowHasError = true;
                    }
                    if (!username.value.trim()) {
                        errors.push(`Baris ${index + 1}: Username wajib.`);
                        rowHasError = true;
                    }
                    if (!group.value) {
                        errors.push(`Baris ${index + 1}: Role wajib.`);
                        rowHasError = true;
                    }

                    if (username.value.trim()) {
                        if (usernames.has(username.value.trim())) {
                            errors.push(
                                `Baris ${index + 1}: Username "${username.value.trim()}" duplikat.`);
                            rowHasError = true;
                        }
                        usernames.add(username.value.trim());
                    }
                    const email = row.querySelector('input[name*="[email]"]');
                    if (email.value.trim()) {
                        if (emails.has(email.value.trim())) {
                            errors.push(`Baris ${index + 1}: Email "${email.value.trim()}" duplikat.`);
                            rowHasError = true;
                        }
                        emails.add(email.value.trim());
                    }

                    const groupId = group.value;
                    const isSiswaOrPembimbing = ['3', '4'].includes(groupId);
                    const isSiswa = groupId === '4';
                    const sekolah = row.querySelector('select[name*="[sekolah_id]"]');
                    const program = row.querySelector('select[name*="[program_keahlian_id]"]');
                    if (isSiswaOrPembimbing && !sekolah.value) {
                        errors.push(`Baris ${index + 1}: Sekolah wajib untuk role ini.`);
                        rowHasError = true;
                    }
                    if (isSiswa && !program.value) {
                        errors.push(`Baris ${index + 1}: Program wajib untuk Siswa.`);
                        rowHasError = true;
                    }

                    if (rowHasError) {
                        row.classList.add('user-row-error');
                        isValid = false;
                    }
                });

                if (!isValid) {
                    alert('Terdapat kesalahan pada form:\n\n- ' + errors.join('\n- '));
                }
                return isValid;
            };

            // --- INISIALISASI HALAMAN ---
            const oldUsers = @json(old('users', []));
            if (oldUsers && oldUsers.length > 0) {
                oldUsers.forEach(userData => addRow(userData));
            } else {
                addRow();
            }

            // --- EVENT LISTENERS ---
            addUserBtn.addEventListener('click', () => addRow());

            tableBody.addEventListener('click', e => {
                if (e.target.closest('.remove-user-row')) {
                    if (tableBody.rows.length > 1) {
                        e.target.closest('tr').remove();
                        togglePlaceholder();
                    } else {
                        alert('Minimal satu baris user harus ada.');
                    }
                }
            });

            tableBody.addEventListener('change', e => {
                if (e.target.matches('select[name*="[group_id]"]')) {
                    handleGroupChange(e);
                }
            });

            btnReview.addEventListener('click', () => {
                if (!validateForm()) return;
                const userRows = tableBody.querySelectorAll('tr');
                let summary = {
                    siswa: 0,
                    pembimbing: 0,
                    total: userRows.length
                };
                let nameListHtml = '';
                userRows.forEach(row => {
                    const name = row.querySelector('input[name*="[name]"]').value ||
                    '(Nama kosong)';
                    const roleSelect = row.querySelector('select[name*="[group_id]"]');
                    const roleText = roleSelect.options[roleSelect.selectedIndex]?.text
                        ?.toLowerCase() || '';
                    if (roleText.includes('siswa')) summary.siswa++;
                    else if (roleText.includes('pembimbing')) summary.pembimbing++;
                    nameListHtml += `<div class="list-group-item">${name}</div>`;
                });
                document.getElementById('review-summary').innerHTML =
                    `<ul><li>Total User: <strong>${summary.total}</strong></li><li>Siswa: <strong>${summary.siswa}</strong></li><li>Pembimbing: <strong>${summary.pembimbing}</strong></li></ul>`;
                document.getElementById('review-name-list').innerHTML = nameListHtml;
                confirmationModal.show();
            });

            btnFinalSubmit.addEventListener('click', () => {
                if (validateForm()) {
                    mainForm.submit();
                }
            });

            document.getElementById('apply-defaults-to-all').addEventListener('click', () => {
                const defaults = {
                    group: document.getElementById('default_group_id').value,
                    sekolah: document.getElementById('default_sekolah_id').value,
                    program: document.getElementById('default_program_keahlian_id').value
                };
                if (!defaults.group && !defaults.sekolah && !defaults.program) {
                    alert('Pilih minimal satu pengaturan default terlebih dahulu');
                    return;
                }
                tableBody.querySelectorAll('tr').forEach(row => {
                    if (defaults.group) row.querySelector('select[name*="[group_id]"]').value =
                        defaults.group;
                    if (defaults.sekolah) row.querySelector('select[name*="[sekolah_id]"]').value =
                        defaults.sekolah;
                    if (defaults.program) row.querySelector('select[name*="[program_keahlian_id]"]')
                        .value = defaults.program;
                    handleGroupChange({
                        target: row.querySelector('select[name*="[group_id]"]')
                    });
                });
                alert('Pengaturan default berhasil diterapkan ke semua baris');
            });

            const periodeSelect = document.getElementById('periode_id');
            const periodeFields = document.getElementById('periode-baru-fields');
            periodeSelect.addEventListener('change', function() {
                const isNew = this.value === 'new';
                periodeFields.classList.toggle('d-none', !isNew);
                periodeFields.querySelectorAll('input').forEach(input => {
                    if (isNew) {
                        input.setAttribute('required', 'required');
                    } else {
                        input.removeAttribute('required');
                        input.value = '';
                    }
                });
            });
            if ('{{ old('periode_id') }}' === 'new') {
                periodeSelect.value = 'new';
                periodeSelect.dispatchEvent(new Event('change'));
            }

            document.getElementById('btn-save-sekolah').addEventListener('click', function() {
                const form = document.getElementById('form-tambah-sekolah'),
                    formData = new FormData(form);
                fetch("{{ route('admin.sekolah.ajax.store') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => response.json()).then(data => {
                        if (data.success) {
                            document.querySelectorAll(
                                'select[name*="[sekolah_id]"], #default_sekolah_id').forEach(
                                select => {
                                    select.appendChild(new Option(data.sekolah.nama, data.sekolah
                                        .id, false, false));
                                });
                            bootstrap.Modal.getInstance(document.getElementById('modalTambahSekolah'))
                                .hide();
                            form.reset();
                            alert('Sekolah berhasil ditambahkan!');
                        } else {
                            alert('Gagal: ' + data.message);
                        }
                    }).catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menyimpan sekolah');
                    });
            });
            document.getElementById('btn-save-program').addEventListener('click', function() {
                const form = document.getElementById('form-tambah-program'),
                    formData = new FormData(form);
                fetch("{{ route('admin.program-keahlian.ajax.store') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(Object.fromEntries(formData))
                    })
                    .then(response => response.json()).then(data => {
                        if (data.success) {
                            document.querySelectorAll(
                                'select[name*="[program_keahlian_id]"], #default_program_keahlian_id'
                                ).forEach(select => {
                                select.appendChild(new Option(data.program.nama, data.program
                                    .id, false, false));
                            });
                            bootstrap.Modal.getInstance(document.getElementById('modalTambahProgram'))
                                .hide();
                            form.reset();
                            alert('Program keahlian berhasil ditambahkan!');
                        } else {
                            alert('Gagal: ' + data.message);
                        }
                    }).catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menyimpan program keahlian');
                    });
            });
        });
    </script>
@endpush
