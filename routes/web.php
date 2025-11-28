<?php

use App\Http\Controllers\ProgramKeahlianController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\ColectDataController;
use App\Http\Controllers\TaskBreakdownController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\PresensiSettingController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TugasHarianController;
use App\Http\Controllers\NotificationController;
use App\Models\Presensi;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\DivisiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JenisKegiatanController;
use App\Http\Controllers\TimController;
use App\Http\Controllers\PeriodePklController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get("/", function () {
    return redirect()->route("login");
});

// ===== PUBLIC ROUTES =====
Route::middleware('guest')->group(function () {
    // Authentication Routes
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/authenticate', [AuthController::class, 'authenticate'])->name('authenticate');

    // Registration Routes
    Route::get('/register', [RegisterController::class, 'index'])->name('register.form');
    Route::post('/register/store', [RegisterController::class, 'store'])->name('register.siswa');

    // Password Reset Routes
    Route::get('/forgotpass', [AuthController::class, 'showForgotPasswordForm'])->name('password_request');
    Route::post('/forgotpass', [AuthController::class, 'sendResetLink'])->name('password_email');
    Route::get('/resetpass/{token}', [AuthController::class, 'showResetForm'])->name('password_reset');
    Route::post('/resetpass', [AuthController::class, 'resetPassword'])->name('password_update');

    route::get('/403', function () {
        return view('errors.403');
    })->name('403');
});

