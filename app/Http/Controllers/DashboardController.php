<?php

namespace App\Http\Controllers;

use App\Helpers\PresensiHelper;
use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\Tim;
use App\Models\Laporan;
use App\Models\User;
use App\Models\Sekolah;
use App\Models\TaskBreakdown;
use App\Models\ProgramKeahlian;
use App\Models\PeriodePkl;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapitulasiPklExport;

class DashboardController extends Controller
{
    // ... (Fungsi index, rekapitulasiPkl, rekapitulasiPklExport, getAdminDashboardData, getPembimbingDashboardData, getSiswaDashboardData, getKaryawanDashboardData, buildSiswaDetailData tidak berubah dari sebelumnya) ...
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login dulu.');
        }

        if (in_array($user->group_id, [1,2,6,7])) {
            $data = $this->getAdminDashboardData($request);
            return view('administrator.dashboard.admin', $data);

        } elseif ($user->group_id == 3) {
            $data = $this->getPembimbingDashboardData($request, $user);
            return view('administrator.dashboard.pembimbing', $data);

        } elseif ($user->group_id == 4) {
            $data = $this->getSiswaDashboardData($user);
            return view('administrator.dashboard.siswa', $data);

        } elseif ($user->group_id == 5) {
            $data = $this->getKaryawanDashboardData($user);
            return view('administrator.dashboard.karyawan', $data);

        } else {
            abort(403, 'Anda tidak memiliki akses ke dashboard.');
        }
    }

    public function rekapitulasiPkl(Request $request)
    {
        $sekolahs = Sekolah::orderBy('nama')->get();
        $programKeahlians = ProgramKeahlian::orderBy('nama')->get();
        $periodePkls = PeriodePkl::orderBy('awal_periode', 'desc')->get(); // Get PKL periods

        $filters = $request->only(['sekolah_id', 'program_keahlian_id', 'tanggal_awal', 'tanggal_akhir', 'periode_pkl_id']);
        $filters['tanggal_awal'] = $filters['tanggal_awal'] ?? Carbon::now()->startOfWeek()->toDateString();
        $filters['tanggal_akhir'] = $filters['tanggal_akhir'] ?? Carbon::now()->endOfWeek()->toDateString();

        $tanggalAwal = Carbon::parse($filters['tanggal_awal']);
        $tanggalAkhir = Carbon::parse($filters['tanggal_akhir']);
        $rekapData = $this->getRekapData($filters);
        $semuaTanggal = CarbonPeriod::create($tanggalAwal, '1 day', $tanggalAkhir);

        return view('administrator.dashboard.rekapitulasi_pkl', compact(
            'sekolahs',
            'programKeahlians',
            'periodePkls', // Pass periods to the view
            'filters',
            'rekapData',
            'semuaTanggal'
        ));
    }

    public function rekapitulasiPklExport(Request $request)
    {
        try {
            $filters = $request->only(['sekolah_id', 'program_keahlian_id', 'tanggal_awal', 'tanggal_akhir', 'periode_pkl_id']);

            $tanggalAwal = Carbon::parse($request->input('tanggal_awal', Carbon::now()->startOfWeek()));
            $tanggalAkhir = Carbon::parse($request->input('tanggal_akhir', Carbon::now()->endOfWeek()));

            $fileName = 'Rekapitulasi_PKL_' . $tanggalAwal->format('d-m-Y') . '_-_' . $tanggalAkhir->format('d-m-Y') . '.xlsx';
            return Excel::download(new RekapitulasiPklExport($filters), $fileName);

        } catch (\Exception $e) {
            Log::error('Export Excel Dashboard Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat file Excel: ' . $e->getMessage());
        }
    }

    private function getAdminDashboardData(Request $request)
    {
        $sekolahs = Sekolah::orderBy('nama')->get();
        $programKeahlians = ProgramKeahlian::orderBy('nama')->get();
        $periodePkls = PeriodePkl::orderBy('awal_periode', 'desc')->get(); 

        $selectedSekolahId = $request->input('sekolah_id');
        $selectedProgramKeahlianId = $request->input('program_keahlian_id');
        $selectedPeriodePklId = $request->input('periode_pkl_id'); 

        $tanggalTerpilih = $request->filled('bulan')
            ? Carbon::createFromFormat('Y-m', $request->bulan)
            : Carbon::now();

        $selectedBulan = $tanggalTerpilih->month;
        $selectedTahun = $tanggalTerpilih->year;
        $bulanTeks = $tanggalTerpilih->translatedFormat('F Y');

        $pendingPresensiCount = Presensi::whereIn('approval_status', ['pending', 'pending_update'])->count();
        $pendingTimCount = Tim::where('status_approval', 'belum_selesai')->count();
        $hadirHariIni = Presensi::whereDate('presensi_at', Carbon::today())
            ->whereIn('status', ['Tepat Waktu', 'Terlambat', 'Sangat Terlambat', 'Terlalu Awal'])
            ->count();

        $listSiswa = User::where('group_id', 4)
            ->when($selectedSekolahId, fn($q) => $q->where('sekolah_id', $selectedSekolahId))
            ->when($selectedProgramKeahlianId, fn($q) => $q->where('program_keahlian_id', $selectedProgramKeahlianId))
            ->when($selectedPeriodePklId, function ($query) use ($selectedPeriodePklId) { 
                return $query->whereHas('periodePkl', function ($q) use ($selectedPeriodePklId) {
                    $q->where('periode_pkl.id', $selectedPeriodePklId);
                });
            })
            ->when($request->filled('siswa_nama'), fn($q) => $q->where('name', 'like', '%' . $request->siswa_nama . '%'))
            ->with('sekolah')
            ->orderBy('name')
            ->get();

        $dataSiswaDetail = $this->buildSiswaDetailData($listSiswa, $selectedTahun, $selectedBulan);

        return array_merge($dataSiswaDetail, compact(
            'pendingPresensiCount',
            'pendingTimCount',
            'hadirHariIni',
            'bulanTeks',
            'selectedBulan',
            'selectedTahun',
            'sekolahs',
            'programKeahlians',
            'periodePkls', 
            'selectedSekolahId',
            'selectedProgramKeahlianId',
            'selectedPeriodePklId' 
        ));
    }

    private function getPembimbingDashboardData(Request $request, User $pembimbing)
    {
        $sekolahs = Sekolah::where('id', $pembimbing->sekolah_id)->get();
        $programKeahlians = ProgramKeahlian::where('id', $pembimbing->program_keahlian_id)->get();
        $periodePkls = $pembimbing->periodePkl()->orderBy('awal_periode', 'desc')->get(); 

        $tanggalTerpilih = $request->filled('bulan')
            ? Carbon::createFromFormat('Y-m', $request->bulan)
            : Carbon::now();

        $selectedBulan = $tanggalTerpilih->month;
        $selectedTahun = $tanggalTerpilih->year;
        $bulanTeks = $tanggalTerpilih->translatedFormat('F Y');

        $selectedSekolahId = $pembimbing->sekolah_id;
        $selectedProgramKeahlianId = $pembimbing->program_keahlian_id;
        $selectedPeriodePklId = $request->input('periode_pkl_id'); 

        $baseSiswaQuery = User::where('group_id', 4)
            ->where('sekolah_id', $selectedSekolahId)
            ->where('program_keahlian_id', $selectedProgramKeahlianId)
            ->when($selectedPeriodePklId, function ($query) use ($selectedPeriodePklId) { 
                $query->whereHas('periodePkl', function ($q) use ($selectedPeriodePklId) {
                    $q->where('periode_pkl.id', $selectedPeriodePklId);
                });
            });

        $siswaIds = (clone $baseSiswaQuery)->pluck('id');

        $listSiswa = (clone $baseSiswaQuery)
            ->when($request->filled('siswa_nama'), fn($q) => $q->where('name', 'like', '%' . $request->siswa_nama . '%'))
            ->with('sekolah')
            ->orderBy('name')
            ->get();

        $pendingPresensiCount = Presensi::whereIn('user_id', $siswaIds)
            ->whereIn('approval_status', ['pending', 'pending_update'])
            ->count();

        $pendingTimCount = Tim::whereHas('anggota', fn($q) => $q->whereIn('user_id', $siswaIds))
            ->where('status_approval', 'belum_selesai')
            ->count();

        $hadirHariIni = Presensi::whereIn('user_id', $siswaIds)
            ->whereDate('presensi_at', Carbon::today())
            ->whereIn('status', ['Tepat Waktu', 'Terlambat', 'Sangat Terlambat', 'Terlalu Awal'])
            ->count();

        $dataSiswaDetail = $this->buildSiswaDetailData($listSiswa, $selectedTahun, $selectedBulan);

        return array_merge($dataSiswaDetail, compact(
            'pendingPresensiCount',
            'pendingTimCount',
            'hadirHariIni',
            'bulanTeks',
            'selectedBulan',
            'selectedTahun',
            'sekolahs',
            'programKeahlians',
            'periodePkls', 
            'selectedSekolahId',
            'selectedProgramKeahlianId',
            'selectedPeriodePklId' 
        ));
    }

    private function getSiswaDashboardData(User $siswa)
    {
        $today = Carbon::today();

        $presensiHariIni = Presensi::where('user_id', $siswa->id)
            ->whereDate('presensi_at', $today)
            ->get()
            ->keyBy('sesi');

       $daftarTimHariIni = Tim::where('tanggal', $today)
        ->whereHas('anggota', fn($q) => $q->where('user_id', $siswa->id))
        ->with(['ketua', 'anggota'])
        ->get();

        $todaysTask = TaskBreakdown::whereDate('applicable_date', $today)->first();

        $listSiswa = collect([$siswa]);
        $dataRekap = $this->buildSiswaDetailData($listSiswa, $today->year, $today->month);

        $bulanTeks = $today->translatedFormat('F Y');

        return [
            'siswa' => $siswa,
            'presensiHariIni' => $presensiHariIni,
            'daftarTimHariIni' => $daftarTimHariIni,
            'data' => $dataRekap['dataSiswaDetail'][0] ?? null,
            'todaysTask' => $todaysTask,
            'bulanTeks' => $bulanTeks,
        ];
    }
    public function getKaryawanDashboardData(User $karyawan)
    {
        $today = Carbon::today();

        $teamsToday = Tim::whereHas('ketua', function ($query) use ($karyawan) {
                $query->where('user_id', $karyawan->id);
            })
            ->whereDate('tanggal', $today)
            ->with('anggota', 'divisi')
            ->get();

        $siswaBimbinganIds = $teamsToday->pluck('anggota.*.id')->flatten()->unique();

        $pendingTaskCount = Tim::whereHas('ketua', function ($query) use ($karyawan) {
                $query->where('user_id', $karyawan->id);
            })
            ->whereIn('status_approval', ['belum_selesai', 'perlu_revisi'])
            ->count();

        $hadirHariIniCount = 0;
        if ($siswaBimbinganIds->isNotEmpty()) {
            $hadirHariIniCount = Presensi::whereIn('user_id', $siswaBimbinganIds)
                ->whereDate('presensi_at', $today)
                ->whereIn('status', ['Tepat Waktu', 'Terlambat', 'Sangat Terlambat', 'Terlalu Awal', 'Hadir'])
                ->distinct('user_id')
                ->count();
        }
        
        return [
            'karyawan' => $karyawan,
            'teamsToday' => $teamsToday,
            'pendingTaskCount' => $pendingTaskCount,
            'totalSiswaBimbingan' => $siswaBimbinganIds->count(),
            'hadirHariIniCount' => $hadirHariIniCount,
        ];
    }
    private function buildSiswaDetailData($listSiswa, $selectedTahun, $selectedBulan)
    {
        if ($listSiswa->isEmpty()) {
            return ['dataSiswaDetail' => []];
        }

        $presensiBulanIni = Presensi::whereIn('user_id', $listSiswa->pluck('id'))
            ->whereYear('presensi_at', $selectedTahun)
            ->whereMonth('presensi_at', $selectedBulan)
            ->get()
            ->groupBy('user_id');

        $laporanHariIni = Laporan::with(['jenisKegiatan', 'tim'])
            ->whereIn('user_id', $listSiswa->pluck('id'))
            ->whereDate('created_at', Carbon::today())
            ->get()
            ->groupBy('user_id');

        $dataSiswaDetail = [];
        foreach ($listSiswa as $siswa) {
            $rekapBulanan = $this->calculateMonthlyAttendance(
                $presensiBulanIni->get($siswa->id) ?? collect(),
                $selectedTahun,
                $selectedBulan
            );

            $kegiatanHariIniSiswa = $laporanHariIni->get($siswa->id) ?? collect();

            $dataSiswaDetail[] = [
                'siswa' => $siswa,
                'rekap_bulan_ini' => $rekapBulanan,
                'kegiatan_hari_ini' => $kegiatanHariIniSiswa,
            ];
        }

        return ['dataSiswaDetail' => $dataSiswaDetail];
    }

    private function calculateMonthlyAttendance($presensiSiswa, $year, $month)
    {
        $rekap = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
        $presensiPerHari = $presensiSiswa->groupBy(fn ($p) => $p->presensi_at->toDateString());

        // Definisikan grup status untuk pengecekan
        $statusAlpa = ['Alpa'];
        $statusHadir = ['Tepat Waktu', 'Terlambat', 'Sangat Terlambat', 'Terlalu Awal', 'Hadir'];
        $statusSakit = ['Sakit'];
        $statusIzin = ['Izin', 'Izin Mendesak', 'Izin Terencana'];

        // Iterasi hanya pada hari-hari yang memiliki data presensi
        foreach ($presensiPerHari as $tanggal => $presensiHarian) {
            if (Carbon::parse($tanggal)->isWeekend()) {
                continue; // Lewati akhir pekan
            }

            // Cek keberadaan setiap jenis status dalam satu hari
            $isAlpa = $presensiHarian->contains(fn ($p) => in_array($p->status, $statusAlpa));
            $isHadir = $presensiHarian->contains(fn ($p) => in_array($p->status, $statusHadir));
            $isSakit = $presensiHarian->contains(fn ($p) => in_array($p->status, $statusSakit));
            $isIzin = $presensiHarian->contains(fn ($p) => in_array($p->status, $statusIzin));

            // Terapkan logika prioritas
            if ($isAlpa) {
                $rekap['alpa']++;
            } elseif ($isHadir) {
                $rekap['hadir']++;
            } elseif ($isSakit) {
                $rekap['sakit']++;
            } elseif ($isIzin) {
                $rekap['izin']++;
            }
        }
        return $rekap;
    }


    /**
     * [PERUBAHAN TERBARU]
     * Data kosong/tanpa presensi akan ditandai sebagai '-' (strip).
     * Status 'A' (Alpa) hanya muncul jika ada data presensi dengan status 'Alpa'.
     */
    private function getRekapData($filters)
    {
        $tanggalAwal = Carbon::parse($filters['tanggal_awal']);
        $tanggalAkhir = Carbon::parse($filters['tanggal_akhir']);

        $listSiswa = User::where('group_id', 4)
            ->when($filters['sekolah_id'] ?? null, fn($q, $id) => $q->where('sekolah_id', $id))
            ->when($filters['program_keahlian_id'] ?? null, fn($q, $id) => $q->where('program_keahlian_id', $id))
            ->when($filters['periode_pkl_id'] ?? null, function ($query, $id) {
                return $query->whereHas('periodePkl', function ($q) use ($id) {
                    $q->where('periode_pkl.id', $id);
                });
            })
            ->with('sekolah')
            ->orderBy('name')
            ->get();

        if ($listSiswa->isEmpty()) {
            return collect();
        }

        $presensiData = Presensi::whereIn('user_id', $listSiswa->pluck('id'))
            ->whereBetween('presensi_at', [$tanggalAwal, $tanggalAkhir])
            ->get()
            ->groupBy('user_id');

        $laporanData = Laporan::whereIn('user_id', $listSiswa->pluck('id'))
            ->whereBetween('created_at', [$tanggalAwal, $tanggalAkhir])
            ->get()
            ->groupBy('user_id');

        // Definisikan grup status
        $statusAlpa = ['Alpa'];
        $statusHadir = ['Tepat Waktu', 'Terlambat', 'Sangat Terlambat', 'Terlalu Awal', 'Hadir'];
        $statusSakit = ['Sakit'];
        $statusIzin = ['Izin', 'Izin Mendesak', 'Izin Terencana'];

        $rekapData = collect();
        foreach ($listSiswa as $siswa) {
            $presensiSiswa = $presensiData->get($siswa->id, collect());
            $laporanSiswa = $laporanData->get($siswa->id, collect());

            $rekapHarian = [];
            $period = CarbonPeriod::create($tanggalAwal, $tanggalAkhir);

            foreach ($period as $tanggalObj) {
                $tanggal = $tanggalObj->toDateString();
                $presensiHariIni = $presensiSiswa->filter(fn($p) => $p->presensi_at->isSameDay($tanggal));

                $absenStatus = '-'; // Default ke strip (tidak ada data)

                if ($presensiHariIni->isNotEmpty()) {
                    // Jika ada data, baru tentukan statusnya
                    $isAlpa = $presensiHariIni->contains(fn($p) => in_array($p->status, $statusAlpa));
                    $isHadir = $presensiHariIni->contains(fn($p) => in_array($p->status, $statusHadir));
                    $isSakit = $presensiHariIni->contains(fn($p) => in_array($p->status, $statusSakit));
                    $isIzin = $presensiHariIni->contains(fn($p) => in_array($p->status, $statusIzin));
                    
                    if ($isAlpa) {
                        $absenStatus = 'A';
                    } elseif ($isHadir) {
                        $absenStatus = 'H';
                    } elseif ($isSakit) {
                        $absenStatus = 'S';
                    } elseif ($isIzin) {
                        $absenStatus = 'I';
                    }
                }
                
                if ($tanggalObj->isWeekend() && $absenStatus === '-') {
                    $absenStatus = 'LBR';
                }

                $laporanPadaHariItu = $laporanSiswa->first(fn($l) => $l->created_at->isSameDay($tanggal));

                $rekapHarian[$tanggal] = [
                    'absen' => $absenStatus,
                    'laporan' => $laporanPadaHariItu ? 'OK' : '-',
                ];
            }

            $rekapData->push([
                'siswa' => $siswa,
                'rekap_harian' => $rekapHarian,
            ]);
        }
        return $rekapData;
    }
}