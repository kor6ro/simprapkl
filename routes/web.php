<?php

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

    // ===== PRESENSI ROUTES =====
    Route::prefix('presensi')->name('presensi.')->group(function () {
        // Main presensi routes
        Route::get('/', [PresensiController::class, 'index'])->name('index');
        Route::post('/checkin', [PresensiController::class, 'checkin'])->name('checkin');
        Route::post('/checkout', [PresensiController::class, 'checkout'])->name('checkout');
        Route::post('/sakit', [PresensiController::class, 'sakit'])->name('sakit');
        Route::get('/rekap', [PresensiController::class, 'rekap'])->name('rekap');

        // Camera presensi routes
        Route::post('/camera', [PresensiController::class, 'PresensiCamera'])->name('camera');

        // Izin/Sakit manual submission
        Route::post('/izin-sakit', [PresensiController::class, 'submitIzinSakit'])->name('izin-sakit');

        // Request edit alpa to izin/sakit (for students)
        Route::post('/request-edit', [PresensiController::class, 'requestEditAlpa'])->name('request-edit');

        // Admin approval routes
        Route::get('/approval', [PresensiController::class, 'approvalIndex'])
            ->name('approval.index');
        Route::post('/approval/{presensiId}', [PresensiController::class, 'processApproval'])
            ->name('approval');

        // CRUD operations
        Route::get('/create', [PresensiController::class, 'create'])->name('create');
        Route::post('/store', [PresensiController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [PresensiController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PresensiController::class, 'update'])->name('update');
        Route::delete('/{id}', [PresensiController::class, 'destroy'])->name('destroy');

        // Additional operations
        Route::post('/generate-alpa', [PresensiController::class, 'generateAlpa'])->name('generate.alpa');
        Route::get('/all', [PresensiController::class, 'all'])->name('all'); // For Yajra DataTables
    });

    // ===== TUGAS HARIAN ROUTES =====
    Route::prefix('tugas-harian')->name('tugas_harian.')->group(function () {
        Route::get('/', [TugasHarianController::class, 'index'])->name('index');
        Route::post('/mulai', [TugasHarianController::class, 'mulaiTugas'])->name('mulai');
        Route::post('/lapor', [TugasHarianController::class, 'laporTugas'])->name('lapor');
    });

    // ===== MANAGEMENT ROUTES =====
    Route::prefix('admin')->name('admin.')->group(function () {

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
        // Setting Tugas 
Route::controller(SettingTugasController::class)->prefix('setting-tugas')->name('setting_tugas.')->group(function () {
    // Main route
    Route::get('/', 'index')->name('index');
    
    // Individual team operations
    Route::post('/', 'store')->name('store');
    
    // PERBAIKAN: Bulk operations HARUS di atas route /{id}
    Route::post('/bulk-store', 'storeBulk')->name('storeBulk');
    Route::put('/bulk-update', 'updateBulk')->name('updateBulk');
    Route::post('/destroy-all', 'destroyAll')->name('destroyAll'); // UBAH ke POST
    
    // Utility routes (harus di atas route /{id})
    Route::post('/swap-divisi', 'swapDivisi')->name('swapDivisi');
    Route::get('/statistics', 'getStatistics')->name('statistics');
    Route::get('/edit-all', 'getAllTeamsForEdit')->name('getAllTeamsForEdit');
    
    // Route dengan parameter ID HARUS di bawah semua route spesifik
    Route::delete('/{id}', 'destroy')->name('destroy');
});
    });
});