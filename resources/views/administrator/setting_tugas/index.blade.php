@extends('layout.main')

@section('css')
{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
{{-- SweetAlert2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

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
        font-size: 1.3rem;
        color: #495057;
        margin: 0;
        line-height: 1.5;
    }
    .empty-slot {
        color: #999;
        font-style: italic;
    }
    .form-control, .form-select {
        font-size: 0.9rem;
    }
    .team-row {
        background-color: #f8f9fa;
    }
    
    /* MODIFIKASI: Style untuk anggota list yang bisa scroll */
    .anggota-list {
        max-height: 120px;
        overflow-y: auto;
        position: relative;
    }
    
    .anggota-scrollable {
        max-height: 100px;
        overflow-y: auto;
        overflow-x: hidden;
        white-space: normal;
        word-wrap: break-word;
        line-height: 1.4;
        padding: 2px 0;
        /* Scrollbar style */
        scrollbar-width: thin;
        scrollbar-color: #ccc transparent;
    }
    
    /* Webkit scrollbar styling */
    .anggota-scrollable::-webkit-scrollbar {
        width: 6px;
    }
    
    .anggota-scrollable::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .anggota-scrollable::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 3px;
    }
    
    .anggota-scrollable::-webkit-scrollbar-thumb:hover {
        background: #999;
    }
    
    /* Hover effect untuk tooltip */
    .anggota-scrollable:hover {
        cursor: help;
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .team-display {
        background-color: #fff;
    }
    .team-display .ketua-name {
        font-weight: normal;
        color: #495057;
    }
    .team-display .anggota-name {
        padding: 2px 0;
        border-bottom: 1px solid #eee;
    }
    .team-display .anggota-name:last-child {
        border-bottom: none;
    }
    .badge-sales {
        background-color: #28a745;
    }
    .badge-teknisi {
        background-color: #17a2b8;
    }
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 20px;
        margin-bottom: 15px;
    }
    .section-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .section-header-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .btn-add-team {
        line-height: 1;
        padding: 6px 12px;
        font-size: 0.8rem;
        min-width: 36px;
        font-weight: 500;
    }
    .btn-bulk-action {
        padding: 6px 12px;
        font-size: 0.8rem;
        font-weight: 500;
        line-height: 1;
    }
    .form-section {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .team-input-row {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 10px;
    }
    .team-number {
        font-weight: bold;
        color: #495057;
        margin-bottom: 10px;
    }
    .remove-team {
        color: #dc3545;
        text-decoration: none;
        font-size: 0.8rem;
    }
    .remove-team:hover {
        color: #c82333;
    }

    /* DataTables Custom Styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 15px;
    }
    
    .dataTables_wrapper .dataTables_length select {
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        padding: 6px 12px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        margin-left: 8px;
    }
    
    .dataTables_wrapper .dataTables_info {
        padding-top: 8px;
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .dataTables_wrapper .dataTables_paginate {
        padding-top: 8px;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem;
        margin-left: 2px;
        border-radius: 0.375rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        
        .section-header-right {
            width: 100%;
            justify-content: flex-start;
            flex-wrap: wrap;
        }
    }
</style>
@endsection
@section('content')
<div class="row mb-4">
    <div class="col">
        <h4 class="fw-bold">Setting Anggota Tim</h4>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-warning btn-sm" onclick="swapDivisiConfirm()">
            🔄 Tukar Semua Anggota Team Hari Ini
        </button>
    </div>
</div>

{{-- FORM TAMBAH TIM --}}
<div class="form-section">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"> Form Tambah Tim</h5>
        <div>
            <button type="button" class="btn btn-success btn-sm" onclick="addTeamRow()">
                + Tambah Tim
            </button>
            <button type="button" class="btn btn-primary btn-sm ms-2" onclick="saveAllTeams()">
                 Simpan Semua Tim
            </button>
        </div>
    </div>
    
    <div id="team-forms-container">
        <div class="text-center text-muted py-3" id="no-forms-message">
            Klik "Tambah Tim" untuk mulai membuat tim baru
        </div>
    </div>
</div>

{{-- TABEL DAFTAR TIM dengan DataTables --}}
<div class="section-header">
    <div class="section-header-left">
        <div class="section-title"> DAFTAR TIM HARI INI</div>
        <small class="text-muted">Total: <span id="total-teams">{{ $timSales->count() + $timTeknisi->count() }}</span> tim</small>
    </div>
    <div class="section-header-right">
        <button type="button" class="btn btn-info btn-bulk-action" onclick="editAllTeams()">
             Edit Semua Tim
        </button>
        <button type="button" class="btn btn-danger btn-bulk-action" onclick="deleteAllTeamsConfirm()">
             Hapus Semua Tim
        </button>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-team" id="teams-table">
        <thead class="table-light">
            <tr>
                <th style="width: 25%">Ketua Tim</th>
                <th style="width: 15%">Jenis Tim</th>
                <th style="width: 45%">Anggota Tim</th>
                <th style="width: 15%">Created At</th>
            </tr>
        </thead>
        <tbody>
            @php
                $allTeams = collect();
                $allTeams = $allTeams->merge($timSales->map(function($tim) {
                    $tim->jenis = 'sales';
                    return $tim;
                }));
                $allTeams = $allTeams->merge($timTeknisi->map(function($tim) {
                    $tim->jenis = 'teknisi';
                    return $tim;
                }));
                $allTeams = $allTeams->sortBy('created_at');
            @endphp
            
            @forelse($allTeams as $index => $tim)
            <tr class="team-display" data-team-id="{{ $tim->id }}">
                <td>
                    <div class="ketua-name">
                        {{ $loop->iteration }}. {{ $tim->ketua->name ?? 'Ketua tidak ditemukan' }}
                    </div>
                </td>
                <td>
                    <span class="badge {{ $tim->jenis == 'sales' ? 'badge-sales' : 'badge-teknisi' }} text-white px-2 py-1">
                        {{ strtoupper($tim->jenis) }}
                    </span>
                </td>
                <td>
                    <div class="anggota-list">
                        @php
                            $anggotaNames = $tim->anggota->pluck('name')->toArray();
                            $anggotaString = implode(', ', $anggotaNames);
                        @endphp
                        
                        @if(count($anggotaNames) > 0)
                            <div class="anggota-scrollable" title="{{ $anggotaString }}">
                                {{ $anggotaString }}
                            </div>
                        @else
                            <div class="text-muted">Tidak ada anggota</div>
                        @endif
                    </div>
                </td>
                <td>
                    <small class="text-muted">
                        {{ $tim->created_at->format('d/m/Y H:i') }}
                    </small>
                </td>
            </tr>
            @empty
            <tr id="empty-row">
                <td colspan="4" class="text-center empty-slot">
                    Belum ada tim hari ini. Gunakan form di atas untuk membuat tim baru.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

@section('js')
{{-- DataTables JS --}}
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
{{-- SweetAlert2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Data untuk JavaScript
const availableAdmins = @json($availableAdmins);
const availableSiswa = @json($availableSiswa);
let teamCounter = 0;
let teamsDataTable;

// Initialize DataTable
$(document).ready(function() {
    // Initialize DataTable only if there are rows with data
    const hasData = $('#teams-table tbody tr[data-team-id]').length > 0;
    
    if (hasData) {
        initializeDataTable();
    }
    
    // Check if there are any teams today, if not, auto add first form
    if (!hasData) {
        addTeamRow();
    }
    
    // Check for session messages and show SweetAlert2
    checkSessionMessages();
    
    // Pastikan CSRF token tersedia
    if (!document.querySelector('meta[name="csrf-token"]')) {
        console.warn('CSRF token meta tag tidak ditemukan. Pastikan layout memiliki @csrf atau meta tag csrf-token');
    }
});

// UPDATED: Function to check session messages and display SweetAlert2
function checkSessionMessages() {
    @if(session('success'))
        showSuccessAlert('{{ session('success') }}');
    @endif
    
    @if(session('error'))
        showErrorAlert('{{ session('error') }}');
    @endif
}

// SweetAlert2 Helper Functions
function showSuccessAlert(message, callback = null) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#28a745',
        timer: 4000,
        timerProgressBar: true,
        showConfirmButton: true
    }).then((result) => {
        if (callback && typeof callback === 'function') {
            callback();
        }
    });
}

function showErrorAlert(message) {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#dc3545'
    });
}

function showWarningAlert(message) {
    Swal.fire({
        icon: 'warning',
        title: 'Peringatan',
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#ffc107'
    });
}

function showConfirmAlert(title, message, confirmText, callback) {
    Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed && callback) {
            callback();
        }
    });
}

function showLoadingAlert(message = 'Memproses...') {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function initializeDataTable() {
    teamsDataTable = $('#teams-table').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            },
            emptyTable: "Tidak ada data yang tersedia dalam tabel",
            zeroRecords: "Tidak ditemukan data yang sesuai"
        },
        order: [[3, 'desc']], // Sort by Created At descending
        columnDefs: [
            {
                targets: [2], // Anggota Tim column
                orderable: false
            }
        ],
        drawCallback: function(settings) {
            // Update total teams count
            const info = this.api().page.info();
            $('#total-teams').text(info.recordsTotal);
        }
    });
}

// Function to refresh/reinitialize DataTable
function refreshDataTable() {
    if (teamsDataTable) {
        teamsDataTable.destroy();
    }
    
    // Check if there's data
    const hasData = $('#teams-table tbody tr[data-team-id]').length > 0;
    
    if (hasData) {
        initializeDataTable();
    } else {
        // Show empty message
        $('#teams-table tbody').html(`
            <tr id="empty-row">
                <td colspan="4" class="text-center empty-slot">
                    Belum ada tim hari ini. Gunakan form di atas untuk membuat tim baru.
                </td>
            </tr>
        `);
        $('#total-teams').text('0');
    }
}

// UPDATED: Fungsi untuk menambah row tim baru dengan SweetAlert2
function addTeamRow() {
    teamCounter++;
    
    // Hide no-forms message
    const noFormsMessage = document.getElementById('no-forms-message');
    if (noFormsMessage) {
        noFormsMessage.style.display = 'none';
    }
    
    // Filter admin yang tersedia (belum terdaftar)
    const adminTersedia = availableAdmins.filter(admin => !admin.sudah_terdaftar);
    if (adminTersedia.length === 0) {
        showWarningAlert('Tidak ada admin yang tersedia untuk menjadi ketua tim!');
        return;
    }
    
    // Filter siswa yang tersedia (belum terdaftar)  
    const siswaTersedia = availableSiswa.filter(siswa => !siswa.sudah_terdaftar);
    if (siswaTersedia.length === 0) {
        showWarningAlert('Tidak ada siswa yang tersedia untuk menjadi anggota tim!');
        return;
    }
    
    // Buat dropdown options
    let ketuaOptions = '<option value="">-- Pilih Ketua Tim --</option>';
    adminTersedia.forEach(admin => {
        ketuaOptions += `<option value="${admin.id}">${admin.name}</option>`;
    });
    
    let anggotaOptions = '';
    siswaTersedia.forEach(siswa => {
        anggotaOptions += `<option value="${siswa.id}">${siswa.name}</option>`;
    });
    
    const teamFormHtml = `
        <div class="team-input-row" id="team-form-${teamCounter}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="team-number"></div>
                <a href="#" class="remove-team" onclick="removeTeamRow(${teamCounter})">❌ Hapus</a>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Ketua Tim:</label>
                    <select class="form-select ketua-select" name="teams[${teamCounter}][ketua_id]" required>
                        ${ketuaOptions}
                    </select>
                    <small class="text-muted">${adminTersedia.length} admin tersedia</small>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Jenis Tim:</label>
                    <select class="form-select jenis-select" name="teams[${teamCounter}][divisi]" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="sales">SALES</option>
                        <option value="teknisi">TEKNISI</option>
                    </select>
                </div>
                
                <div class="col-md-7">
                    <label class="form-label">Anggota Tim:</label>
                    <select class="form-select anggota-select" name="teams[${teamCounter}][anggota][]" multiple size="4" required>
                        ${anggotaOptions}
                    </select>
                    <small class="text-muted">Ctrl+Click untuk pilih beberapa (${siswaTersedia.length} siswa tersedia)</small>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('team-forms-container').insertAdjacentHTML('beforeend', teamFormHtml);
}

// UPDATED: Fungsi untuk menghapus row tim dengan SweetAlert2
function removeTeamRow(teamId) {
    const teamForm = document.getElementById(`team-form-${teamId}`);
    if (teamForm) {
        teamForm.remove();
        
        // Kalau sudah tidak ada form lagi, tampilkan pesan default
        const container = document.getElementById('team-forms-container');
        if (container.children.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-3" id="no-forms-message">Klik "Tambah Tim" untuk mulai membuat tim baru</div>';
        }

    }
}


// UPDATED: Fungsi untuk simpan semua tim sekaligus dengan SweetAlert2
function saveAllTeams() {
    const teamForms = document.querySelectorAll('.team-input-row');
    
    if (teamForms.length === 0) {
        showWarningAlert('Tidak ada tim untuk disimpan! Tambah tim terlebih dahulu.');
        return;
    }
    
    // Validasi semua form
    let isValid = true;
    const teams = [];
    
    teamForms.forEach((form, index) => {
        const ketuaSelect = form.querySelector('.ketua-select');
        const jenisSelect = form.querySelector('.jenis-select');
        const anggotaSelect = form.querySelector('.anggota-select');
        
        if (!ketuaSelect.value) {
            showErrorAlert(`Tim ${index + 1}: Pilih ketua tim terlebih dahulu!`);
            ketuaSelect.focus();
            isValid = false;
            return;
        }
        
        if (!jenisSelect.value) {
            showErrorAlert(`Tim ${index + 1}: Pilih jenis tim terlebih dahulu!`);
            jenisSelect.focus();
            isValid = false;
            return;
        }
        
        const selectedAnggota = Array.from(anggotaSelect.selectedOptions).map(option => option.value);
        if (selectedAnggota.length === 0) {
            showErrorAlert(`Tim ${index + 1}: Pilih minimal satu anggota tim!`);
            anggotaSelect.focus();
            isValid = false;
            return;
        }
        
        teams.push({
            ketua_id: ketuaSelect.value,
            divisi: jenisSelect.value,
            anggota: selectedAnggota
        });
    });
    
    if (!isValid) return;
    
    // Validasi duplikasi ketua
    const ketuaIds = teams.map(team => team.ketua_id);
    const uniqueKetuaIds = [...new Set(ketuaIds)];
    if (ketuaIds.length !== uniqueKetuaIds.length) {
        showErrorAlert('Ada ketua tim yang sama! Setiap ketua hanya boleh memimpin satu tim.');
        return;
    }
    
    // Validasi duplikasi anggota
    const allAnggota = teams.flatMap(team => team.anggota);
    const uniqueAnggota = [...new Set(allAnggota)];
    if (allAnggota.length !== uniqueAnggota.length) {
        showErrorAlert('Ada anggota yang terdaftar di beberapa tim! Setiap siswa hanya boleh ikut satu tim.');
        return;
    }
    
    // Get CSRF token
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfTokenMeta) {
        showErrorAlert('CSRF token tidak ditemukan!');
        return;
    }
    
    // Show confirmation before saving
    Swal.fire({
        icon: 'question',
        title: 'Simpan Semua Tim?',
        html: `
            <p>Yakin ingin menyimpan <strong>${teams.length} tim</strong> sekaligus?</p>
            <ul class="text-start mt-3">
                <li><strong>${teams.filter(t => t.divisi === 'sales').length}</strong> Tim Sales</li>
                <li><strong>${teams.filter(t => t.divisi === 'teknisi').length}</strong> Tim Teknisi</li>
            </ul>
        `,
        showCancelButton: true,
        confirmButtonText: '💾 Ya, Simpan!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#007bff',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            showLoadingAlert('Menyimpan tim...');
            
            // Disable semua tombol
            const saveButton = document.querySelector('button[onclick="saveAllTeams()"]');
            const addButton = document.querySelector('button[onclick="addTeamRow()"]');
            
            saveButton.disabled = true;
            addButton.disabled = true;
            
            // Kirim request
            fetch('{{ route("admin.setting_tugas.storeBulk") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfTokenMeta.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ teams: teams })
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                Swal.close();
                if (data.success) {
                    showSuccessAlert(
                        data.message || 'Semua tim berhasil disimpan!',
                        function() {
                            window.location.reload();
                        }
                    );
                } else {
                    showErrorAlert(data.message || 'Terjadi kesalahan saat menyimpan tim');
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                showErrorAlert('Terjadi kesalahan: ' + error.message);
            })
            .finally(() => {
                saveButton.disabled = false;
                addButton.disabled = false;
            });
        }
    });
}

// UPDATED: Fungsi untuk edit semua tim dengan SweetAlert2
function editAllTeams() {
    const teamRows = document.querySelectorAll('#teams-table tbody tr[data-team-id]');
    
    if (teamRows.length === 0) {
        showWarningAlert('Tidak ada tim untuk diedit!');
        return;
    }
    
    const editButton = document.querySelector('button[onclick="editAllTeams()"]');
    const isEditMode = editButton.textContent.includes('Edit');
    
    if (isEditMode) {
        // Konfirmasi sebelum masuk edit mode
        Swal.fire({
            icon: 'question',
            title: 'Edit Semua Tim?',
            html: `
                <p>Yakin ingin mengaktifkan mode edit untuk semua <strong>${teamRows.length} tim</strong>?</p>
                <p class="text-info mt-2">Anda dapat mengubah ketua dan anggota tim.</p>
            `,
            showCancelButton: true,
            confirmButtonText: ' Ya, Edit!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Destroy DataTable before editing
                if (teamsDataTable) {
                    teamsDataTable.destroy();
                    teamsDataTable = null;
                }
                
                enableEditMode();
                editButton.innerHTML = ' Simpan Edit Semua';
                editButton.setAttribute('onclick', 'saveAllEdits()');
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Mode Edit Aktif!',
                    text: 'Silakan ubah data tim yang diperlukan.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }
        });
    } else {
        saveAllEdits();
    }
}

function enableEditMode() {
    const teamRows = document.querySelectorAll('#teams-table tbody tr[data-team-id]');
    
    teamRows.forEach((row, index) => {
        const teamId = row.getAttribute('data-team-id');
        const ketuaCell = row.cells[0];
        const jenisCell = row.cells[1];
        const anggotaCell = row.cells[2];
        
        // Dapatkan data tim saat ini
        const currentKetua = ketuaCell.textContent.trim().replace(/^\d+\.\s*/, '');
        const currentJenis = jenisCell.textContent.trim().toLowerCase();
        const currentAnggotaString = anggotaCell.querySelector('.anggota-scrollable')?.textContent || '';
        const currentAnggota = currentAnggotaString.split(', ').filter(name => name.trim() !== '');
        
        // Buat form edit untuk ketua
        const adminOptions = availableAdmins.map(admin => 
            `<option value="${admin.id}" ${admin.name === currentKetua ? 'selected' : ''}>${admin.name}</option>`
        ).join('');
        
        ketuaCell.innerHTML = `
            <select class="form-select form-select-sm edit-ketua" data-team-id="${teamId}">
                <option value="">-- Pilih Ketua --</option>
                ${adminOptions}
            </select>
        `;
        
        // Buat form edit untuk jenis tim
        jenisCell.innerHTML = `
            <select class="form-select form-select-sm edit-jenis" data-team-id="${teamId}">
                <option value="sales" ${currentJenis === 'sales' ? 'selected' : ''}>SALES</option>
                <option value="teknisi" ${currentJenis === 'teknisi' ? 'selected' : ''}>TEKNISI</option>
            </select>
        `;
        
        // Buat form edit untuk anggota
        const siswaOptions = availableSiswa.map(siswa => {
            const isSelected = currentAnggota.includes(siswa.name);
            return `<option value="${siswa.id}" ${isSelected ? 'selected' : ''}>${siswa.name}</option>`;
        }).join('');
        
        anggotaCell.innerHTML = `
            <select class="form-select form-select-sm edit-anggota" data-team-id="${teamId}" multiple size="4">
                ${siswaOptions}
            </select>
            <small class="text-muted">Ctrl+Click untuk pilih beberapa</small>
        `;
    });
    
    // Disable tombol lain
    const deleteAllButton = document.querySelector('button[onclick="deleteAllTeamsConfirm()"]');
    const addTeamButton = document.querySelector('button[onclick="addTeamRow()"]');
    const saveTeamsButton = document.querySelector('button[onclick="saveAllTeams()"]');
    
    if (deleteAllButton) deleteAllButton.disabled = true;
    if (addTeamButton) addTeamButton.disabled = true;
    if (saveTeamsButton) saveTeamsButton.disabled = true;
}

