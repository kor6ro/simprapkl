<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PresensiSettingController;
use App\Http\Controllers\PresensiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Presensi Setting API
Route::get('/presensi-setting/active', [PresensiSettingController::class, 'getActiveSetting']);

// Presensi API
Route::get('/presensi/statistics', [PresensiController::class, 'getStatistics']);


use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceSettingController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);

    // Middleware tambahan seperti 'can:is-admin' bisa ditambahkan sesuai kebutuhan
    Route::get('/attendance-setting', [AttendanceSettingController::class, 'show']);
    Route::post('/attendance-setting', [AttendanceSettingController::class, 'update']);
});
