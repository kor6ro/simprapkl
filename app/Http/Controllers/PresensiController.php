<?php

namespace App\Http\Controllers;

use App\Helpers\PresensiHelper;
use App\Models\Presensi;
use App\Models\PresensiSetting;
use App\Models\PresensiStatus;
use App\Models\User;
use App\Notifications\SiswaMengajukanIzin;
use App\Notifications\SiswaDinyatakanAlpa;
use App\Notifications\KonfirmasiPresensiBerhasil;
use App\Notifications\HasilApprovalIzin;
use App\Notifications\SiswaMengajukanUlangIzin;
use App\Models\Sekolah;
use App\Models\PeriodePkl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use PDF;



class PresensiController extends Controller
{

public function index()
    {
        $user = Auth::user();
        
        $statusPresensi = [
            'can_presensi' => false,
            'message' => 'Hanya siswa yang dapat melakukan presensi.'
        ];

        if ($user->group_id == 4) {
            $setting = PresensiSetting::first();

            $presensiHariIni = Presensi::where('user_id', $user->id)
                ->whereDate('presensi_at', now())
                ->get();

            $statusPresensi = $this->getStatusPresensiHariIni($presensiHariIni, $setting);
        }

        $periodePkls = PeriodePkl::orderBy('awal_periode', 'desc')->get();

        return view('administrator.presensi.index', compact('statusPresensi', 'periodePkls'));
    }

    public function create()
    {
        $users = User::where('group_id', 4)->orderBy('name')->get();
        $presensiStatus = PresensiStatus::orderBy('id')->get();
        return view('administrator.presensi.create', compact('users', 'presensiStatus'));
    }

    /**
     * Menampilkan form untuk mengedit data presensi.
     */
    public function edit(Presensi $presensi)
    {

        $presensiStatus = PresensiStatus::orderBy('id')->get();

        return view('administrator.presensi.edit', compact('presensi', 'presensiStatus'));
    }

