<?php

use App\Http\Controllers\RegisterSiswaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\ColectDataController;
use App\Http\Controllers\TaskBreakdownController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\SettingPresensiController;
use App\Http\Controllers\PresensiGambarController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanGambarController;
use App\Http\Controllers\JenisLaporanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get("/", function () {
    return redirect()->to(route("login"));
});

// Register Siswa
Route::get('/register', [RegisterController::class, 'index'])->name('register.form');
Route::post('/register/store', [RegisterController::class, 'store'])->name('register.siswa');

Route::get("/login", [AuthController::class, "index"])->name("login");
Route::get("/logout", [AuthController::class, "logout"])->name("logout");
Route::post("/authenticate", [AuthController::class, "authenticate"])->name(
    "authenticate"
);

Route::prefix("/admin")
    ->middleware("auth")
    ->group(function () {

        // Dashboard
        Route::get("dashboard", [DashboardController::class, "index"])->name(
            "dashboard"
        );

        // Profile
        Route::get("profile", [ProfileController::class, "index"])->name(
            "profile.index"
        );
        Route::get("profile/edit", [ProfileController::class, "edit"])->name(
            "profile.edit"
        );
        Route::post("profile", [ProfileController::class, "save"])->name(
            "profile.update"
        );

        // User
        Route::resource("user", UserController::class);
        Route::post("user/fetch", [UserController::class, "fetch"]);

        // Group
        Route::resource("group", GroupController::class);
        Route::post("group/fetch", [GroupController::class, "fetch"]);

        // Sekolah
        Route::resource("sekolah", SekolahController::class);
        Route::post("sekolah/fetch", [SekolahController::class, "fetch"]);

        // ColectData
        Route::resource("colect_data", ColectDataController::class);
        Route::post("colect_data/fetch", [
            ColectDataController::class,
            "fetch",
        ]);

        // TaskBreakdown
        Route::resource("task_break_down", TaskBreakDownController::class);
        Route::post("task_break_down/fetch", [TaskBreakDownController::class, "fetch"]);

        // Presensi
        Route::resource("presensi", PresensiController::class);
        Route::post("presensi/fetch", [PresensiController::class, "fetch"]);

        // SettingPresensi
        Route::resource("setting_presensi", SettingPresensiController::class);
        Route::post("setting_presensi/fetch", [
            SettingPresensiController::class,
            "fetch",
        ]);

        // PresensiGambar
        Route::resource("presensi_gambar", PresensiGambarController::class);
        Route::post("presensi_gambar/fetch", [
            PresensiGambarController::class,
            "fetch",
        ]);

        // Laporan
        Route::resource("laporan", LaporanController::class);
        Route::post("laporan/fetch", [LaporanController::class, "fetch"]);

        // LaporanGambar
        Route::resource("laporan_gambar", LaporanGambarController::class);
        Route::post("laporan_gambar/fetch", [
            LaporanGambarController::class,
            "fetch",
        ]);
    });
