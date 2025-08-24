<?php

namespace App\Http\Controllers;

use App\Helpers\PresensiHelper;
use App\Models\Presensi;
use App\Models\PresensiSetting;
use App\Models\PresensiStatus;
use App\Models\User;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDF;

class PresensiController extends Controller
{
    /**
     * Halaman utama presensi (1 halaman, multi-tab).
     */
    // app/Http/Controllers/PresensiController.php

    public function index()
    {
        // Logika ini dipertahankan karena digunakan oleh elemen halaman lain (di luar tabel)
        $user    = Auth::user();
        $today   = now()->toDateString();
        $setting = PresensiSetting::first();

        $presensiHariIni = Presensi::where('user_id', $user->id)
            ->where('tanggal_presensi', $today)
            ->get();

        $statusPresensi = $this->getStatusPresensiHariIni($presensiHariIni, $setting);
        $sekolahList = Sekolah::select('id', 'nama')->orderBy('nama')->get();

        // Tugas fungsi index() sekarang hanya menampilkan view dengan data yang diperlukan
        return view('administrator.presensi.index', compact('statusPresensi', 'setting', 'sekolahList'));
    }

    // app/Http/Controllers/PresensiController.php

    public function create()
    {
        // Hanya user siswa (group_id 4) yang bisa dipilih
        $users = User::where('group_id', 4)->with('sekolah')->orderBy('name')->get();
        $presensiStatus = PresensiStatus::all();
        return view('administrator.presensi.create', compact('users', 'presensiStatus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal_presensi' => 'required|date',
            'sesi' => 'required|in:pagi,sore',
            'status' => 'required|string',
            'jam_presensi' => 'nullable|date_format:H:i',
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Cek duplikasi
        $existing = Presensi::where('user_id', $request->user_id)
            ->where('tanggal_presensi', $request->tanggal_presensi)
            ->where('sesi', $request->sesi)
            ->first();

        if ($existing) {
            return back()->with('error', 'Data presensi untuk siswa, tanggal, dan sesi yang sama sudah ada.')->withInput();
        }

        $buktiPath = null;
        if ($request->hasFile('bukti_foto')) {
            $buktiPath = $request->file('bukti_foto')->store('uploads/presensi', 'public');
        }

        $statusId = PresensiStatus::where('status', $request->status)->value('id');

        Presensi::create([
            'user_id' => $request->user_id,
            'tanggal_presensi' => $request->tanggal_presensi,
            'sesi' => $request->sesi,
            'jam_presensi' => $request->jam_presensi,
            'status' => $request->status,
            'presensi_status_id' => $statusId,
            'keterangan' => $request->keterangan,
            'bukti_foto' => $buktiPath
        ]);

        return redirect()->route('presensi.index')->with('success', 'Data presensi berhasil ditambahkan.');
    }

    public function edit(Presensi $presensi)
    {
        $presensiStatus = PresensiStatus::all();
        return view('administrator.presensi.edit', compact('presensi', 'presensiStatus'));
    }

    public function update(Request $request, Presensi $presensi)
    {
        $request->validate([
            'tanggal_presensi' => 'required|date',
            'sesi' => 'required|in:pagi,sore',
            'status' => 'required|string',
            'jam_presensi' => 'nullable|date_format:H:i',
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = $request->only(['tanggal_presensi', 'sesi', 'jam_presensi', 'status', 'keterangan']);

        if ($request->hasFile('bukti_foto')) {
            // Hapus foto lama jika ada
            if ($presensi->bukti_foto) {
                Storage::disk('public')->delete($presensi->bukti_foto);
            }
            $data['bukti_foto'] = $request->file('bukti_foto')->store('uploads/presensi', 'public');
        }

        $data['presensi_status_id'] = PresensiStatus::where('status', $request->status)->value('id');

        $presensi->update($data);

        return redirect()->route('presensi.index')->with('success', 'Data presensi berhasil diperbarui.');
    }
    /**
     * [OPTIMIZED] Menyediakan data untuk DataTable Unified.
     */
    public function dataUnified(Request $request)
    {
        $query = Presensi::query()
            ->leftJoin('user', 'presensi.user_id', '=', 'user.id')
            ->leftJoin('sekolah', 'user.sekolah_id', '=', 'sekolah.id')
            ->select('presensi.*')
            ->with(['user:id,name,sekolah_id', 'user.sekolah:id,nama']);

        if (isSiswa()) {
            $query->where('presensi.user_id', Auth::id());
        }

        $this->applyFiltersToQuery($query, $request);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nama', fn($row) => $row->user->name ?? '-')
            ->addColumn('sekolah', fn($row) => $row->user->sekolah->nama ?? '-')
            ->addColumn('tanggal', fn($row) => Carbon::parse($row->tanggal_presensi)->format('d/m/Y'))
            ->addColumn('sesi_badge', fn($row) => '<span class="badge bg-' . ($row->sesi === 'pagi' ? 'info' : 'warning') . '">' . ucfirst($row->sesi) . '</span>')
            ->addColumn('jam_presensi', fn($row) => $row->jam_presensi ? Carbon::parse($row->jam_presensi)->format('H:i') : '-')
            ->addColumn('status_badge', fn($row) => $this->renderStatusBadge($row))
            ->addColumn('approval_badge', fn($row) => $this->renderApprovalBadge($row))
            ->addColumn('keterangan', function ($row) {
                $keterangan = $row->keterangan ?? '-';
                if (strlen($keterangan) > 50) {
                    return '<div class="text-truncate" style="max-width: 150px;" title="' . e($keterangan) . '">' . e(substr($keterangan, 0, 50)) . '...</div>';
                }
                return e($keterangan);
            })
            ->addColumn('bukti_foto', function ($row) {
                if ($row->bukti_foto && $row->bukti_foto !== 'default.jpg') {
                    $url = asset('storage/' . $row->bukti_foto);
                    return '<button class="btn btn-sm btn-outline-primary" onclick="showImage(\'' . $url . '\')"><i class="fas fa-eye"></i></button>';
                }
                return '-';
            })
            ->addColumn('aksi', fn($row) => $this->renderAksiColumn($row))
            ->addColumn('is_admin', fn() => isAdmin()) // Mengirim flag jika user adalah admin
            ->addColumn('is_owner', fn($row) => $row->user_id === Auth::id()) // Mengirim flag jika user adalah pemilik data
            ->rawColumns(['sesi_badge', 'status_badge', 'approval_badge', 'keterangan', 'bukti_foto', 'aksi'])
            ->make(true);
    }

    // ===================================================================
    // KODE ORIGINAL ANDA YANG DIPERTAHANKAN UNTUK KOMPATIBILITAS
    // ===================================================================

    /**
     * DataTables: Presensi Hari Ini (detail per sesi)
     * Method ini dipertahankan jika ada bagian lain yang masih menggunakannya.
     */
    public function dataHariIni()
    {
        $today = now()->toDateString();
        $data = Presensi::with(['user.sekolah', 'presensiStatus'])->whereDate('tanggal_presensi', $today)->orderBy('presensi.created_at', 'desc');
        if (Auth::user()->group_id == 4) {
            $data->where('user_id', Auth::id());
        }
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nama', fn($row) => $row->user->name ?? '-')
            ->addColumn('sekolah', fn($row) => $row->user->sekolah->nama ?? '-')
            ->addColumn('status_badge', fn($row) => $this->renderStatusBadge($row))
            ->addColumn('bukti_foto', function ($row) {
                if ($row->bukti_foto && $row->bukti_foto !== 'default.jpg') {
                    $url = asset('storage/' . $row->bukti_foto);
                    return '<a href="' . $url . '" target="_blank"><img src="' . $url . '" width="60" class="rounded"></a>';
                }
                return '-';
            })
            ->rawColumns(['status_badge', 'bukti_foto'])
            ->make(true);
    }

    /**
     * Presensi otomatis (kamera) – input: base64 image
     */
    public function PresensiCamera(Request $request)
    {
        Log::info('=== CAMERA PRESENSI AUTO START ===', ['user_id' => auth()->id()]);
        $request->validate(['image_data' => 'required|string', 'keterangan' => 'nullable|string|max:255']);
        $user = Auth::user();
        $today = now()->toDateString();
        $now = now();
        $setting = PresensiSetting::first();
        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Pengaturan presensi belum dikonfigurasi'], 422);
        }
        $currentTime = $now->format('H:i');
        $sesi = PresensiHelper::getCurrentSession($setting, $currentTime);
        if (!$sesi) {
            return response()->json(['success' => false, 'message' => 'Presensi hanya dapat dilakukan pada jam kerja'], 422);
        }
        if (Presensi::where('user_id', $user->id)->where('tanggal_presensi', $today)->where('sesi', $sesi)->exists()) {
            return response()->json(['success' => false, 'message' => "Anda sudah melakukan presensi {$sesi} hari ini"], 422);
        }
        try {
            $imageFile = $this->processBase64Image($request->image_data);
            if (!$imageFile) throw new \Exception('Gagal memproses gambar');
            $fileName = 'camera_' . date('Y-m-d_H-i-s') . '_' . $user->id . '_' . uniqid() . '.jpg';
            $path = 'uploads/presensi/' . $fileName;
            if (!Storage::disk('public')->put($path, $imageFile)) {
                throw new \Exception('Gagal menyimpan gambar');
            }
            $jamPresensi = $now->format('H:i:s');
            $status = $this->getStatusByTime($jamPresensi, $sesi, $setting);
            $statusId = PresensiStatus::where('status', $status)->value('id');
            Presensi::create(['user_id' => $user->id, 'tanggal_presensi' => $today, 'sesi' => $sesi, 'jam_presensi' => $jamPresensi, 'status' => $status, 'presensi_status_id' => $statusId, 'bukti_foto' => $path, 'keterangan' => $request->keterangan ?? "Presensi {$sesi} otomatis",]);
            return response()->json(['success' => true, 'message' => "Presensi {$sesi} berhasil! Status: {$status}"]);
        } catch (\Exception $e) {
            Log::error('Camera presensi error', ['error' => $e->getMessage()]);
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan presensi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Submit Izin/Sakit
     */
    public function submitIzinSakit(Request $request)
    {
        $request->validate(['jenis' => 'required|in:Izin,Sakit', 'keterangan' => 'required|string|min:10|max:255', 'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048']);
        $user = Auth::user();
        $today = now()->toDateString();
        if (Presensi::where('user_id', $user->id)->where('tanggal_presensi', $today)->exists()) {
            return back()->with('error', 'Anda sudah melakukan presensi hari ini');
        }
        try {
            $buktiPath = $request->hasFile('bukti_foto') ? $request->file('bukti_foto')->store('uploads/presensi', 'public') : null;
            $jenis = $request->jenis;
            $statusId = PresensiStatus::where('status', $jenis)->value('id');
            foreach (['pagi', 'sore'] as $sesi) {
                Presensi::create(['user_id' => $user->id, 'tanggal_presensi' => $today, 'sesi' => $sesi, 'status' => $jenis, 'presensi_status_id' => $statusId, 'bukti_foto' => $buktiPath, 'keterangan' => $request->keterangan, 'jam_presensi' => null,]);
            }
            return back()->with('success', "Pengajuan {$jenis} berhasil disubmit!");
        } catch (\Exception $e) {
            Log::error('Izin/Sakit submit error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan data. Silakan coba lagi.');
        }
    }

    /**
     * Siswa meminta perubahan status Alpa
     */
    public function requestApprovalDate(Request $request)
    {
        $request->validate(['tanggal_presensi' => 'required|date', 'requested_status' => 'required|in:Izin,Sakit', 'keterangan' => 'required|string|min:20', 'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048']);
        $userId = Auth::id();
        $tanggal = $request->tanggal_presensi;
        if (!Presensi::where('user_id', $userId)->where('tanggal_presensi', $tanggal)->where('status', 'Alpa')->exists()) {
            return back()->with('error', 'Tidak ada status Alpa pada tanggal tersebut');
        }
        if (Presensi::where('user_id', $userId)->where('tanggal_presensi', $tanggal)->where('approval_status', 'pending')->exists()) {
            return back()->with('error', 'Anda sudah memiliki permintaan approval untuk tanggal ini');
        }
        try {
            $buktiPath = $request->hasFile('bukti_foto') ? $request->file('bukti_foto')->store('uploads/presensi/approval', 'public') : null;
            Presensi::where('user_id', $userId)->where('tanggal_presensi', $tanggal)->where('status', 'Alpa')->update(['requested_status' => $request->requested_status, 'keterangan' => $request->keterangan, 'bukti_foto' => $buktiPath, 'approval_status' => 'pending']);
            return back()->with('success', 'Permintaan perubahan status berhasil dikirim. Menunggu approval admin.');
        } catch (\Exception $e) {
            \Log::error('Request approval error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengirim permintaan. Silakan coba lagi.');
        }
    }

    /**
     * Admin memproses permintaan approve/reject
     */
    public function processApproval(Request $request, $presensiId)
    {
        if (!isAdmin()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk melakukan approval');
        }
        $request->validate(['action' => 'required|in:approve,reject', 'notes' => 'nullable|string|max:255']);
        $presensi = Presensi::findOrFail($presensiId);
        if ($presensi->approval_status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya');
        }
        try {
            $isApproved = $request->action === 'approve';
            $newStatus = $isApproved ? $presensi->requested_status : 'Alpa';
            $statusId = PresensiStatus::where('status', $newStatus)->value('id');
            Presensi::where('user_id', $presensi->user_id)->where('tanggal_presensi', $presensi->tanggal_presensi)->where('approval_status', 'pending')
                ->update(['status' => $newStatus, 'presensi_status_id' => $statusId, 'approval_status' => $isApproved ? 'approved' : 'rejected', 'approval_notes' => $request->notes, 'approved_by' => Auth::id(), 'approved_at' => now()]);
            return back()->with('success', 'Permintaan perubahan status ' . ($isApproved ? 'disetujui' : 'ditolak'));
        } catch (\Exception $e) {
            Log::error('Approval process error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses approval. Silakan coba lagi.');
        }
    }

    /**
     * DataTables untuk approval pending
     */
    public function approvalData()
    {
        if (!isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $data = Presensi::with(['user.sekolah'])->where('approval_status', 'pending')->orderBy('updated_at', 'desc');
        // ... (Logika DataTables dari file original bisa dimasukkan di sini jika ada kustomisasi)
        return DataTables::of($data)->make(true);
    }

    /**
     * DataTables untuk histori approval
     */
    public function approvalHistory()
    {
        if (!isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $data = Presensi::with(['user', 'approvedBy'])->whereIn('approval_status', ['approved', 'rejected'])->orderBy('approved_at', 'desc');
        // ... (Logika DataTables dari file original bisa dimasukkan di sini jika ada kustomisasi)
        return DataTables::of($data)->make(true);
    }

    /**
     * Mendapatkan statistik untuk dashboard
     */
    public function getStats(Request $request)
    {
        if (!isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $today = now()->toDateString();
        try {
            $stats = [
                'hadir' => Presensi::whereDate('tanggal_presensi', $today)->whereIn('status', ['Tepat Waktu', 'Terlambat', 'Sangat Terlambat', 'Terlalu Awal'])->distinct('user_id')->count(),
                'terlambat' => Presensi::whereDate('tanggal_presensi', $today)->whereIn('status', ['Terlambat', 'Sangat Terlambat'])->distinct('user_id')->count(),
                'izin' => Presensi::whereDate('tanggal_presensi', $today)->where('status', 'Izin')->distinct('user_id')->count(),
                'sakit' => Presensi::whereDate('tanggal_presensi', $today)->where('status', 'Sakit')->distinct('user_id')->count(),
                'alpa' => Presensi::whereDate('tanggal_presensi', $today)->where('status', 'Alpa')->distinct('user_id')->count(),
                'pending' => Presensi::where('approval_status', 'pending')->count()
            ];
            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error getting stats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load stats'], 500);
        }
    }

    /**
     * Mendapatkan daftar sekolah untuk filter
     */
    public function getSekolahList()
    {
        return response()->json(Sekolah::select('id', 'nama')->orderBy('nama')->get());
    }

    private function getRekapAbsensiSiswa($monthString, Request $request)
    {
        $startDate = Carbon::createFromFormat('Y-m', $monthString)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $totalHariKerja = $startDate->diffInWeekdays($endDate);

        // Menggunakan whereHas yang lebih aman dan sesuai dengan Eloquent
        $presensiBulanan = Presensi::with('user') // Eager load relasi user
            ->whereBetween('tanggal_presensi', [$startDate, $endDate])
            ->whereHas('user', function ($query) use ($request) {
                $query->where('group_id', 4);

                if ($request->filled('filter_sekolah')) {
                    $query->where('sekolah_id', $request->filter_sekolah);
                }
            })
            ->get();

        $allSiswaQuery = User::where('group_id', 4);
        if ($request->filled('filter_sekolah')) {
            $allSiswaQuery->where('sekolah_id', $request->filter_sekolah);
        }
        $allSiswa = $allSiswaQuery->pluck('name', 'id');

        $rekap = $presensiBulanan
            ->groupBy('user_id')
            ->map(function ($presensiUser) use ($totalHariKerja) {
                $harian = $presensiUser->groupBy('tanggal_presensi')
                    ->map(fn($h) => PresensiHelper::hitungStatusHarianFromCollection($h));

                $summary = $harian->countBy();

                $hadir = ($summary['hadir'] ?? 0) + ($summary['telat'] ?? 0);
                $sakit = $summary['sakit'] ?? 0;
                $izin = $summary['izin'] ?? 0;

                return [
                    'nama' => $presensiUser->first()->user->name,
                    'hadir' => $hadir,
                    'sakit' => $sakit,
                    'izin'  => $izin,
                    'tidak_hadir'  => max(0, $totalHariKerja - ($hadir + $sakit + $izin)),
                ];
            });

        $siswaTanpaPresensi = $allSiswa->diffKeys($rekap);
        foreach ($siswaTanpaPresensi as $id => $name) {
            $rekap[$id] = [
                'nama' => $name,
                'hadir' => 0,
                'sakit' => 0,
                'izin' => 0,
                'tidak_hadir' => $totalHariKerja
            ];
        }
        return $rekap->sortBy('nama')->values()->toArray();
    }
    /**
     * Menerapkan filter dari request ke query builder.
     */
    private function applyFiltersToQuery($query, Request $request)
    {
        // === LOGIKA FILTER TANGGAL YANG DIPERBAIKI ===
        // Prioritaskan filter_bulan. Jika ada, abaikan filter_tanggal.
        if ($request->filled('filter_bulan')) {
            try {
                $date = Carbon::parse($request->filter_bulan);
                $query->whereYear('presensi.tanggal_presensi', $date->year)
                    ->whereMonth('presensi.tanggal_presensi', $date->month);
            } catch (\Exception $e) {
                Log::error('Invalid date format for filter_bulan: ' . $request->filter_bulan);
            }
        }
        // Jika filter_bulan tidak diisi, baru jalankan filter_tanggal
        elseif ($request->filled('filter_tanggal')) {
            match ($request->filter_tanggal) {
                'today'     => $query->whereDate('presensi.tanggal_presensi', today()),
                'yesterday' => $query->whereDate('presensi.tanggal_presensi', today()->subDay()),
                'week'      => $query->whereBetween('presensi.tanggal_presensi', [today()->subDays(6), today()]),
                'month'     => $query->whereBetween('presensi.tanggal_presensi', [today()->subDays(29), today()]),
                default     => null,
            };
        }

        if ($request->filled('filter_status')) {
            $query->where('presensi.status', $request->filter_status);
        }
        if ($request->filled('filter_sesi')) {
            $query->where('presensi.sesi', $request->filter_sesi);
        }
        if ($request->filled('filter_approval')) {
            if ($request->filter_approval === 'none') {
                $query->whereNull('presensi.approval_status');
            } else {
                $query->where('presensi.approval_status', $request->filter_approval);
            }
        }
        if ($request->filled('filter_sekolah')) {
            $query->where('user.sekolah_id', $request->filter_sekolah);
        }

        if ($request->filled('filter_approval')) {
            if ($request->filter_approval === 'pending_all') {
                // Gabungkan dua status pending menjadi satu filter
                $query->whereIn('presensi.approval_status', ['pending', 'pending_update']);
            } else {
                $query->where('presensi.approval_status', $request->filter_approval);
            }
        }

        // === PENCARIAN GLOBAL ===
        if ($request->input('search.value')) {
            $search = $request->input('search.value');
            // Grouping 'OR' condition for search
            $query->where(function ($q) use ($search) {
                $q->where('user.name', 'like', "%{$search}%")
                    ->orWhere('sekolah.nama', 'like', "%{$search}%")
                    ->orWhere('presensi.status', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Membuat HTML untuk kolom aksi di DataTables.
     */
    private function renderAksiColumn(Presensi $row)
    {
        $actions = '<div class="btn-group btn-group-sm table-actions" role="group">';
        if ($row->status === 'Alpa' && Auth::id() == $row->user_id && !$row->approval_status) {
            $actions .= '<button class="btn btn-outline-warning" onclick="requestApprovalForDate(\'' . $row->tanggal_presensi . '\')" title="Request Perubahan"><i class="fas fa-edit"></i></button>';
        }
        if ($row->approval_status === 'pending' && isAdmin()) {
            $actions .= '<button class="btn btn-outline-success" onclick="processQuickApproval(' . $row->id . ', \'approve\')" title="Setujui"><i class="fas fa-check"></i></button>';
            $actions .= '<button class="btn btn-outline-danger" onclick="processQuickApproval(' . $row->id . ', \'reject\')" title="Tolak"><i class="fas fa-times"></i></button>';
        }
        if (isAdmin()) {
            $actions .= '<button class="btn btn-outline-primary" onclick="editPresensi(' . $row->id . ')" title="Edit"><i class="fas fa-pencil-alt"></i></button>';
        }
        $actions .= '</div>';
        return ($actions === '<div class="btn-group btn-group-sm table-actions" role="group"></div>') ? '-' : $actions;
    }

    /**
     * Membuat HTML untuk badge status presensi.
     */
    private function renderStatusBadge($row): string
    {
        if ($row->approval_status === 'pending') {
            return '<span class="badge bg-warning">' . e($row->requested_status) . ' (Menunggu)</span>';
        }
        $status = $row->status ?? '-';
        $map = ['Tepat Waktu' => 'success', 'Terlambat' => 'warning', 'Sangat Terlambat' => 'danger', 'Terlalu Awal' => 'info', 'Izin' => 'primary', 'Sakit' => 'secondary', 'Alpa' => 'danger'];
        return '<span class="badge bg-' . ($map[$status] ?? 'light') . '">' . e($status) . '</span>';
    }

    /**
     * Membuat HTML untuk badge status approval.
     */
    private function renderApprovalBadge($row): string
    {
        if (!$row->approval_status) {
            return '<span class="badge bg-light text-muted">-</span>';
        }
        $map = ['pending' => ['class' => 'warning', 'text' => 'Menunggu'], 'approved' => ['class' => 'success', 'text' => 'Disetujui'], 'rejected' => ['class' => 'danger', 'text' => 'Ditolak']];
        $config = $map[$row->approval_status] ?? ['class' => 'secondary', 'text' => ucfirst($row->approval_status)];
        return '<span class="badge bg-' . $config['class'] . '">' . e($config['text']) . '</span>';
    }

    // Helper-helper lain dari file original
    private function getStatusPresensiHariIni($presensiHariIni, $setting)
    {
        $now           = now();
        $currentTime   = $now->format('H:i');
        $pagiData      = $presensiHariIni->where('sesi', 'pagi')->first();
        $soreData      = $presensiHariIni->where('sesi', 'sore')->first();

        $status = [
            'can_presensi'    => false,
            'current_session' => null,
            'message'         => '',
            'pagi_status'     => $pagiData?->status,
            'sore_status'     => $soreData?->status,
            'pagi_jam'        => $pagiData?->jam_presensi,
            'sore_jam'        => $soreData?->jam_presensi,
        ];

        if (!$setting) {
            $status['message'] = 'Pengaturan presensi belum dikonfigurasi';
            return $status;
        }

        if ($currentTime >= $setting->pagi_mulai && $currentTime < $setting->sore_mulai) {
            $status['current_session'] = 'pagi';
            $status['can_presensi'] = !$pagiData;
            $status['message'] = $pagiData
                ? "Presensi pagi sudah dilakukan ({$pagiData->status})"
                : 'Silakan lakukan presensi pagi';
        } elseif ($currentTime >= $setting->sore_mulai && $currentTime <= $setting->sore_selesai) {
            $status['current_session'] = 'sore';
            $status['can_presensi'] = !$soreData;
            $status['message'] = $soreData
                ? "Presensi sore sudah dilakukan ({$soreData->status})"
                : 'Silakan lakukan presensi sore';
        } else {
            $status['message'] = 'Waktu presensi sudah berakhir untuk hari ini';
        }

        return $status;
    }

    public function exportExcel(Request $request)
    {
        try {
            // --- Logika dari getFiltersFromRequest digabung langsung ke sini ---
            $bulan = $request->input('bulan', now()->month);
            $tahun = $request->input('tahun', now()->year);

            // Membuat variabel $monthString dan $bulanTeks secara langsung
            $monthString = sprintf('%04d-%02d', $tahun, $bulan);
            $bulanTeks = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
            // ----------------------------------------------------------------

            // Menggunakan $monthString yang sudah pasti terdefinisi
            $rekapData = $this->getRekapAbsensiSiswa($monthString, $request);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Menggunakan $bulanTeks yang sudah pasti terdefinisi
            $sheet->setCellValue('A1', 'REKAP ABSENSI SISWA')->mergeCells('A1:E1');
            $sheet->setCellValue('A2', strtoupper($bulanTeks))->mergeCells('A2:E2');
            $sheet->fromArray(['NAMA SISWA', 'Hadir', 'Sakit', 'Izin', 'TK (Tanpa Keterangan)'], null, 'A4');

            $rowData = array_map(fn($siswa) => [$siswa['nama'], $siswa['hadir'], $siswa['sakit'], $siswa['izin'], $siswa['tidak_hadir']], $rekapData);
            $sheet->fromArray($rowData, null, 'A5');

            $lastRow = count($rekapData) + 4;
            $sheet->getStyle('A1:E4')->getFont()->setBold(true);
            $sheet->getStyle('A4:E' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            foreach (range('A', 'E') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
            $sheet->getColumnDimension('A')->setWidth(35);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Rekap_Absensi_' . str_replace(' ', '_', $bulanTeks) . '.xlsx';

            return response()->streamDownload(fn() => $writer->save('php://output'), $filename);
        } catch (\Exception $e) {
            // Tampilkan pesan error yang lebih detail saat development
            Log::error('Export Excel error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return back()->with('error', 'Gagal membuat file Excel: ' . $e->getMessage());
        }
    }

    public function exportPDF(Request $request)
    {
        try {
            $bulan = $request->input('bulan', now()->month);
            $tahun = $request->input('tahun', now()->year);
            $monthString = sprintf('%04d-%02d', $tahun, $bulan);
            $bulanTeks = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');

            $rekapData = $this->getRekapAbsensiSiswa($monthString, $request);

            $totals = [
                'totalHadir' => array_sum(array_column($rekapData, 'hadir')),
                'totalSakit' => array_sum(array_column($rekapData, 'sakit')),
                'totalIzin'  => array_sum(array_column($rekapData, 'izin')),
                'totalTK'    => array_sum(array_column($rekapData, 'tidak_hadir')),
            ];

            $pdf = PDF::loadView('administrator.presensi.exports.rekap_pdf', array_merge(
                ['rekapData' => $rekapData, 'bulanTeks' => $bulanTeks],
                $totals
            ));

            $filename = 'Rekap_Absensi_' . str_replace(' ', '_', $bulanTeks) . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Export PDF error: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat file PDF: ' . $e->getMessage());
        }
    }
    public function generateAlpa()
    {
        if (!isAdmin()) {
            return back()->with('error', 'Hanya admin yang bisa menjalankan fitur ini.');
        }
        $today = today()->toDateString();
        $presentStudentIds = Presensi::where('tanggal_presensi', $today)->distinct()->pluck('user_id');
        $absentStudentIds = User::where('group_id', 4)->whereNotIn('id', $presentStudentIds)->pluck('id');
        if ($absentStudentIds->isEmpty()) {
            return back()->with('info', 'Semua siswa sudah melakukan presensi hari ini.');
        }
        $alpaStatusId = PresensiStatus::where('status', 'Alpa')->value('id');
        $dataToInsert = [];
        $now = now();
        foreach ($absentStudentIds as $userId) {
            foreach (['pagi', 'sore'] as $sesi) {
                $dataToInsert[] = ['user_id' => $userId, 'tanggal_presensi' => $today, 'sesi' => $sesi, 'status' => 'Alpa', 'presensi_status_id' => $alpaStatusId, 'keterangan' => 'Generated by system', 'created_at' => $now, 'updated_at' => $now,];
            }
        }
        if (!empty($dataToInsert)) {
            Presensi::insert($dataToInsert);
        }
        return back()->with('success', "Berhasil generate status Alpa untuk " . $absentStudentIds->count() . " siswa.");
    }
    private function processBase64Image(string $imageData): ?string
    { /* ... (Logika dari file original) ... */
        return base64_decode(explode(',', $imageData, 2)[1]);
    }
    private function getStatusByTime(string $jamPresensi, string $sesi, $setting)
    {
        return PresensiHelper::getStatusByTime($jamPresensi, $sesi, $setting);
    }
}