// Logout Route (accessible when authenticated)
Route::get('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ===== AUTHENTICATED ROUTES =====
Route::middleware(['auth', 'throttle:60,1'])->group(function () {

    Route::get('/notifications/fetch', [NotificationController::class, 'fetch'])->name('notifications.fetch');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark_as_read');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');


    // ===== DASHBOARD ROUTES =====
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // profile route
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'index')->name('index');         // Menampilkan halaman profil
        Route::get('/edit', 'edit')->name('edit');       // Menampilkan form edit profil
        Route::post('/save', 'save')->name('save');     // Menyimpan perubahan dari form edit

        // Ganti password
        Route::get('/change-password', 'showChangePasswordForm')->name('changePasswordForm');
        Route::post('/change-password', 'updatePassword')->name('updatePassword');
    });
    Route::prefix('presensi')->name('presensi.')->group(function () {
        // Main page
        Route::get('/', [PresensiController::class, 'index'])->name('index');

        Route::get('/users/list-siswa', [PresensiController::class, 'getSiswaList'])->name('users.list.siswa');
        Route::get('/siswa-list', [PresensiController::class, 'getSiswaList'])->name('siswa.list');
        Route::post('/batch-alpa', [PresensiController::class, 'batchCreateAlpa'])->name('batch.alpa');

        // CRUD operations
        Route::get('/create', [PresensiController::class, 'create'])->name('create');
        Route::post('/', [PresensiController::class, 'store'])->name('store');
        Route::get('/{presensi}/edit', [PresensiController::class, 'edit'])->name('edit');
        Route::put('/{presensi}', [PresensiController::class, 'update'])->name('update');
        Route::delete('/{presensi}', [PresensiController::class, 'destroy'])->name('destroy');

        // Camera & Izin/Sakit submission (untuk siswa)
        Route::post('/camera', [PresensiController::class, 'PresensiCamera'])->name('camera');
        Route::post('/pengajuan-absen', [PresensiController::class, 'submitAbsenceRequest'])->name('submit_absence');
        
        Route::get('/izin/{presensi}/edit', [PresensiController::class, 'editAbsenceRequest'])->name('edit_absence');
        Route::put('/izin/{presensi}', [PresensiController::class, 'updateAbsenceRequest'])->name('update_absence');

        // Data Endpoints
        Route::post('/data/unified', [PresensiController::class, 'dataUnified'])->name('data.unified');
        Route::get('/data/hari-ini', [PresensiController::class, 'dataHariIni'])->name('data.hari_ini');
        Route::get('/stats', [PresensiController::class, 'getStats'])->name('stats');
        Route::get('/sekolah/list', [PresensiController::class, 'getSekolahList'])->name('sekolah.list');


        // Export routes
        Route::post('/export/excel', [PresensiController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [PresensiController::class, 'exportPDF'])->name('export.pdf');

        // Approval routes
        Route::get('/approval/data', [PresensiController::class, 'approvalData'])->name('approval.data');
        Route::get('/approval/history', [PresensiController::class, 'approvalHistory'])->name('approval.history');
        Route::post('/approval/{presensi}', [PresensiController::class, 'processApproval'])->name('approval.process');
        Route::post('/request-approval-date', [PresensiController::class, 'requestApprovalDate'])->name('request.approval.date');

        // Additional operations
        Route::post('/generate-alpa', [PresensiController::class, 'generateAlpa'])->name('generate.alpa');
        Route::post('/bulk-approval', [PresensiController::class, 'processBulkApproval'])->name('bulk_approval');
    });

    Route::get('dashboard/rekapitulasi', [DashboardController::class, 'rekapitulasiPkl'])->name('dashboard.rekapitulasi.show');
    Route::post('dashboard/rekapitulasi/export', [App\Http\Controllers\DashboardController::class, 'rekapitulasiPklExport'])->name('dashboard.rekapitulasi.export.process');
    Route::get('dashboard/rekapitulasi/download', [DashboardController::class, 'downloadExport'])->name('dashboard.rekapitulasi.export.download');

    // ===== TUGAS HARIAN ROUTES =====
    Route::prefix('tugas-harian')->name('tugas_harian.')->group(function () {
        Route::get('/', [TugasHarianController::class, 'index'])->name('index');
        Route::post('/mulai', [TugasHarianController::class, 'mulaiTugas'])->name('mulai');
        Route::post('/lapor', [TugasHarianController::class, 'laporTugas'])->name('lapor');
    });

    // ===== MANAGEMENT ROUTES =====
    Route::prefix('admin')->name('admin.')->group(function () {


        // 1. Route khusus yang harus di atas 'resource'
        Route::post('penilaian/fetch', [PenilaianController::class, 'fetch'])->name('penilaian.fetch');
        Route::get('penilaian/get-periode/{user}', [PenilaianController::class, 'getPeriodeBySiswa'])->name('penilaian.get_periode');
        Route::post('penilaian/batch-create', [PenilaianController::class, 'batchCreate'])->name('penilaian.batchCreate');
        Route::get('penilaian/{penilaian}/cetak', [PenilaianController::class, 'cetakPDF'])->name('penilaian.cetak');
        Route::get('/penilaian/{penilaian}/sertifikat', [App\Http\Controllers\PenilaianController::class, 'cetakSertifikat'])->name('penilaian.sertifikat');
        Route::resource('penilaian', PenilaianController::class);

        // Admin Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // User Management
        Route::controller(UserController::class)->prefix('user')->name('user.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{user}/edit', 'edit')->name('edit');
            Route::put('/{user}', 'update')->name('update');
            Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('resetpass');
            Route::delete('/{user}', 'destroy')->name('destroy');
            Route::post('/fetch', 'fetch')->name('fetch');
            Route::get('/batch_create', [UserController::class, 'batchCreate'])->name('batch.create');
            Route::post('/batch_store', [UserController::class, 'batchStore'])->name('batch.store');
            Route::get('/batch/download/{filename}', [UserController::class, 'batchDownload'])->name('batch.download');
            Route::get('/export-credentials/{periode}', [UserController::class, 'exportCredentials'])->name('export_credentials');

        });

        // Group Management
        Route::controller(GroupController::class)->prefix('group')->name('group.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{group}/edit', 'edit')->name('edit');
            Route::put('/{group}', 'update')->name('update');
            Route::delete('/{group}', 'destroy')->name('destroy');
            Route::post('/fetch', 'fetch')->name('fetch');
        });

        // School Management
        Route::controller(SekolahController::class)->prefix('sekolah')->name('sekolah.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{sekolah}/edit', 'edit')->name('edit');
            Route::put('/{sekolah}', 'update')->name('update');
            Route::delete('/{sekolah}', 'destroy')->name('destroy');
            Route::post('/fetch', 'fetch')->name('fetch');
            Route::post('/ajax-store', 'ajaxStore')->name('ajax.store');
        });

        // Program Keahlian Management
        Route::post('program-keahlian/fetch', [ProgramKeahlianController::class, 'fetch'])->name('program-keahlian.fetch');
        Route::resource('program-keahlian', ProgramKeahlianController::class);
        Route::post('program-keahlian/ajax-store', [ProgramKeahlianController::class, 'ajaxStore'])->name('program-keahlian.ajax.store');
        

        // Task Breakdown Management
         Route::post('/task-breakdown/store', [TaskBreakdownController::class, 'store'])->name('task.store');


        // Presensi Settings
        Route::controller(PresensiSettingController::class)->prefix('presensi-setting')->name('presensi_setting.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/update', 'update')->name('update');
        });

        // Laporan Management
        Route::controller(LaporanController::class)->prefix('laporan')->name('laporan.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/data', 'data')->name('data');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{laporan}/edit', 'edit')->name('edit');
            Route::put('/{laporan}', 'update')->name('update');
            Route::delete('/{laporan}', 'destroy')->name('destroy');
            // Aprove Reject Laporan
             Route::post('/{laporan}/approve', 'approve')->name('approve');
    Route::post('/{laporan}/reject', 'reject')->name('reject');

            // Route untuk export pdf laporan
             Route::get('/export-pdf', 'exportPDF')->name('export.pdf');
        });
        // Tim Management (digunakan oleh menu "Atur Tim")
        Route::controller(TimController::class)->prefix('tim')->name('tim.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/data', 'data')->name('data');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{tim}/edit', 'edit')->name('edit');
            Route::put('/{tim}', 'update')->name('update');
            Route::delete('/{tim}', 'destroy')->name('destroy');
        });

        // 1. Route POST khusus untuk mengambil data via AJAX oleh DataTables
        Route::post('periode-pkl/fetch', [PeriodePklController::class, 'fetch'])->name('periode-pkl.fetch');
        Route::resource('periode-pkl', PeriodePklController::class);

        // Colect Data Management
        Route::controller(ColectDataController::class)->prefix('colect-data')->name('colect_data.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{colectData}/edit', 'edit')->name('edit');
            Route::put('/{colectData}', 'update')->name('update');
            Route::delete('/{colectData}', 'destroy')->name('destroy');
            Route::post('/fetch', 'fetch')->name('fetch');

            // Route untuk export, ini yang benar
            Route::get('/export-excel', 'exportExcel')->name('export');
        });
        // Jenis Kegiatan Management
        Route::controller(JenisKegiatanController::class)->prefix('jenis-kegiatan')->name('jenis_kegiatan.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{jenisKegiatan}/edit', 'edit')->name('edit');
            Route::put('/{jenisKegiatan}', 'update')->name('update');
            Route::delete('/{jenisKegiatan}', 'destroy')->name('destroy');
            Route::post('/fetch', 'fetch')->name('fetch');
        });
        // / Divisi Management
        Route::controller(DivisiController::class)->prefix('divisi')->name('divisi.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{divisi}/edit', 'edit')->name('edit');
            Route::put('/{divisi}', 'update')->name('update');
            Route::delete('/{divisi}', 'destroy')->name('destroy');
            Route::post('/fetch', 'fetch')->name('fetch');
        });
    });
});