    /**
     * Menyimpan data presensi baru yang diinput manual oleh admin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:user,id',
            'tanggal_presensi' => 'required|date',
            'jam_presensi' => 'nullable|date_format:H:i', // Validasi untuk input jam
            'sesi' => 'required|in:pagi,sore',
            'status' => 'required|string|exists:presensi_status,status',
            'keterangan' => 'nullable|string',
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if (Presensi::where('user_id', $request->user_id)
            ->whereDate('presensi_at', $request->tanggal_presensi)
            ->where('sesi', $request->sesi)
            ->exists()
        ) {
            return back()->with('error', 'Data presensi untuk siswa, tanggal, dan sesi yang sama sudah ada.')->withInput();
        }

        $buktiPath = $request->hasFile('bukti_foto') ? $request->file('bukti_foto')->store('uploads/presensi', 'public') : null;
        $statusModel = PresensiStatus::where('status', $request->status)->firstOrFail();

        // Logika penggabungan tanggal dan jam yang benar
        $presensi_at = null;
        if ($request->filled('tanggal_presensi') && $request->filled('jam_presensi')) {
            $presensi_at = Carbon::parse($request->tanggal_presensi . ' ' . $request->jam_presensi);
        } elseif ($request->filled('tanggal_presensi')) {
            $presensi_at = Carbon::parse($request->tanggal_presensi);
        }

        Presensi::create([
            'user_id' => $request->user_id,
            'presensi_at' => $presensi_at,
            'sesi' => $request->sesi,
            'status' => $statusModel->status,
            'presensi_status_id' => $statusModel->id,
            'keterangan' => $request->keterangan,
            'bukti_foto' => $buktiPath,
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        return redirect()->route('presensi.index')->with('success', 'Data presensi berhasil ditambahkan.');
    }

    /**
     * Mengupdate data presensi. Logika ini membedakan antara admin dan siswa (dengan grace period).
     */
    public function update(Request $request, Presensi $presensi)
    {
        $request->validate([
            'tanggal_presensi' => 'required|date',
            'jam_presensi' => 'nullable|date_format:H:i',
            'sesi' => 'required|in:pagi,sore',
            'status' => 'required|string|exists:presensi_status,status',
            'keterangan' => 'nullable|string',
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = auth()->user();

        if ($user->group_id > 2) {
            return redirect()->route('presensi.index')->with('error', 'Anda tidak memiliki izin untuk mengubah data ini.');
        }
        $presensi_at = null;
        if ($request->filled('tanggal_presensi') && $request->filled('jam_presensi')) {
            $presensi_at = Carbon::parse($request->tanggal_presensi . ' ' . $request->jam_presensi);
        } elseif ($request->filled('tanggal_presensi')) {
            $presensi_at = Carbon::parse($request->tanggal_presensi);
        }

        $statusModel = PresensiStatus::where('status', $request->status)->firstOrFail();
        $data = [
            'presensi_at' => $presensi_at,
            'sesi' => $request->sesi,
            'status' => $statusModel->status,
            'presensi_status_id' => $statusModel->id,
            'keterangan' => $request->keterangan,
            'approval_status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'requested_status' => null,
        ];

        // 3. Handle upload foto jika ada
        if ($request->hasFile('bukti_foto')) {
            // Hapus foto lama jika ada
            if ($presensi->bukti_foto) {
                Storage::disk('public')->delete($presensi->bukti_foto);
            }
            // Simpan foto baru
            $data['bukti_foto'] = $request->file('bukti_foto')->store('uploads/presensi', 'public');
        }

        $presensi->update($data);

        return redirect()->route('presensi.index')->with('success', 'Data presensi berhasil diperbarui oleh Admin.');
    }
    /**
     * Menghapus data presensi.
     */
    public function destroy(Presensi $presensi)
    {
        try {
            if ($presensi->bukti_foto) {
                Storage::disk('public')->delete($presensi->bukti_foto);
            }
            $presensi->delete();
            return redirect()->route('presensi.index')->with('success', 'Data presensi berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus presensi: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus data.');
        }
    }

    public function PresensiCamera(Request $request)
    {
        $request->validate(['image_data' => 'required|string']);

        $user = Auth::user()->group_id == 4 ? Auth::user() : null;
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Hanya siswa yang dapat melakukan presensi.'], 403);
        }

        $today = now()->toDateString();
        $now = now();
        $setting = PresensiSetting::firstOrFail();

        $sesi = PresensiHelper::getCurrentSession($setting);

        if (!$sesi) {
            return response()->json(['success' => false, 'message' => 'Presensi hanya dapat dilakukan pada jam kerja yang ditentukan.'], 422);
        }

        if (Presensi::where('user_id', $user->id)->whereDate('presensi_at', $today)->where('sesi', $sesi)->exists()) {
            return response()->json(['success' => false, 'message' => "Anda sudah melakukan presensi {$sesi} hari ini."], 422);
        }

        $path = null;
        try {
            $path = PresensiHelper::storeBase64Image($request->image_data, $user->id);

            // Ambil timestamp saat ini
            $waktuSekarang = now();

            $statusKode = PresensiHelper::getStatusByTime($waktuSekarang->format('H:i:s'), $sesi, $setting);
            $statusModel = PresensiStatus::where('kode', $statusKode)->firstOrFail();

        $presensiRecord = Presensi::create([
            'user_id' => $user->id,
            'presensi_at' => $waktuSekarang,
            'sesi' => $sesi,
            'status' => $statusModel->status,
            'presensi_status_id' => $statusModel->id,
            'bukti_foto' => $path,
            'keterangan' => "Presensi {$sesi} via kamera",
        ]);

        $user->notify(new KonfirmasiPresensiBerhasil($presensiRecord));

            return response()->json(['success' => true, 'message' => "Presensi {$sesi} berhasil! Status: {$statusModel->status}"]);
        } catch (\Exception $e) {
            // Jika terjadi error, hapus gambar yang mungkin sudah terlanjur disimpan
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            Log::error('Camera presensi error untuk user: ' . $user->id, ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal. Gagal menyimpan presensi.'], 500);
        }
    }

    public function submitAbsenceRequest(Request $request)
    {
        $rules = [
            'jenis' => 'required|in:IZIN_TERENCANA,IZIN_MENDESAK,SAKIT',
            'durasi' => 'required|in:FULL_DAY,PAGI_ONLY,SORE_ONLY',
            'keterangan' => 'required|string|min:20|max:255',
            'bukti_foto' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ];

        if ($request->jenis == 'IZIN_TERENCANA') {
            $rules['tanggal_mulai'] = 'required|date|after_or_equal:today';
            $rules['tanggal_selesai'] = 'required|date|after_or_equal:tanggal_mulai';
        } else {
            $rules['tanggal_single'] = 'required|date';
        }
        $request->validate($rules);

         $user = Auth::user();
        $buktiPath = null;
        try {
            DB::beginTransaction();
            $statusModel = PresensiStatus::where('kode', $request->jenis)->firstOrFail();
            $buktiPath = $request->file('bukti_foto')->store('uploads/presensi/absences', 'public');

            $targetSessions = ($request->durasi == 'PAGI_ONLY') ? ['pagi'] : (($request->durasi == 'SORE_ONLY') ? ['sore'] : ['pagi', 'sore']);
            $dates = ($request->jenis == 'IZIN_TERENCANA') ? Carbon::parse($request->tanggal_mulai)->toPeriod($request->tanggal_selesai) : [Carbon::parse($request->tanggal_single)];

            $firstPresensiRecord = null; // Variabel untuk menyimpan record pertama

            foreach ($dates as $date) {
                $tanggal = $date->toDateString();
                if ($date->isWeekend()) continue;

                foreach ($targetSessions as $sesi) {
                    $payload = [
                        'status' => $statusModel->status,
                        'presensi_status_id' => $statusModel->id,
                        'bukti_foto' => $buktiPath,
                        'keterangan' => $request->keterangan,
                        'approval_status' => 'pending',
                        'requested_status' => $statusModel->status,
                        'presensi_at' => $date->copy()->setTimeFrom(now()),
                    ];

                    $existing = Presensi::where('user_id', $user->id)
                        ->where('sesi', $sesi)
                        ->whereDate('presensi_at', $tanggal)
                        ->first();

                    if ($existing) {
                        $existing->update($payload);
                        if (!$firstPresensiRecord) $firstPresensiRecord = $existing->fresh(); // Simpan record pertama
                    } else {
                        $newRecord = Presensi::create(array_merge(['user_id' => $user->id, 'sesi' => $sesi], $payload));
                        if (!$firstPresensiRecord) $firstPresensiRecord = $newRecord; // Simpan record pertama
                    }
                }
            }

            if ($firstPresensiRecord) {
                $penerimaNotifikasi = User::whereIn('group_id', [1, 2, 5, 6, 7])->get();
                Notification::send($penerimaNotifikasi, new SiswaMengajukanIzin($user, $firstPresensiRecord));
            }
            
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Pengajuan berhasil dikirim dan menunggu persetujuan.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // [PERBAIKAN 4] Tangani error validasi secara spesifik
            DB::rollBack();
            if ($buktiPath) Storage::disk('public')->delete($buktiPath);
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($buktiPath) Storage::disk('public')->delete($buktiPath);
            Log::error('Submit Absence Request error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal. Silakan coba lagi.'], 500);
        }
    }

public function processApproval(Request $request, Presensi $presensi)
{
    $request->validate(['action' => 'required|in:approve,reject', 'notes' => 'nullable|string|max:255']);
    try {
        DB::beginTransaction();
        
        if ($presensi->approval_status !== 'pending') {
            throw new \Exception('Permintaan ini sudah diproses sebelumnya.');
        }
        
        $isApproved = $request->action == 'approve';
        $newApprovalStatus = $isApproved ? 'approved' : 'rejected';
        
        $statusModel = PresensiStatus::where('status', $presensi->requested_status)->first() ?? PresensiStatus::where('kode', 'ALPA')->firstOrFail();

        $relatedPresensi = collect();
        if ($presensi->bukti_foto) {
            $relatedPresensi = Presensi::where('user_id', $presensi->user_id)
                ->where('bukti_foto', $presensi->bukti_foto)
                ->where('approval_status', 'pending') 
                ->get();
        }
        
        // Fallback jika tidak ada (seharusnya tidak terjadi, tapi untuk aman)
        if ($relatedPresensi->isEmpty()) {
             $relatedPresensi = Presensi::where('user_id', $presensi->user_id)
                ->whereDate('presensi_at', $presensi->presensi_at->toDateString())
                ->where('approval_status', 'pending')->get();
        }

        // Update semua data yang terkait
        foreach ($relatedPresensi as $record) {
            $record->update([
                'status' => $statusModel->status,
                'presensi_status_id' => $statusModel->id,
                'approval_status' => $newApprovalStatus,
                'approval_notes' => $request->notes,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'requested_status' => null
            ]);
        }
                
        $siswa = $presensi->user;
        if ($siswa) {
            $siswa->notify(new HasilApprovalIzin($presensi, $isApproved));
        }

        DB::commit();
        $message = "Pengajuan {$presensi->requested_status} (dan " . ($relatedPresensi->count() - 1) . " data terkait) telah " . ($isApproved ? 'disetujui' : 'ditolak');
        return response()->json(['success' => true, 'message' => $message]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Approval process error: ' . $e->getMessage());
        return response()->json(['error' => true, 'message' => 'Gagal memproses approval: ' . $e->getMessage()], 500);
    }
}

public function dataUnified(Request $request)
{
    $query = Presensi::query()
        ->join('user', 'presensi.user_id', '=', 'user.id')
        ->with(['user.sekolah', 'presensiStatus'])
        ->select('presensi.*');

    $user = Auth::user();

    if ($user->group_id == 4) { // Jika Siswa
        $query->where('presensi.user_id', $user->id);
    } elseif ($user->group_id == 3) { // Jika Pembimbing Sekolah
        $query->whereHas('user', function ($q) use ($user) {
            $q->where('sekolah_id', $user->sekolah_id)
              ->where('program_keahlian_id', $user->program_keahlian_id);
        });
    }

    $this->applyFiltersToQuery($query, $request);

    // [MODIFIKASI] Buat instance DataTables tanpa 'action' dulu
    $dataTable = DataTables::of($query)
        ->addIndexColumn()
        ->addColumn('nama', fn($row) => $row->user?->name ?? '-')
        ->addColumn('sekolah', fn($row) => $row->user?->sekolah?->nama ?? '-')
        ->addColumn('tanggal', fn($row) => $row->presensi_at ? \Carbon\Carbon::parse($row->presensi_at)->format('d/m/Y') : '-')
        ->addColumn('sesi_badge', fn($row) => '<span class="badge bg-' . ($row->sesi == 'pagi' ? 'info' : 'warning') . '">' . ucfirst($row->sesi) . '</span>')
        ->addColumn('jam', fn($row) => $row->presensi_at ? \Carbon\Carbon::parse($row->presensi_at)->format('H:i') : '-')
        ->addColumn('status_badge', fn($row) => PresensiHelper::renderStatusBadge($row))
        ->addColumn('approval_badge', fn($row) => PresensiHelper::renderApprovalBadge($row))
        ->addColumn('keterangan', fn($row) => $row->keterangan)
        ->addColumn('bukti_foto', fn($row) => $row->bukti_foto ? '<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#imageViewerModal" data-image-url="' . asset('storage/' . $row->bukti_foto) . '"><i class="fa fa-eye"></i></button>' : '-')
        ->rawColumns(['sesi_badge', 'status_badge', 'approval_badge', 'bukti_foto']); // 'action' dihapus sementara

    // =================================================================
    // [MODIFIKASI] Logika Optimasi yang Diperbaiki
    // =================================================================
    
    // 1. Ambil data yang sudah difilter/dipaginasi oleh Yajra
    // Kita clone query yang sudah difilter (termasuk limit/offset)
    $filteredData = $dataTable->getFilteredQuery()->get();

    // 2. Kumpulkan batch keys HANYA dari data di halaman ini
    $batchKeys = [];
    foreach ($filteredData as $row) {
        if (in_array($row->approval_status, ['pending', 'pending_update']) && $row->bukti_foto) {
            $batchKeys[$row->bukti_foto] = $row->user_id;
        }
    }

    // 3. Jalankan SATU query untuk menemukan semua ID "utama"
    $primaryIds = collect();
    if (!empty($batchKeys)) {
        $queryBuilder = DB::table('presensi')->selectRaw('MAX(id) as id');
        
        $first = true;
        foreach ($batchKeys as $bukti_foto => $user_id) {
            $clause = $first ? 'where' : 'orWhere';
            $queryBuilder->$clause(function($q) use ($user_id, $bukti_foto) {
                $q->where('user_id', $user_id)
                  ->where('bukti_foto', $bukti_foto);
            });
            $first = false;
        }
        // Pastikan hanya ambil dari data 'pending'
        $primaryIds = $queryBuilder->whereIn('approval_status', ['pending', 'pending_update']) 
                                   ->groupBy('user_id', 'bukti_foto')
                                   ->pluck('id');
    }

    return $dataTable->addColumn('action', function($row) use ($user, $primaryIds) {
        $actions = '';

        if (in_array($user->group_id, [1, 2, 5, 6, 7])) {
            
            if (in_array($row->approval_status, ['pending', 'pending_update']) && $row->bukti_foto) {
                
                if ($primaryIds->contains($row->id)) {
                    $actions .= '<button class="btn btn-success btn-sm me-1 action-btn" data-action="approve" data-id="'.$row->id.'" title="Setujui (Semua Sesi Terkait)"><i class="fa fa-check"></i></button>';
                    $actions .= '<button class="btn btn-secondary btn-sm me-1 action-btn" data-action="reject" data-id="'.$row->id.'" title="Tolak (Semua Sesi Terkait)"><i class="fa fa-times"></i></button>';
                } else {
                    $actions .= '<span class="text-muted" title="Tindakan ada di baris pertama pengajuan ini">(terkait)</span>';
                }
                
                 $actions .= '<a href="'.url('presensi').'/'.$row->id.'/edit" class="btn btn-warning btn-sm me-1" title="Edit"><i class="fa fa-edit"></i></a>';
                 $actions .= '<button class="btn btn-danger btn-sm action-btn" data-action="delete" data-id="'.$row->id.'" title="Hapus"><i class="fa fa-trash"></i></button>';

            } else {
                 $actions .= '<a href="'.url('presensi').'/'.$row->id.'/edit" class="btn btn-warning btn-sm me-1" title="Edit"><i class="fa fa-edit"></i></a>';
                 $actions .= '<button class="btn btn-danger btn-sm action-btn" data-action="delete" data-id="'.$row->id.'" title="Hapus"><i class="fa fa-trash"></i></button>';
            }

        } 
        elseif ($user->group_id == 4 && $row->user_id == $user->id) {
            if (in_array($row->approval_status, ['pending', 'rejected'])) {
                
                if ($primaryIds->contains($row->id) || !$row->bukti_foto) {
                     $actions .= '<a href="'.route('presensi.edit_absence', $row->id).'" class="btn btn-warning btn-sm me-1" title="Edit Pengajuan"><i class="fa fa-edit"></i> Edit</a>';
                } else {
                     $actions .= '<span class="text-muted" title="Edit melalui baris pertama pengajuan">(terkait)</span>';
                }
            }
        }
        
        return $actions ?: '-';
    })
    // [MODIFIKASI TAMBAHAN]
    ->editColumn('approval_badge', function($row) use ($primaryIds) {
        $baseBadge = PresensiHelper::renderApprovalBadge($row);
        // Cek apakah ini batch pending
        if (in_array($row->approval_status, ['pending', 'pending_update']) && $row->bukti_foto) {
            // Jika ini BUKAN primary ID, tambahkan teks (Terkait)
            if (!$primaryIds->contains($row->id)) {
                 // Ini akan menambahkan teks kecil di bawah badge
                 return $baseBadge . ' <span class="text-muted d-block" style="font-size: 0.8em;">(Terkait)</span>';
            }
        }
        // Jika tidak, kembalikan badge aslinya
        return $baseBadge;
    })
    ->rawColumns(['sesi_badge', 'status_badge', 'approval_badge', 'bukti_foto', 'action']) 
    ->make(true);

}
    public function getSekolahList()
    {
        return response()->json(Sekolah::select('id', 'nama')->orderBy('nama')->get());
    }

    public function exportPDF(Request $request)
    {
        $reportType = $request->input('report_type', 'detail');
        if ($reportType == 'rekap') {
            return $this->exportPDFRekap($request);
        }
        return $this->exportPDFDetail($request);
    }

    private function exportPDFDetail(Request $request)
    {
        $availableColumns = [
            'sekolah' => 'Sekolah',
            'tanggal' => 'Tanggal',
            'sesi' => 'Sesi',
            'presensi_at' => 'Jam',
            'status' => 'Status',
            'approval_status' => 'Approval',
            'keterangan' => 'Keterangan',
        ];
        $selectedKeys = $request->input('columns', array_keys($availableColumns));
        $selectedColumns = array_filter($availableColumns, fn($key) => in_array($key, $selectedKeys), ARRAY_FILTER_USE_KEY);

        $query = Presensi::query()
            ->join('user', 'presensi.user_id', '=', 'user.id')
            ->with('user.sekolah');

        $this->applyFiltersToQuery($query, $request);

        $dataPresensi = $query
            ->select('presensi.*')
            ->orderBy('user.name', 'asc')
            ->orderBy('presensi.presensi_at', 'asc')
            ->get();

        $bulanTeks = $request->filled('filter_bulan') ? Carbon::parse($request->filter_bulan)->translatedFormat('F Y') : 'Semua Data';
        $namaSekolah = $request->filled('filter_sekolah') ? Sekolah::find($request->filter_sekolah)?->nama : 'Semua Sekolah';

        $pdfData = [
            'data' => $dataPresensi,
            'selectedColumns' => $selectedColumns,
            'judul' => 'Laporan Detail Presensi Siswa',
            'periode' => $bulanTeks,
            'sekolah' => $namaSekolah,
        ];

        $pdf = PDF::loadView('administrator.presensi.exports.rekap_pdf', $pdfData)->setPaper('a4', 'landscape');
        return $pdf->stream('Laporan_Detail_Presensi.pdf');
    }

    private function exportPDFRekap(Request $request)
    {
        $rekapData = $this->getRekapData($request);
        $bulanTeks = $request->filled('filter_bulan') ? Carbon::parse($request->filter_bulan)->translatedFormat('F Y') : 'Semua Data';
        $namaSekolah = $request->filled('filter_sekolah') ? Sekolah::find($request->filter_sekolah)?->nama : 'Semua Sekolah';

        $pdfData = [
            'rekapData' => $rekapData,
            'judul' => 'Laporan Rekapitulasi Presensi',
            'periode' => $bulanTeks,
            'sekolah' => $namaSekolah,
        ];

        $pdf = PDF::loadView('administrator.presensi.exports.rekap_presensi_pdf', $pdfData)->setPaper('a4', 'portrait');
        return $pdf->stream('Rekap_Presensi.pdf');
    }

    //===============================================
    // METHOD BANTUAN (PRIVATE HELPERS)
    //===============================================

    private function getRekapData(Request $request)
    {
        $query = Presensi::query()
            ->with('user:id,name', 'presensiStatus:id,kategori')
            ->join('user', 'presensi.user_id', '=', 'user.id');

        $this->applyFiltersToQuery($query, $request);
        $allPresensi = $query->get();

        return $allPresensi->groupBy('user_id')->map(function ($presensiUser) {
            // << PERUBAHAN PADA PENGELOMPOKAN >>
            $presensiPerHari = $presensiUser->groupBy(fn($item) => $item->presensi_at ? $item->presensi_at->toDateString() : null);

            $rekapHarian = ['hadir' => 0, 'telat' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];

            foreach ($presensiPerHari as $tanggal => $entriSatuHari) {
                if (is_null($tanggal)) continue; // Lewati jika tidak ada tanggal
                $statusHarian = PresensiHelper::hitungStatusHarianFromCollection($entriSatuHari);
                if (isset($rekapHarian[$statusHarian])) {
                    $rekapHarian[$statusHarian]++;
                }
            }

            return [
                'nama' => $presensiUser->first()->user->name,
                'hadir' => $rekapHarian['hadir'] + $rekapHarian['telat'],
                'sakit' => $rekapHarian['sakit'],
                'izin' => $rekapHarian['izin'],
                'alpa' => $rekapHarian['alpa'],
            ];
        })->sortBy('nama')->values();
    }

    private function applyFiltersToQuery($query, Request $request)
    {
        if ($request->filled('filter_bulan')) {
            try {
                $date = Carbon::parse($request->filter_bulan);
                $query->whereYear('presensi.presensi_at', $date->year)
                    ->whereMonth('presensi.presensi_at', $date->month);
            } catch (\Exception $e) {
                Log::warning('Filter bulan tidak valid: ' . $request->filter_bulan);
            }
        }
        if ($request->filled('filter_sekolah')) {
            $query->whereHas('user', fn($q) => $q->where('sekolah_id', $request->filter_sekolah));
        }

        if ($request->filled('filter_periode')) {
            $query->whereHas('user.periodePkl', function ($q) use ($request) {
                $q->where('periode_pkl.id', $request->filter_periode);
            });
        }

        if ($request->filled('filter_approval')) {
            $status = $request->filter_approval;
            if ($status == 'pending_all') {
                $query->whereIn('presensi.approval_status', ['pending', 'pending_update']);
            } elseif ($status == 'none') {
                $query->whereNull('presensi.approval_status');
            } else {
                $query->where('presensi.approval_status', $status);
            }
        }

        if ($request->input('search.value')) {
            $search = $request->input('search.value');
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }
    }

    private function getStatusPresensiHariIni($presensiHariIni, $setting)
    {
        $status = [
            'can_presensi' => false,
            'current_session' => null,
            'message' => 'Waktu presensi telah berakhir untuk hari ini.'
        ];

        if (!$setting) {
            $status['message'] = 'Pengaturan presensi belum dikonfigurasi.';
            return $status;
        }

        $now = now();

        $pagiMulai = Carbon::parse($setting->pagi_mulai);
        $soreMulai = Carbon::parse($setting->sore_mulai);
        $soreSelesai = Carbon::parse($setting->sore_selesai);

        $pagiData = $presensiHariIni->where('sesi', 'pagi')->first();
        $soreData = $presensiHariIni->where('sesi', 'sore')->first();

        if ($now->isBetween($pagiMulai, $soreMulai->copy()->subSecond())) {
            $status['current_session'] = 'pagi';
            $status['can_presensi'] = !$pagiData;
            $status['message'] = $pagiData ? "Presensi pagi sudah dilakukan ({$pagiData->status})" : 'Silakan lakukan presensi pagi.';
        } elseif ($now->isBetween($soreMulai, $soreSelesai)) {
            $status['current_session'] = 'sore';
            $status['can_presensi'] = !$soreData;
            $status['message'] = $soreData ? "Presensi sore sudah dilakukan ({$soreData->status})" : 'Silakan lakukan presensi sore.';
        }

        return $status;
    }

public function editAbsenceRequest(Presensi $presensi)
{
    if ($presensi->user_id !== Auth::id() || !in_array($presensi->approval_status, ['pending', 'rejected'])) {
        abort(403, 'Anda tidak memiliki izin untuk mengubah pengajuan ini.');
    }

    return view('administrator.presensi.edit_izin_siswa', compact('presensi'));
}

public function updateAbsenceRequest(Request $request, Presensi $presensi)
{
    // 1. Keamanan: Validasi awal tetap sama
    if ($presensi->user_id !== Auth::id() || !in_array($presensi->approval_status, ['pending', 'rejected'])) {
        return redirect()->route('presensi.index')->with('error', 'Anda tidak dapat mengubah pengajuan ini.');
    }

    // 2. Validasi input juga tetap sama
    $request->validate([
        'keterangan' => 'required|string|min:20|max:255',
        'bukti_foto' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048'
    ]);

    try {
        DB::beginTransaction();

        $buktiPathBaru = null;

        // 3. Handle file upload di luar loop untuk efisiensi
        if ($request->hasFile('bukti_foto')) {
            $buktiPathBaru = $request->file('bukti_foto')->store('uploads/presensi/absences', 'public');
        }

        // 4. [LOGIKA BARU] Cari semua data presensi (pagi & sore) milik siswa pada tanggal yang sama
        $tanggalIzin = $presensi->presensi_at->toDateString();
        $semuaSesiTerkait = Presensi::where('user_id', Auth::id())
            ->whereDate('presensi_at', $tanggalIzin)
            ->whereIn('approval_status', ['pending', 'rejected'])
            ->get();

        foreach ($semuaSesiTerkait as $sesi) {
            $sesi->keterangan = $request->keterangan;
            $sesi->approval_status = 'pending';
            
            $sesi->requested_status = $sesi->status; 

            // Jika ada file baru diupload, update path & hapus file lama
            if ($buktiPathBaru) {
                if ($sesi->bukti_foto && $sesi->bukti_foto !== $buktiPathBaru) {
                    Storage::disk('public')->delete($sesi->bukti_foto);
                }
                $sesi->bukti_foto = $buktiPathBaru;
            }
            
            $sesi->save();
        }
        $penerimaNotifikasi = User::whereIn('group_id', [1, 2, 5, 6, 7])->get();
            $siswa = Auth::user();
            Notification::send($penerimaNotifikasi, new SiswaMengajukanUlangIzin($siswa, $presensi));
        DB::commit();

        return redirect()->route('presensi.index')->with('success', 'Pengajuan izin berhasil diperbarui dan dikirim ulang untuk persetujuan.');

    
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Update Absence Request error: ' . $e->getMessage());
        return back()->with('error', 'Terjadi kesalahan saat memperbarui data. Silakan coba lagi.');
    }
}

    /**
     * Mengambil daftar siswa aktif untuk form.
     */
    public function getSiswaList()
    {
        $siswa = User::where('group_id', 4)->orderBy('name')->select('id', 'name as text')->get();
        return response()->json($siswa);
    }

    public function batchCreateAlpa(Request $request)
    {
        $request->validate([
            'tanggal_alpa' => 'required|date',
            'target_type' => 'required|in:all_missing,specific',
            'user_ids' => 'nullable|required_if:target_type,specific|array'
        ]);

        try {
            DB::beginTransaction();

            $tanggal = Carbon::parse($request->tanggal_alpa);
            if ($tanggal->isWeekend()) {
                return response()->json(['message' => 'Tidak dapat membuat presensi alpa pada hari libur (Sabtu/Minggu).'], 422);
            }

            $alpaStatus = PresensiStatus::where('kode', 'ALPA')->first();
            if (!$alpaStatus) {
                return response()->json(['message' => 'Status "ALPA" tidak ditemukan di database.'], 422);
            }

            $targetUserIds = [];
            if ($request->target_type === 'specific') {
                $targetUserIds = $request->user_ids;
            } else { // 'all_missing'
                $siswaAktifIds = User::where('group_id', 4)->pluck('id');
                $sudahPresensiIds = Presensi::whereDate('presensi_at', $tanggal)->distinct()->pluck('user_id');
                $targetUserIds = $siswaAktifIds->diff($sudahPresensiIds)->values()->all();
            }

            if (empty($targetUserIds)) {
                return response()->json(['success' => true, 'message' => 'Tidak ada siswa yang perlu ditandai alpa untuk tanggal yang dipilih.'], 200);
            }

            $dataToInsert = [];
            $sessions = ['pagi', 'sore'];
            $now = now();

            $existingRecords = Presensi::whereDate('presensi_at', $tanggal)
                ->whereIn('user_id', $targetUserIds)
                ->select('user_id', 'sesi')
                ->get()
                ->groupBy('user_id')
                ->map(fn($group) => $group->pluck('sesi'));

            foreach ($targetUserIds as $userId) {
                foreach ($sessions as $sesi) {
                    if (isset($existingRecords[$userId]) && $existingRecords[$userId]->contains($sesi)) {
                        continue; // Lewati jika sudah ada data presensi untuk sesi ini
                    }
                    $dataToInsert[] = [
                        'user_id' => $userId,
                        'presensi_at' => $tanggal->copy()->setTimeFrom(now()),
                        'sesi' => $sesi,
                        'status' => $alpaStatus->status,
                        'presensi_status_id' => $alpaStatus->id,
                        'keterangan' => 'Dibuat otomatis oleh Admin.',
                        'approval_status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (!empty($dataToInsert)) {
                Presensi::insert($dataToInsert);
            }
                    $siswaYangDiAlpa = User::whereIn('id', $targetUserIds)->get();
        foreach ($siswaYangDiAlpa as $siswa) {
            $siswa->notify(new SiswaDinyatakanAlpa($tanggal));
        }
            
            DB::commit();

            $createdCount = count($dataToInsert);
            return response()->json(['success' => true, 'message' => "Berhasil membuat {$createdCount} data presensi Alpa."]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch Create Alpa error: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan internal. Gagal membuat data.'], 500);
        }
    }

        public function getAnalyticsData(Request $request)
    {
        // Gunakan query yang sama persis dengan filter tabel Anda
        $query = Presensi::query()
            ->join('user', 'presensi.user_id', '=', 'user.id')
            ->select(
                'presensi.status',
                DB::raw('COUNT(presensi.id) as total')
            )
            ->groupBy('presensi.status');

        $user = Auth::user();

        // Terapkan filter scope (siswa/pembimbing)
        if ($user->group_id == 4) {
            $query->where('presensi.user_id', $user->id);
        } elseif ($user->group_id == 3) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('sekolah_id', $user->sekolah_id)
                  ->where('program_keahlian_id', $user->program_keahlian_id);
            });
        }
        
        // Terapkan filter dari request (bulan, sekolah, dll)
        $this->applyFiltersToQuery($query, $request);

        $analyticsData = $query->pluck('total', 'status');

        return response()->json($analyticsData);
    }
}
