<?php

use App\Http\Controllers\administrator\PenilaianController as AdministratorPenilaianController;
use App\Http\Controllers\RegisterSiswaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\ColectDataController;
use App\Http\Controllers\TaskBreakdownController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\PresensiSettingController;
use App\Http\Controllers\PresensiGambarController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanGambarController;
use App\Http\Controllers\JenisLaporanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SettingTugasController;
use App\Http\Controllers\TugasHarianController;
use App\Models\Presensi;
use App\Http\Controllers\PenilaianController;
use Illuminate\Support\Facades\Route;

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
});

// Logout Route (accessible when authenticated)
Route::get('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ===== AUTHENTICATED ROUTES =====
Route::middleware(['auth', 'throttle:60,1'])->group(function () {


    // ===== DASHBOARD ROUTES =====
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ===== PROFILE ROUTES =====
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/edit', 'edit')->name('edit');
        Route::post('/update', 'save')->name('update');
    });

    Route::prefix('presensi')->name('presensi.')->group(function () {
        // Main page
        Route::get('/', [PresensiController::class, 'index'])->name('index');

        // CRUD operations (disesuaikan untuk Route Model Binding)
        Route::get('/create', [PresensiController::class, 'create'])->name('create');
        Route::post('/', [PresensiController::class, 'store'])->name('store'); // Menggunakan URL dasar untuk store
        Route::get('/{presensi}/edit', [PresensiController::class, 'edit'])->name('edit'); // Menggunakan {presensi}
        Route::put('/{presensi}', [PresensiController::class, 'update'])->name('update'); // Menggunakan {presensi}
        Route::delete('/{presensi}', [PresensiController::class, 'destroy'])->name('destroy'); // Menggunakan {presensi}

        // Camera & Izin/Sakit submission (untuk siswa)
        Route::post('/camera', [PresensiController::class, 'PresensiCamera'])->name('camera');
        Route::post('/izin-sakit', [PresensiController::class, 'submitIzinSakit'])->name('izin-sakit');
        Route::post('/request-approval-date', [PresensiController::class, 'requestApprovalDate'])->name('request.approval.date');

        // Data Endpoints (duplikasi dihapus dan diperbaiki)
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
        Route::post('/approval/{id}', [PresensiController::class, 'processApproval'])->name('approval.process');

        // Additional operations
        Route::post('/generate-alpa', [PresensiController::class, 'generateAlpa'])->name('generate.alpa');
    });

    // ===== TUGAS HARIAN ROUTES =====
    Route::prefix('tugas-harian')->name('tugas_harian.')->group(function () {
        Route::get('/', [TugasHarianController::class, 'index'])->name('index');
        Route::post('/mulai', [TugasHarianController::class, 'mulaiTugas'])->name('mulai');
        Route::post('/lapor', [TugasHarianController::class, 'laporTugas'])->name('lapor');
    });

    // ===== MANAGEMENT ROUTES =====
    Route::prefix('admin')->name('admin.')->group(function () {

        // --- Route untuk Fitur Penilaian ---
        Route::post('penilaian/fetch', [PenilaianController::class, 'fetch'])->name('penilaian.fetch');
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
            Route::delete('/{user}', 'destroy')->name('destroy');
            Route::post('/fetch', 'fetch')->name('fetch');
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
        });

        // Task Breakdown Management
        Route::controller(TaskBreakdownController::class)->prefix('task-breakdown')->name('task_breakdown.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{taskBreakdown}/edit', 'edit')->name('edit');
            Route::put('/{taskBreakdown}', 'update')->name('update');
            Route::delete('/{taskBreakdown}', 'destroy')->name('destroy');
            Route::post('/fetch', 'fetch')->name('fetch');
        });

        // Presensi Settings
        Route::controller(PresensiSettingController::class)->prefix('presensi-setting')->name('presensi_setting.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/update', 'update')->name('update');
        });

        // Reports Management
        Route::controller(LaporanController::class)->prefix('laporan')->name('laporan.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{laporan}/edit', 'edit')->name('edit');
            Route::put('/{laporan}', 'update')->name('update');
            Route::delete('/{laporan}', 'destroy')->name('destroy');
            Route::post('/fetch', 'fetch')->name('fetch');
        });

        // Report Images Management
        Route::controller(LaporanGambarController::class)->prefix('laporan-gambar')->name('laporan_gambar.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{laporanGambar}/edit', 'edit')->name('edit');
            Route::put('/{laporanGambar}', 'update')->name('update');
            Route::delete('/{laporanGambar}', 'destroy')->name('destroy');
            Route::post('/fetch', 'fetch')->name('fetch');
        });

        // Data Collection Management
        Route::controller(ColectDataController::class)->prefix('colect-data')->name('colect_data.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{colectData}/edit', 'edit')->name('edit');
            Route::put('/{colectData}', 'update')->name('update');
            Route::delete('/{colectData}', 'destroy')->name('destroy');
            Route::post('/fetch', 'fetch')->name('fetch');
        });
        // Setting Tugas - FIXED ROUTES
        Route::controller(SettingTugasController::class)->prefix('setting-tugas')->name('setting_tugas.')->group(function () {
            // Main routes
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');

            // DataTables route untuk server-side processing  
            Route::get('/data', 'data')->name('data');

            // Utility routes HARUS di atas parameter routes untuk menghindari konflik
            Route::get('/available-users', 'getAvailableUsers')->name('getAvailableUsers');
            Route::get('/statistics', 'getStatistics')->name('statistics');
            Route::get('/edit-all', 'getAllTeamsForEdit')->name('getAllTeamsForEdit');

            // Bulk operations HARUS di atas route /{id}
            Route::post('/bulk-store', 'storeBulk')->name('storeBulk');
            Route::put('/bulk-update', 'updateBulk')->name('updateBulk');
            Route::post('/destroy-all', 'destroyAll')->name('destroyAll');

            // CRUD operations - parameter routes di paling bawah
            Route::post('/', 'store')->name('store');
            Route::get('/get/{id}', 'getTeam')->name('getTeam');              // Specific route
            Route::get('/{id}/edit', 'edit')->name('edit');                   // Edit form route
            Route::put('/{id}', 'update')->name('update');                    // Update route
            Route::delete('/{id}', 'destroy')->name('destroy');               // Delete route
        });
    });
});
