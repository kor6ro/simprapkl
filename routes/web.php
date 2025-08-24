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
use App\Http\Controllers\DivisiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JenisKegiatanController;
use App\Http\Controllers\TimController;
use App\Models\Tim;

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

    Route::prefix('admin')->name('admin.')->group(function () {
        // ... rute lain seperti user, group, divisi
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
        // Tim Management (digunakan oleh menu "Atur Tim")
        Route::controller(TimController::class)->prefix('tim')->name('tim.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/data', 'data')->name('data'); // Rute untuk DataTables
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{tim}/edit', 'edit')->name('edit');
            Route::put('/{tim}', 'update')->name('update');
            Route::delete('/{tim}', 'destroy')->name('destroy');
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
