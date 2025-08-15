<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\User;
use App\Helpers\PresensiHelper;
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
            $selectedYear = $request->tahun;
        } else {
            $latestPresensi = Presensi::latest('tanggal_presensi')->first();
            if ($latestPresensi) {
                $currentMonth = $latestPresensi->tanggal_presensi->format('Y-m');
                $bulanTeks = $latestPresensi->tanggal_presensi->translatedFormat('F Y');
                $selectedYear = $latestPresensi->tanggal_presensi->year;
            } else {
                $currentMonth = now()->format('Y-m');
                $bulanTeks = now()->translatedFormat('F Y');
                $selectedYear = now()->year;
            }
        }

        $today = Carbon::today();

        // Ambil data presensi summary per hari untuk bulan yang dipilih (menggunakan logika yang sama dengan presensi)
        $presensiSummary = $this->getPresensiSummaryByMonth($currentMonth);

        // Data untuk yearly chart
        $yearlyData = $this->getYearlyPresensiData($selectedYear);

        // Data lainnya (tetap menggunakan hari ini untuk realtime stats)
        $todayPresensi = Presensi::whereDate('tanggal_presensi', $today)->count();
        $totalUsers = User::count();
        $pagiPresensi = Presensi::whereDate('tanggal_presensi', $today)->where('sesi', 'pagi')->count();
        $sorePresensi = Presensi::whereDate('tanggal_presensi', $today)->where('sesi', 'sore')->count();
        $recentPresensi = Presensi::with('user')->orderBy('created_at', 'desc')->limit(5)->get();
        $riwayatPresensi = Presensi::where('user_id', auth()->id())->orderByDesc('tanggal_presensi')->limit(5)->get();

        // Pie chart data untuk bulan yang dipilih (menggunakan status harian)
        $chartData = $this->generateChartData($presensiSummary);

        // Monthly stats menggunakan data summary harian
        $monthlyStats = $this->generateMonthlyStats($presensiSummary);
        $attendancePercentage = $monthlyStats['total_presensi'] > 0
            ? round(($monthlyStats['hadir_count'] / $monthlyStats['total_presensi']) * 100, 1)
            : 0;

        // Data untuk student individual analysis
        $selectedStudent = null;
        $studentChartData = [];
        $studentMonthlyStats = [];

        if ($request->filled('student_id')) {
            $selectedStudent = User::find($request->student_id);
            if ($selectedStudent) {
                // Data presensi siswa untuk bulan yang dipilih
                $studentMonthlyData = $this->getStudentPresensiByMonth($selectedStudent->id, $currentMonth);
                $studentMonthlyStats = $this->generateMonthlyStats($studentMonthlyData);

                $studentAttendancePercentage = $studentMonthlyStats['total_presensi'] > 0
                    ? round(($studentMonthlyStats['hadir_count'] / $studentMonthlyStats['total_presensi']) * 100, 1)
                    : 0;

                $studentMonthlyStats['attendance_percentage'] = $studentAttendancePercentage;
            }
        }

        // List semua siswa untuk dropdown
        $allStudents = User::where('group_id', 4)
            ->orderBy('name')
            ->get();

        // NEW: Data untuk tabel rekap absensi siswa
        $rekapAbsensiSiswa = $this->getRekapAbsensiSiswa($currentMonth);

        // Yearly chart data
        $yearlyChartData = $this->generateYearlyChartData($yearlyData);

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
            'bulanTeks',
            'yearlyChartData',
            'selectedYear',
            'allStudents',
            'selectedStudent',
            'studentMonthlyStats',
            'rekapAbsensiSiswa' // NEW: Tambahan data rekap
        ));
    }

    /**
     * NEW: Ambil rekap absensi semua siswa untuk bulan tertentu
     */
    private function getRekapAbsensiSiswa($monthString)
    {
        // Ambil semua siswa
        $siswa = User::where('group_id', 4)->orderBy('name')->get();

        $rekapData = [];

        foreach ($siswa as $student) {
            // Ambil semua tanggal presensi siswa untuk bulan tersebut
            $tanggalPresensi = Presensi::select('tanggal_presensi')
                ->where('user_id', $student->id)
                ->whereRaw('DATE_FORMAT(tanggal_presensi, "%Y-%m") = ?', [$monthString])
                ->groupBy('tanggal_presensi')
                ->get();

            $statusSummary = [
                'hadir' => 0,
                'sakit' => 0,
                'izin' => 0,
                'terlambat' => 0,
                'alpa' => 0
            ];

            foreach ($tanggalPresensi as $tanggal) {
                $statusHarian = PresensiHelper::hitungStatusHarian($student->id, $tanggal->tanggal_presensi);

                switch (strtolower($statusHarian)) {
                    case 'hadir':
                    case 'tepat waktu':
                        $statusSummary['hadir']++;
                        break;
                    case 'terlambat':
                    case 'sangat terlambat':
                        $statusSummary['terlambat']++;
                        break;
                    case 'sakit':
                        $statusSummary['sakit']++;
                        break;
                    case 'izin':
                        $statusSummary['izin']++;
                        break;
                    case 'alpa':
                        $statusSummary['alpa']++;
                        break;
                }
            }

            // Hitung total hari kerja dalam bulan (asumsi senin-jumat)
            $startDate = Carbon::createFromFormat('Y-m', $monthString)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $monthString)->endOfMonth();
            $totalHariKerja = 0;

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                if ($date->isWeekday()) { // Senin-Jumat
                    $totalHariKerja++;
                }
            }

            // Hitung hari yang tidak hadir (TK = Tidak Hadir)
            $totalPresensi = array_sum($statusSummary);
            $tidakHadir = max(0, $totalHariKerja - $totalPresensi);

            $rekapData[] = [
                'nama' => $student->name,
                'username' => $student->username,
                'hadir' => $statusSummary['hadir'],
                'sakit' => $statusSummary['sakit'],
                'izin' => $statusSummary['izin'],
                'terlambat' => $statusSummary['terlambat'],
                'tidak_hadir' => $tidakHadir,
                'total_presensi' => $totalPresensi,
                'total_hari_kerja' => $totalHariKerja
            ];
        }

        return $rekapData;
    }

    /**
     * Ambil summary presensi per hari untuk bulan tertentu
     */
    private function getPresensiSummaryByMonth($monthString)
    {
        // Ambil semua tanggal unik dalam bulan tersebut yang ada presensi
        $tanggalList = Presensi::select('tanggal_presensi', 'user_id')
            ->whereRaw('DATE_FORMAT(tanggal_presensi, "%Y-%m") = ?', [$monthString])
            ->groupBy('tanggal_presensi', 'user_id')
            ->get();

        $summary = [];

        foreach ($tanggalList as $item) {
            // Gunakan helper untuk menghitung status harian (sama seperti di presensi controller)
            $statusHarian = PresensiHelper::hitungStatusHarian($item->user_id, $item->tanggal_presensi);

            if (!isset($summary[$statusHarian])) {
                $summary[$statusHarian] = 0;
            }
            $summary[$statusHarian]++;
        }

        return $summary;
    }

    /**
     * Ambil data presensi tahunan (12 bulan)
     */
    private function getYearlyPresensiData($year)
    {
        $yearlyData = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthString = sprintf('%04d-%02d', $year, $month);
            $monthlyData = $this->getPresensiSummaryByMonth($monthString);
            $yearlyData[$month] = $monthlyData;
        }

        return $yearlyData;
    }

    /**
     * Ambil data presensi untuk siswa tertentu dalam bulan tertentu
     */
    private function getStudentPresensiByMonth($userId, $monthString)
    {
        $tanggalList = Presensi::select('tanggal_presensi')
            ->where('user_id', $userId)
            ->whereRaw('DATE_FORMAT(tanggal_presensi, "%Y-%m") = ?', [$monthString])
            ->groupBy('tanggal_presensi')
            ->get();

        $summary = [];

        foreach ($tanggalList as $item) {
            $statusHarian = PresensiHelper::hitungStatusHarian($userId, $item->tanggal_presensi);

            if (!isset($summary[$statusHarian])) {
                $summary[$statusHarian] = 0;
            }
            $summary[$statusHarian]++;
        }

        return $summary;
    }

    /**
     * Generate data untuk pie chart
     */
    private function generateChartData($presensiSummary)
    {
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

        $chartData = [
            'labels' => [],
            'data' => [],
            'colors' => []
        ];

        foreach ($presensiSummary as $status => $count) {
            $statusKey = strtolower($status);
            $chartData['labels'][] = ucfirst($status);
            $chartData['data'][] = $count;
            $chartData['colors'][] = $statusColors[$statusKey] ?? '#6c757d';
        }

        return $chartData;
    }

    /**
     * Generate monthly statistics
     */
    private function generateMonthlyStats($presensiSummary)
    {
        $hadirCount = 0;
        $terlambatCount = 0;
        $alpaCount = 0;
        $izinCount = 0;
        $sakitCount = 0;
        $totalPresensi = 0;

        // Handle case when $presensiSummary is array of arrays (for student analysis)
        if (isset($presensiSummary[0]) && is_array($presensiSummary[0])) {
            $presensiSummary = $presensiSummary[0];
        }

        // Ensure $presensiSummary is an array
        if (!is_array($presensiSummary)) {
            $presensiSummary = [];
        }

        foreach ($presensiSummary as $status => $count) {
            // Ensure $count is numeric
            $count = is_numeric($count) ? (int)$count : 0;
            $statusKey = strtolower($status);
            $totalPresensi += $count;

            switch ($statusKey) {
                case 'hadir':
                case 'tepat waktu':
                    $hadirCount += $count;
                    break;
                case 'terlambat':
                case 'sangat terlambat':
                    $terlambatCount += $count;
                    break;
                case 'alpa':
                    $alpaCount += $count;
                    break;
                case 'izin':
                    $izinCount += $count;
                    break;
                case 'sakit':
                    $sakitCount += $count;
                    break;
            }
        }

        return [
            'total_presensi' => $totalPresensi,
            'hadir_count' => $hadirCount,
            'terlambat_count' => $terlambatCount,
            'alpa_count' => $alpaCount,
            'izin_count' => $izinCount,
            'sakit_count' => $sakitCount,
            'izin_sakit_count' => $izinCount + $sakitCount
        ];
    }

    /**
     * Generate data untuk yearly chart
     */
    private function generateYearlyChartData($yearlyData)
    {
        $monthLabels = [];
        $allStatuses = [];

        // Generate month labels
        for ($month = 1; $month <= 12; $month++) {
            $monthLabels[] = Carbon::createFromDate(null, $month, 1)->translatedFormat('M');

            // Collect all statuses
            foreach ($yearlyData[$month] ?? [] as $status => $count) {
                $statusKey = strtolower($status);
                if (!in_array($statusKey, $allStatuses)) {
                    $allStatuses[] = $statusKey;
                }
            }
        }

        // Generate datasets
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

        $datasets = [];
        foreach ($allStatuses as $status) {
            $data = [];
            for ($month = 1; $month <= 12; $month++) {
                $data[] = $yearlyData[$month][$status] ?? 0;
            }

            $datasets[] = [
                'label' => ucfirst($status),
                'data' => $data,
                'backgroundColor' => $statusColors[$status] ?? '#6c757d',
                'borderColor' => $statusColors[$status] ?? '#6c757d',
                'borderWidth' => 1
            ];
        }

        return [
            'labels' => $monthLabels,
            'datasets' => $datasets
        ];
    }
}
