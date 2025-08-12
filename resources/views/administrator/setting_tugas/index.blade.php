@extends('layout.main')

@section('css')
<style>
    .table-team {
        background: #fff;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #ddd;
    }
    .table-team th,
    .table-team td {
        border: 1px solid #ddd !important;
        padding: 12px;
        vertical-align: top;
    }
    .section-title {
        font-weight: bold;
        margin-top: 20px;
        margin-bottom: 15px;
        font-size: 1.1rem;
        color: #495057;
    }
    .empty-slot {
        color: #999;
        font-style: italic;
    }
    .select-ketua, .select-anggota {
        width: 100%;
        margin-bottom: 10px;
    }
    .btn-action {
        margin: 2px;
        padding: 4px 8px;
        font-size: 0.8rem;
    }
    .team-row {
        background-color: #f8f9fa;
    }
    .anggota-list {
        max-height: 120px;
        overflow-y: auto;
    }
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }
    .team-display {
        background-color: #fff;
    }
    .team-display .ketua-name {
        font-weight: bold;
        color: #495057;
    }
    .team-display .anggota-name {
        padding: 2px 0;
        border-bottom: 1px solid #eee;
    }
    .team-display .anggota-name:last-child {
        border-bottom: none;
    }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col">
        <h4 class="fw-bold">Setting Anggota Tim</h4>
    </div>
    <div class="col-auto">
        <form method="POST" action="{{ route('admin.setting_tugas.swapDivisi') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm" 
                    onclick="return confirm('Yakin ingin menukar semua divisi tim hari ini?')">
                🔄 Tukar Semua Anggota Team Hari Ini
            </button>
        </form>
        <button type="button" class="btn btn-success btn-sm ms-2" onclick="addNewTeam('sales')">
            + Tambah Tim Sales
        </button>
        <button type="button" class="btn btn-info btn-sm ms-2" onclick="addNewTeam('teknisi')">
            + Tambah Tim Teknisi
        </button>
    </div>
</div>

{{-- Alert Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- TIM SALES --}}
<div class="section-title">TIM SALES</div>
<table class="table table-bordered table-team" id="sales-table">
    <thead class="table-light">
        <tr>
            <th style="width: 35%">Ketua Tim (Admin)</th>
            <th style="width: 45%">Anggota Tim (Siswa)</th>
            <th style="width: 20%">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($timSales as $index => $tim)
        <tr class="team-display" data-team-id="{{ $tim->id }}">
            <td>
                <div class="ketua-name">
                    {{ $loop->iteration }}. {{ $tim->ketua->name ?? 'Ketua tidak ditemukan' }}
                </div>
            </td>
            <td>
                <div class="anggota-list">
                    @forelse($tim->anggota as $anggota)
                        <div class="anggota-name">
                            {{ $loop->iteration }}. {{ $anggota->name }}
                        </div>
                    @empty
                        <div class="text-muted">Tidak ada anggota</div>
                    @endforelse
                </div>
            </td>
            <td>
                <button type="button" class="btn btn-warning btn-action" onclick="editTeam({{ $tim->id }}, 'sales')">
                    Edit
                </button>
                <button type="button" class="btn btn-danger btn-action" onclick="deleteTeam({{ $tim->id }})">
                    Hapus
                </button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3" class="text-center empty-slot">
                Belum ada tim sales. Klik "Tambah Tim Sales" untuk membuat tim baru.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- TIM TEKNISI --}}
<div class="section-title">TIM TEKNISI</div>
<table class="table table-bordered table-team" id="teknisi-table">
    <thead class="table-light">
        <tr>
            <th style="width: 35%">Ketua Tim (Admin)</th>
            <th style="width: 45%">Anggota Tim (Siswa)</th>
            <th style="width: 20%">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($timTeknisi as $index => $tim)
        <tr class="team-display" data-team-id="{{ $tim->id }}">
            <td>
                <div class="ketua-name">
                    {{ $loop->iteration }}. {{ $tim->ketua->name ?? 'Ketua tidak ditemukan' }}
                </div>
            </td>
            <td>
                <div class="anggota-list">
                    @forelse($tim->anggota as $anggota)
                        <div class="anggota-name">
                            {{ $loop->iteration }}. {{ $anggota->name }}
                        </div>
                    @empty
                        <div class="text-muted">Tidak ada anggota</div>
                    @endforelse
                </div>
            </td>
            <td>
                <button type="button" class="btn btn-warning btn-action" onclick="editTeam({{ $tim->id }}, 'teknisi')">
                    Edit
                </button>
                <button type="button" class="btn btn-danger btn-action" onclick="deleteTeam({{ $tim->id }})">
                    Hapus
                </button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3" class="text-center empty-slot">
                Belum ada tim teknisi. Klik "Tambah Tim Teknisi" untuk membuat tim baru.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@endsection