// UPDATED: Fungsi untuk menyimpan semua edit dengan SweetAlert2
function saveAllEdits() {
    const teamRows = document.querySelectorAll('#teams-table tbody tr[data-team-id]');
    const updates = [];
    
    // Validasi dan kumpulkan data
    let isValid = true;
    teamRows.forEach((row, index) => {
        const teamId = row.getAttribute('data-team-id');
        const ketuaSelect = row.querySelector('.edit-ketua');
        const jenisSelect = row.querySelector('.edit-jenis');
        const anggotaSelect = row.querySelector('.edit-anggota');
        
        if (!ketuaSelect.value) {
            showErrorAlert(`Tim ${index + 1}: Pilih ketua tim!`);
            isValid = false;
            return;
        }
        
        const selectedAnggota = Array.from(anggotaSelect.selectedOptions).map(option => option.value);
        if (selectedAnggota.length === 0) {
            showErrorAlert(`Tim ${index + 1}: Pilih minimal satu anggota!`);
            isValid = false;
            return;
        }
        
        updates.push({
            team_id: teamId,
            ketua_id: ketuaSelect.value,
            divisi: jenisSelect.value,
            anggota: selectedAnggota
        });
    });
    
    if (!isValid) return;
    
    // Validasi duplikasi ketua
    const ketuaIds = updates.map(update => update.ketua_id);
    if (ketuaIds.length !== [...new Set(ketuaIds)].length) {
        showErrorAlert('Ada ketua yang sama! Setiap ketua hanya boleh memimpin satu tim.');
        return;
    }
    
    // Validasi duplikasi anggota
    const allAnggota = updates.flatMap(update => update.anggota);
    if (allAnggota.length !== [...new Set(allAnggota)].length) {
        showErrorAlert('Ada anggota yang sama di beberapa tim! Setiap siswa hanya boleh ikut satu tim.');
        return;
    }
    
    // Konfirmasi sebelum menyimpan
    Swal.fire({
        icon: 'question',
        title: 'Simpan Perubahan?',
        html: `
            <p>Yakin ingin menyimpan perubahan pada <strong>${updates.length} tim</strong>?</p>
            <p class="text-warning mt-2"> Data lama akan diganti dengan data baru!</p>
        `,
        showCancelButton: true,
        confirmButtonText: ' Ya, Simpan!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Simpan perubahan
            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfTokenMeta) {
                showErrorAlert('CSRF token tidak ditemukan!');
                return;
            }
            
            const editButton = document.querySelector('button[onclick="saveAllEdits()"]');
            editButton.disabled = true;
            
            showLoadingAlert('Menyimpan perubahan...');
            
            // Kirim update bulk request
            fetch('{{ route("admin.setting_tugas.updateBulk") }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfTokenMeta.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    updates: updates
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                Swal.close();
                if (data.success) {
                    showSuccessAlert(
                        data.message || 'Berhasil memperbarui semua tim!',
                        function() {
                            location.reload();
                        }
                    );
                } else {
                    showErrorAlert(data.message || 'Terjadi kesalahan saat memperbarui tim');
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Save All Edits Error:', error);
                showErrorAlert('Terjadi kesalahan saat menyimpan perubahan: ' + error.message);
                location.reload();
            });
        }
    });
}

