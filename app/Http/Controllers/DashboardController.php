<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ambil bulan & tahun dari request, atau pakai default dari data terbaru
        if ($request->filled('bulan') && $request->filled('tahun')) {
            $currentMonth = sprintf('%04d-%02d', $request->tahun, $request->bulan);
            $bulanTeks = Carbon::createFromDate($request->tahun, $request->bulan, 1)
                ->translatedFormat('F Y');
        } else {
            $latestPresensi = Presensi::latest('tanggal_presensi')->first();
            if ($latestPresensi) {
                $currentMonth = $latestPresensi->tanggal_presensi->format('Y-m');
                $bulanTeks = $latestPresensi->tanggal_presensi->translatedFormat('F Y');
            } else {
                $currentMonth = now()->format('Y-m');
                $bulanTeks = now()->translatedFormat('F Y');
            }
        }

        $today = Carbon::today();

        // Data sesuai bulan & tahun yang dipilih
        $presensiThisMonth = Presensi::select('status', DB::raw('count(*) as total'))
            ->whereRaw('DATE_FORMAT(tanggal_presensi, "%Y-%m") = ?', [$currentMonth])
            ->groupBy('status')
            ->get();

        // Data lainnya (disingkat agar fokus ke filter)
        $todayPresensi = Presensi::whereDate('tanggal_presensi', $today)->count();
        $totalUsers = User::count();
        $pagiPresensi = Presensi::whereDate('tanggal_presensi', $today)->where('sesi', 'pagi')->count();
        $sorePresensi = Presensi::whereDate('tanggal_presensi', $today)->where('sesi', 'sore')->count();
        $recentPresensi = Presensi::with('user')->orderBy('created_at', 'desc')->limit(5)->get();
        $riwayatPresensi = Presensi::where('user_id', auth()->id())->orderByDesc('tanggal_presensi')->limit(5)->get();

        $chartData = [
            'labels' => [],
            'data' => [],
            'colors' => []
        ];
        $statusColors = [
            'alpa' => '#dc3545',
            'hadir' => '#28a745',
            'tepat waktu' => '#28a745',
            'terlambat' => '#ffc107',
            'sangat terlambat' => '#fd7e14',
            'izin' => '#17a2b8',
            'sakit' => '#6c757d',
            'terlalu awal' => '#6f42c1'
        ];
        foreach ($presensiThisMonth as $item) {
            $status = strtolower($item->status);
            $chartData['labels'][] = ucfirst($status);
            $chartData['data'][] = $item->total;
            $chartData['colors'][] = $statusColors[$status] ?? '#6c757d';
        }

        $monthlyStats = [
            'total_presensi' => $presensiThisMonth->sum('total'),
            'hadir_count' => $presensiThisMonth->where('status', 'hadir')->first()->total ??
                $presensiThisMonth->where('status', 'tepat waktu')->first()->total ?? 0,
            'alpa_count' => $presensiThisMonth->where('status', 'alpa')->first()->total ?? 0,
            'izin_sakit_count' => ($presensiThisMonth->where('status', 'izin')->first()->total ?? 0) +
                ($presensiThisMonth->where('status', 'sakit')->first()->total ?? 0)
        ];

        $attendancePercentage = $monthlyStats['total_presensi'] > 0
            ? round(($monthlyStats['hadir_count'] / $monthlyStats['total_presensi']) * 100, 1)
            : 0;

        return view('administrator.dashboard.index', compact(
            'todayPresensi',
            'totalUsers',
            'pagiPresensi',
            'sorePresensi',
            'recentPresensi',
            'riwayatPresensi',
            'chartData',
            'monthlyStats',
            'attendancePercentage',
            'bulanTeks'
        ));
    }
}