@section('js')
<script>
// Data untuk JavaScript
const availableAdmins = @json($availableAdmins);
const availableSiswa = @json($availableSiswa);
const currentTimSales = @json($timSales);
const currentTimTeknisi = @json($timTeknisi);
let newTeamCounter = 0;

// Fungsi untuk edit tim yang sudah ada
function editTeam(teamId, divisi) {
    const row = document.querySelector(`tr[data-team-id="${teamId}"]`);
    if (!row) return;

    // Cari data tim
    const timData = divisi === 'sales' 
        ? currentTimSales.find(tim => tim.id == teamId)
        : currentTimTeknisi.find(tim => tim.id == teamId);

    if (!timData) {
        alert('Data tim tidak ditemukan!');
        return;
    }

    // Buat dropdown ketua - untuk edit, tampilkan semua admin (kecuali yang sudah terdaftar di tim lain)
    let ketuaOptions = '<option value="">-- Pilih Ketua Tim --</option>';
    availableAdmins.forEach(admin => {
        // Tampilkan jika belum terdaftar atau memang ketua tim ini
        if (!admin.sudah_terdaftar || admin.id == timData.ketua_id) {
            const selected = admin.id == timData.ketua_id ? 'selected' : '';
            ketuaOptions += `<option value="${admin.id}" ${selected}>${admin.name}</option>`;
        }
    });

    // Buat dropdown anggota
    let anggotaOptions = '';
    const selectedAnggotaIds = timData.anggota.map(a => a.id);
    availableSiswa.forEach(siswa => {
        // Tampilkan jika belum terdaftar atau memang anggota tim ini
        if (!siswa.sudah_terdaftar || selectedAnggotaIds.includes(siswa.id)) {
            const selected = selectedAnggotaIds && selectedAnggotaIds.includes(siswa.id) ? 'selected' : '';
            anggotaOptions += `<option value="${siswa.id}" ${selected}>${siswa.name}</option>`;
        }
    });

    // Replace row dengan form edit
    row.innerHTML = `
        <td>
            <select class="form-select select-ketua" data-team-id="${teamId}" data-divisi="${divisi}">
                ${ketuaOptions}
            </select>
        </td>
        <td>
            <select class="form-select select-anggota" multiple data-team-id="${teamId}" data-divisi="${divisi}" size="4">
                ${anggotaOptions}
            </select>
            <small class="text-muted">Ctrl+Click untuk pilih beberapa</small>
        </td>
        <td>
            <button type="button" class="btn btn-primary btn-action" onclick="saveTeam(${teamId}, '${divisi}')">
                Simpan
            </button>
            <button type="button" class="btn btn-secondary btn-action" onclick="cancelEdit(${teamId})">
                Batal
            </button>
        </td>
    `;
    
    row.className = 'team-row';
}

// Fungsi untuk membatalkan edit
function cancelEdit(teamId) {
    // Reload halaman untuk kembali ke tampilan normal
    location.reload();
}

// Fungsi untuk menambah tim baru
function addNewTeam(divisi) {
    newTeamCounter--;
    const tableId = divisi + '-table';
    const tbody = document.querySelector('#' + tableId + ' tbody');
    
    // Hapus row empty jika ada
    const emptyRow = tbody.querySelector('td[colspan="3"]');
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }

    // Buat dropdown ketua (admin) - hanya admin yang belum terdaftar
    let ketuaOptions = '<option value="">-- Pilih Ketua Tim --</option>';
    availableAdmins.forEach(admin => {
        if (!admin.sudah_terdaftar) { // hanya admin yang belum terdaftar
            ketuaOptions += `<option value="${admin.id}">${admin.name}</option>`;
        }
    });

    // Cek apakah masih ada admin yang tersedia
    const adminTersedia = availableAdmins.filter(admin => !admin.sudah_terdaftar);
    if (adminTersedia.length === 0) {
        alert('Tidak ada admin yang tersedia untuk menjadi ketua tim. Semua admin sudah menjadi ketua tim lain hari ini.');
        return;
    }

    // Buat dropdown anggota (siswa) - hanya siswa yang belum terdaftar
    let anggotaOptions = '';
    availableSiswa.forEach(siswa => {
        if (!siswa.sudah_terdaftar) { // hanya siswa yang belum terdaftar
            anggotaOptions += `<option value="${siswa.id}">${siswa.name}</option>`;
        }
    });

    // Cek apakah masih ada siswa yang tersedia
    const siswaTersedia = availableSiswa.filter(siswa => !siswa.sudah_terdaftar);
    if (siswaTersedia.length === 0) {
        alert('Tidak ada siswa yang tersedia untuk menjadi anggota tim. Semua siswa sudah terdaftar di tim lain hari ini.');
        return;
    }

    const newRow = `
        <tr class="team-row" data-team-id="new_${newTeamCounter}">
            <td>
                <select class="form-select select-ketua" data-team-id="new_${newTeamCounter}" data-divisi="${divisi}">
                    ${ketuaOptions}
                </select>
                <small class="text-muted">${adminTersedia.length} admin tersedia</small>
            </td>
            <td>
                <select class="form-select select-anggota" multiple data-team-id="new_${newTeamCounter}" data-divisi="${divisi}" size="4">
                    ${anggotaOptions}
                </select>
                <small class="text-muted">Ctrl+Click untuk pilih beberapa (${siswaTersedia.length} siswa tersedia)</small>
            </td>
            <td>
                <button type="button" class="btn btn-primary btn-action" onclick="saveTeam('new_${newTeamCounter}', '${divisi}')">
                    Simpan
                </button>
                <button type="button" class="btn btn-secondary btn-action" onclick="cancelNewTeam('new_${newTeamCounter}')">
                    Batal
                </button>
            </td>
        </tr>
    `;

    tbody.insertAdjacentHTML('beforeend', newRow);
}

