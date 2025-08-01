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
use App\Http\Controllers\PresensiStatusController;
use App\Http\Controllers\PresensiGambarController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanGambarController;
use App\Http\Controllers\JenisLaporanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegisterController;
use App\Models\Presensi;
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
        Route::get('/presensi/create', [PresensiController::class, 'create'])->name('presensi.create');
        Route::get('/presensi/data', [PresensiController::class, 'data'])->name('presensi.data');



        Route::get('/presensisetting', [PresensiSettingController::class, 'index'])->name('presensi-setting.index');
        Route::post('/presensi_setting/update', [PresensiSettingController::class, 'update'])->name('presensi-setting.update');


        // PresensiStatus
        Route::resource("presensi_status", PresensiStatusController::class);
        Route::post("presensi_status/fetch", [PresensiStatusController::class, "fetch"]);

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
