<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\PresensiSetting;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Riwayat presensi pribadi untuk siswa
        $riwayatPresensi = Presensi::where('user_id', auth()->id())
            ->orderByDesc('tanggal_presensi')
            ->limit(5)
            ->get();

        // Get today's presensi statistics (untuk admin)
        $todayPresensi = Presensi::whereDate('tanggal_presensi', $today)->count();
        $totalUsers = User::count();

        // Get presensi by session today (untuk admin)
        $pagiPresensi = Presensi::whereDate('tanggal_presensi', $today)
            ->where('sesi', 'pagi')
            ->count();

        $sorePresensi = Presensi::whereDate('tanggal_presensi', $today)
            ->where('sesi', 'sore')
            ->count();

        // Get recent presensi (untuk admin)
        $recentPresensi = Presensi::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Semua user (admin dan siswa) menggunakan view yang sama
        // Tapi data yang ditampilkan berbeda berdasarkan kondisi @if di view
        return view('administrator.dashboard.index', compact(
            'todayPresensi',
            'totalUsers',
            'pagiPresensi',
            'sorePresensi',
            'recentPresensi',
            'riwayatPresensi'
        ));
    }
}