// UPDATED: Fungsi untuk konfirmasi hapus semua tim dengan SweetAlert2
function deleteAllTeamsConfirm() {
    const teamRows = document.querySelectorAll('#teams-table tbody tr[data-team-id]');
    
    if (teamRows.length === 0) {
        showWarningAlert('Tidak ada tim untuk dihapus!');
        return;
    }
    
    Swal.fire({
        icon: 'warning',
        title: 'Hapus Semua Tim?',
        html: `
            <div class="text-center">
                <p>Yakin ingin menghapus semua <strong>${teamRows.length} tim</strong> hari ini?</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: ' Ya, Hapus Semua!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            deleteAllTeams();
        }
    });
}

// UPDATED: Fungsi untuk hapus semua tim dengan SweetAlert2
function deleteAllTeams() {
    showLoadingAlert('Menghapus semua tim...');
    
    // Get CSRF token
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfTokenMeta) {
        Swal.close();
        showErrorAlert('CSRF token tidak ditemukan!');
        return;
    }
    
    const deleteAllButton = document.querySelector('button[onclick="deleteAllTeamsConfirm()"]');
    deleteAllButton.disabled = true;
    
    // Kirim request POST untuk delete semua tim
    fetch('{{ route("admin.setting_tugas.destroyAll") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfTokenMeta.getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            action: 'destroy_all'
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        Swal.close();
        if (data.success) {
            showSuccessAlert(
                data.message || 'Berhasil menghapus semua tim!',
                function() {
                    window.location.reload();
                }
            );
        } else {
            showErrorAlert(data.message || 'Terjadi kesalahan saat menghapus tim');
        }
    })
    .catch(error => {
        Swal.close();
        console.error('Delete All Teams Error:', error);
        showErrorAlert('Terjadi kesalahan saat menghapus tim: ' + error.message);
    })
    .finally(() => {
        deleteAllButton.disabled = false;
    });
}

// UPDATED: Fungsi untuk konfirmasi tukar divisi dengan SweetAlert2
function swapDivisiConfirm() {
    Swal.fire({
        icon: 'question',
        title: 'Tukar Anggota Tim?',
        html: `
            <div class="text-center">
                <p>Yakin ingin menukar semua anggota tim hari ini?</p>
                <div class="row mt-3">
                    <div class="col-6">
                        <div class="card bg-success text-white">
                            <div class="card-body py-2">
                                <small>Tim SALES</small><br>
                                <i class="fas fa-arrow-right"></i> TEKNISI
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-info text-white">
                            <div class="card-body py-2">
                                <small>Tim TEKNISI</small><br>
                                <i class="fas fa-arrow-right"></i> SALES
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-warning mt-3 mb-0">
                    <small><strong>Ketua tim tetap tidak berubah</strong></small>
                </p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: ' Ya, Tukar!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            swapDivisi();
        }
    });
}

// UPDATED: Fungsi untuk tukar divisi dengan SweetAlert2
function swapDivisi() {
    showLoadingAlert('Menukar anggota tim...');
    
    // Simulate form submission to swapDivisi route
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.setting_tugas.swapDivisi") }}';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    form.appendChild(csrfInput);
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection