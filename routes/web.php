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
    Route::controller(PresensiController::class)->prefix('presensi')->name('presensi.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/checkin', 'checkin')->name('checkin');
        Route::post('/checkout', 'checkout')->name('checkout');
        Route::post('/sakit', 'sakit')->name('sakit');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/{presensi}/edit', 'edit')->name('edit');
        Route::put('/{presensi}', 'update')->name('update');
        Route::delete('/{presensi}', 'destroy')->name('destroy');
        Route::post('/generate-alpa', 'generateAlpa')->name('generate.alpa');
    });

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

        // School Management - FIXED
        Route::controller(SekolahController::class)->prefix('sekolah')->name('sekolah.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{sekolah}/edit', 'edit')->name('edit');
            Route::put('/{sekolah}', 'update')->name('update');
            Route::delete('/{sekolah}', 'destroy')->name('destroy');
            Route::post('/fetch', 'fetch')->name('fetch');
        });

        // Task Breakdown Management - FIXED Controller Name
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
        Route::controller(PresensiSettingController::class)->prefix('presensi-settings')->name('presensi_setting.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'update')->name('update');
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

        // ===== ADMIN SETTING TUGAS (View Gabungan) =====
        Route::get('setting-tugas', [SettingTugasController::class, 'index'])->name('setting_tugas.index');

        Route::post('setting-tugas/swap-divisi', [SettingTugasController::class, 'swapDivisi'])->name('setting_tugas.swapDivisi');
        Route::post('setting-tugas/set-divisi', [SettingTugasController::class, 'setDivisi'])
            ->name('setting_tugas.setDivisi');

        // Setting Tugas
        Route::post('setting-tugas', [SettingTugasController::class, 'store'])->name('setting_tugas.store');
        Route::delete('setting-tugas/{id}', [SettingTugasController::class, 'destroy'])->name('setting_tugas.destroy');
    });
});