// Fungsi untuk menyimpan tim
function saveTeam(teamId, divisi) {
    const row = document.querySelector(`tr[data-team-id="${teamId}"]`);
    if (!row) {
        alert('Row tidak ditemukan!');
        return;
    }

    const ketuaSelect = row.querySelector('.select-ketua');
    const anggotaSelect = row.querySelector('.select-anggota');
    
    if (!ketuaSelect || !anggotaSelect) {
        alert('Element select tidak ditemukan!');
        return;
    }

    const ketuaId = ketuaSelect.value;
    const anggotaIds = Array.from(anggotaSelect.selectedOptions).map(option => option.value);

    // Validasi input
    if (!ketuaId) {
        alert('Pilih ketua tim terlebih dahulu!');
        ketuaSelect.focus();
        return;
    }

    if (anggotaIds.length === 0) {
        alert('Pilih minimal satu anggota tim!');
        anggotaSelect.focus();
        return;
    }

    // Cek CSRF token
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfTokenMeta) {
        alert('CSRF token tidak ditemukan! Pastikan <meta name="csrf-token"> ada di <head> layout');
        return;
    }

    // Disable button dan loading state
    const saveBtn = row.querySelector('.btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Menyimpan...';
    row.classList.add('loading');

    // Persiapkan data
    const requestData = {
        _token: csrfTokenMeta.getAttribute('content'),
        ketua_id: ketuaId,
        divisi: divisi,
        anggota: anggotaIds
    };
    
    // Jika update existing team
    if (!teamId.toString().startsWith('new_')) {
        requestData.team_id = teamId;
    }

    // Kirim request menggunakan URLSearchParams untuk form-data
    const formData = new URLSearchParams();
    Object.keys(requestData).forEach(key => {
        if (key === 'anggota') {
            requestData[key].forEach(value => formData.append('anggota[]', value));
        } else {
            formData.append(key, requestData[key]);
        }
    });

    fetch('{{ route("admin.setting_tugas.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }
        
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                throw new Error('Response bukan JSON: ' + text);
            });
        }
    })
    .then(data => {
        if (data && data.success) {
            alert(data.message || 'Tim berhasil disimpan!');
            // Reload halaman untuk menampilkan data yang sudah tersimpan
            window.location.reload();
        } else {
            let errorMessage = (data && data.message) ? data.message : 'Terjadi kesalahan tidak diketahui';
            if (data && data.errors) {
                const errorMessages = Object.values(data.errors).flat();
                errorMessage += ':\n' + errorMessages.join('\n');
            }
            alert(errorMessage);
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        alert('Terjadi kesalahan saat menyimpan tim: ' + error.message);
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
        row.classList.remove('loading');
    });
}

// Fungsi untuk menghapus tim
function deleteTeam(teamId) {
    if (!confirm('Yakin ingin menghapus tim ini?')) {
        return;
    }

    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfTokenMeta) {
        alert('CSRF token tidak ditemukan!');
        return;
    }

    fetch(`{{ url('/admin/setting-tugas') }}/${teamId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfTokenMeta.getAttribute('content'),
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message || 'Tim berhasil dihapus!');
            location.reload();
        } else {
            alert(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        console.error('Delete Error:', error);
        alert('Terjadi kesalahan saat menghapus tim: ' + error.message);
    });
}

// Fungsi untuk membatalkan tim baru
function cancelNewTeam(teamId) {
    const row = document.querySelector(`tr[data-team-id="${teamId}"]`);
    if (row) {
        row.remove();
        
        // Cek apakah tabel kosong, jika ya tampilkan pesan empty
        const divisi = teamId.includes('sales') ? 'sales' : 'teknisi';
        const tbody = document.querySelector(`#${divisi}-table tbody`);
        if (tbody && tbody.children.length === 0) {
            const emptyMessage = divisi === 'sales' 
                ? 'Belum ada tim sales. Klik "Tambah Tim Sales" untuk membuat tim baru.'
                : 'Belum ada tim teknisi. Klik "Tambah Tim Teknisi" untuk membuat tim baru.';
                
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center empty-slot">${emptyMessage}</td>
                </tr>
            `;
        }
    }
}
</script>
@endsection