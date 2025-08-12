<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\PresensiSetting;
use App\Models\PresensiStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        // Untuk AJAX DataTables (jika ada)
        if ($request->ajax()) {
            $data = Presensi::with(['user.sekolah', 'presensiStatus'])->latest();
            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('nama', fn($row) => $row->user->name)
                ->addColumn('sekolah', fn($row) => $row->user->sekolah->nama ?? '-')
                ->addColumn('status_badge', function ($row) {
                    return '<span class="badge bg-' . $row->status_color . '">' . $row->status_display . '</span>';
                })
                ->addColumn('bukti_foto', function ($row) {
                    if ($row->bukti_foto) {
                        return '<a href="' . asset('storage/' . $row->bukti_foto) . '" target="_blank">
                                    <img src="' . asset('storage/' . $row->bukti_foto) . '" width="60" class="rounded">
                                </a>';
                    }
                    return '-';
                })
                ->rawColumns(['status_badge', 'bukti_foto'])
                ->make(true);
        }

        $user = Auth::user();
        $today = now()->toDateString();
        $setting = PresensiSetting::first();

        // Data untuk tampilan biasa
        $data = Presensi::with(['user.sekolah', 'presensiStatus'])
            ->where('tanggal_presensi', $today)
            ->latest()
            ->get();

        // Cek status presensi user hari ini
        $presensiHariIni = Presensi::where('user_id', $user->id)
            ->where('tanggal_presensi', $today)
            ->get();

        $statusPresensi = $this->getStatusPresensiHariIni($presensiHariIni, $setting);

        return view('administrator.presensi.index', compact('data', 'statusPresensi', 'setting'));
    }

    // Method baru untuk mendapatkan status presensi hari ini
    private function getStatusPresensiHariIni($presensiHariIni, $setting)
    {
        $now = now();
        $currentTime = $now->format('H:i');

        $pagiData = $presensiHariIni->where('sesi', 'pagi')->first();
        $soreData = $presensiHariIni->where('sesi', 'sore')->first();

        $status = [
            'can_presensi' => false,
            'current_session' => null,
            'message' => '',
            'pagi_status' => $pagiData ? $pagiData->status : null,
            'sore_status' => $soreData ? $soreData->status : null,
            'pagi_jam' => $pagiData ? $pagiData->jam_presensi : null,
            'sore_jam' => $soreData ? $soreData->jam_presensi : null,
        ];

        if (!$setting) {
            $status['message'] = 'Pengaturan presensi belum dikonfigurasi';
            return $status;
        }

        // Tentukan sesi berdasarkan waktu sekarang
        if ($currentTime >= $setting->pagi_mulai && $currentTime < $setting->sore_mulai) {
            $status['current_session'] = 'pagi';

            if (!$pagiData) {
                $status['can_presensi'] = true;
                $status['message'] = 'Silakan lakukan presensi pagi';
            } else {
                $status['message'] = "Presensi pagi sudah dilakukan ({$pagiData->status})";
            }
        } elseif ($currentTime >= $setting->sore_mulai && $currentTime <= $setting->sore_selesai) {
            $status['current_session'] = 'sore';

            if (!$soreData) {
                $status['can_presensi'] = true;
                $status['message'] = 'Silakan lakukan presensi sore';
            } else {
                $status['message'] = "Presensi sore sudah dilakukan ({$soreData->status})";
            }
        } else {
            $status['message'] = 'Waktu presensi sudah berakhir untuk hari ini';
        }

        return $status;
    }

    /**
     * Presensi otomatis dengan camera - hanya perlu foto
     */
    public function PresensiCamera(Request $request)
    {
        Log::info('=== CAMERA PRESENSI AUTO START ===', [
            'user_id' => auth()->id(),
            'request_method' => $request->method(),
        ]);

        try {
            // Validasi request
            $request->validate([
                'image_data' => 'required|string',
                'keterangan' => 'nullable|string|max:255'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', array_flatten($e->errors()))
            ], 422);
        }

        $user = Auth::user();
        $today = now()->toDateString();
        $now = now();
        $setting = PresensiSetting::first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Pengaturan presensi belum dikonfigurasi'
            ], 422);
        }

        // Tentukan sesi otomatis berdasarkan waktu
        $currentTime = $now->format('H:i');
        $sesi = null;

        if ($currentTime >= $setting->pagi_mulai && $currentTime < $setting->sore_mulai) {
            $sesi = 'pagi';
        } elseif ($currentTime >= $setting->sore_mulai && $currentTime <= $setting->sore_selesai) {
            $sesi = 'sore';
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Presensi hanya dapat dilakukan pada jam kerja'
            ], 422);
        }

        // Cek apakah sudah presensi untuk sesi ini
        $existingPresensi = Presensi::where('user_id', $user->id)
            ->where('tanggal_presensi', $today)
            ->where('sesi', $sesi)
            ->first();

        if ($existingPresensi) {
            return response()->json([
                'success' => false,
                'message' => "Anda sudah melakukan presensi {$sesi} hari ini"
            ], 422);
        }

        try {
            // Process image
            $imageFile = $this->processBase64Image($request->image_data);
            if (!$imageFile) {
                throw new \Exception('Failed to process image data');
            }

            // Save image
            $fileName = 'camera_' . date('Y-m-d_H-i-s') . '_' . $user->id . '_' . uniqid() . '.jpg';
            $path = 'uploads/presensi/' . $fileName;

            if (!Storage::disk('public')->put($path, $imageFile)) {
                throw new \Exception('Failed to save image');
            }

            // Tentukan status berdasarkan waktu presensi
            $jamPresensi = $now->format('H:i:s');
            $status = $this->getStatusByTime($jamPresensi, $sesi, $setting);
            $statusId = PresensiStatus::where('status', $status)->first()?->id;

            // Simpan presensi
            Presensi::create([
                'user_id' => $user->id,
                'tanggal_presensi' => $today,
                'sesi' => $sesi,
                'jam_presensi' => $jamPresensi,
                'status' => $status,
                'presensi_status_id' => $statusId,
                'bukti_foto' => $path,
                'keterangan' => $request->keterangan ?? "Presensi {$sesi} otomatis",
            ]);

            Log::info('Auto presensi saved', [
                'sesi' => $sesi,
                'jam' => $jamPresensi,
                'status' => $status
            ]);

            return response()->json([
                'success' => true,
                'message' => "Presensi {$sesi} berhasil! Status: {$status}",
                'data' => [
                    'sesi' => $sesi,
                    'status' => $status,
                    'jam' => $jamPresensi
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Camera presensi error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Cleanup file if exists
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan presensi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit izin/sakit (manual input)
     */
    public function submitIzinSakit(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:Izin,Sakit',
            'keterangan' => 'required|string|min:10|max:255',
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = Auth::user();
        $today = now()->toDateString();

        // Cek apakah sudah ada presensi hari ini
        $existingPresensi = Presensi::where('user_id', $user->id)
            ->where('tanggal_presensi', $today)
            ->exists();

        if ($existingPresensi) {
            return back()->with('error', 'Anda sudah melakukan presensi hari ini');
        }

        try {
            $buktiPath = null;
            if ($request->hasFile('bukti_foto')) {
                $buktiPath = $request->file('bukti_foto')->store('uploads/presensi', 'public');
            }

            $jenis = $request->jenis;
            $statusId = PresensiStatus::where('status', $jenis)->first()?->id;

            // Buat presensi untuk kedua sesi
            foreach (['pagi', 'sore'] as $sesi) {
                Presensi::create([
                    'user_id' => $user->id,
                    'tanggal_presensi' => $today,
                    'sesi' => $sesi,
                    'status' => $jenis,
                    'presensi_status_id' => $statusId,
                    'bukti_foto' => $buktiPath,
                    'keterangan' => $request->keterangan,
                    'jam_presensi' => null,
                ]);
            }

            return back()->with('success', "Pengajuan {$jenis} berhasil disubmit!");
        } catch (\Exception $e) {
            Log::error('Izin/Sakit submit error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan data. Silakan coba lagi.');
        }
    }

    /**
     * Request edit alpa ke izin/sakit
     */
    public function requestEditAlpa(Request $request)
    {
        $request->validate([
            'presensi_id' => 'required|exists:presensi,id',
            'new_status' => 'required|in:Izin,Sakit',
            'keterangan' => 'required|string|min:10|max:500',
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $presensi = Presensi::findOrFail($request->presensi_id);

        // Validasi: hanya bisa edit presensi sendiri
        if ($presensi->user_id !== Auth::id()) {
            return back()->with('error', 'Anda tidak dapat mengedit presensi orang lain');
        }

        // Validasi: hanya bisa edit jika status Alpa
        if ($presensi->status !== 'Alpa') {
            return back()->with('error', 'Hanya presensi dengan status Alpa yang dapat diubah');
        }

        try {
            $buktiPath = $presensi->bukti_foto;
            if ($request->hasFile('bukti_foto')) {
                // Hapus foto lama jika ada
                if ($buktiPath && Storage::disk('public')->exists($buktiPath)) {
                    Storage::disk('public')->delete($buktiPath);
                }
                $buktiPath = $request->file('bukti_foto')->store('uploads/presensi', 'public');
            }

            // Update presensi dengan status pending approval
            $presensi->update([
                'status' => $request->new_status . ' (Menunggu Persetujuan)',
                'keterangan' => $request->keterangan,
                'bukti_foto' => $buktiPath,
                'approval_status' => 'pending', // field baru yang perlu ditambah
                'requested_status' => $request->new_status,
                'approval_notes' => null,
                'approved_by' => null,
                'approved_at' => null
            ]);

            // Update presensi sesi lainnya juga (pagi/sore)
            Presensi::where('user_id', $presensi->user_id)
                ->where('tanggal_presensi', $presensi->tanggal_presensi)
                ->where('id', '!=', $presensi->id)
                ->where('status', 'Alpa')
                ->update([
                    'status' => $request->new_status . ' (Menunggu Persetujuan)',
                    'keterangan' => $request->keterangan,
                    'bukti_foto' => $buktiPath,
                    'approval_status' => 'pending',
                    'requested_status' => $request->new_status,
                ]);

            return back()->with('success', 'Permintaan perubahan status berhasil diajukan. Menunggu persetujuan admin.');
        } catch (\Exception $e) {
            Log::error('Request edit alpa error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengajukan perubahan. Silakan coba lagi.');
        }
    }

    /**
     * Admin approve/reject edit request
     */
    public function processApproval(Request $request, $presensiId)
    {
        // Hanya admin yang bisa approve
        if (Auth::user()->group_id !== 2) {
            return back()->with('error', 'Anda tidak memiliki akses untuk melakukan approval');
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:255'
        ]);

        $presensi = Presensi::findOrFail($presensiId);

        if ($presensi->approval_status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya');
        }

        try {
            if ($request->action === 'approve') {
                // Approve: ubah status ke yang diminta
                $newStatus = $presensi->requested_status;
                $statusId = PresensiStatus::where('status', $newStatus)->first()?->id;

                $presensi->update([
                    'status' => $newStatus,
                    'presensi_status_id' => $statusId,
                    'approval_status' => 'approved',
                    'approval_notes' => $request->notes,
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);

                // Update sesi lainnya juga
                Presensi::where('user_id', $presensi->user_id)
                    ->where('tanggal_presensi', $presensi->tanggal_presensi)
                    ->where('approval_status', 'pending')
                    ->where('requested_status', $newStatus)
                    ->update([
                        'status' => $newStatus,
                        'presensi_status_id' => $statusId,
                        'approval_status' => 'approved',
                        'approval_notes' => $request->notes,
                        'approved_by' => Auth::id(),
                        'approved_at' => now()
                    ]);

                $message = 'Permintaan perubahan status berhasil disetujui';
            } else {
                // Reject: kembalikan ke status Alpa
                $alpaStatusId = PresensiStatus::where('status', 'Alpa')->first()?->id;

                $presensi->update([
                    'status' => 'Alpa',
                    'presensi_status_id' => $alpaStatusId,
                    'approval_status' => 'rejected',
                    'approval_notes' => $request->notes,
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);

                // Update sesi lainnya juga
                Presensi::where('user_id', $presensi->user_id)
                    ->where('tanggal_presensi', $presensi->tanggal_presensi)
                    ->where('approval_status', 'pending')
                    ->update([
                        'status' => 'Alpa',
                        'presensi_status_id' => $alpaStatusId,
                        'approval_status' => 'rejected',
                        'approval_notes' => $request->notes,
                        'approved_by' => Auth::id(),
                        'approved_at' => now()
                    ]);

                $message = 'Permintaan perubahan status ditolak';
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Approval process error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses approval. Silakan coba lagi.');
        }
    }

    // Existing methods remain the same...

    private function processBase64Image(string $imageData): ?string
    {
        try {
            Log::info('Processing base64 image', [
                'data_length' => strlen($imageData),
                'starts_with_data' => str_starts_with($imageData, 'data:')
            ]);

            // Handle data URL format
            if (str_starts_with($imageData, 'data:')) {
                if (!str_contains($imageData, ',')) {
                    throw new \Exception('Invalid data URL format');
                }
                $parts = explode(',', $imageData, 2);
                $imageData = $parts[1];
            }

            // Clean and validate base64
            $imageData = preg_replace('/[^A-Za-z0-9+\/=]/', '', $imageData);

            // Add padding if needed
            $remainder = strlen($imageData) % 4;
            if ($remainder) {
                $imageData .= str_repeat('=', 4 - $remainder);
            }

            // Decode base64
            $imageFile = base64_decode($imageData, true);
            if ($imageFile === false) {
                throw new \Exception('Invalid base64 data');
            }

            // Validate image
            if (!$this->isValidImageData($imageFile)) {
                throw new \Exception('Invalid image format');
            }

            return $imageFile;
        } catch (\Exception $e) {
            Log::error('Base64 processing error: ' . $e->getMessage());
            return null;
        }
    }

    private function isValidImageData(string $data): bool
    {
        if (strlen($data) < 10) return false;

        $signatures = [
            'JPEG' => "\xFF\xD8\xFF",
            'PNG' => "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A",
            'GIF87a' => "GIF87a",
            'GIF89a' => "GIF89a",
        ];

        foreach ($signatures as $signature) {
            if (str_starts_with($data, $signature)) {
                return true;
            }
        }

        return false;
    }

    private function getStatusByTime(string $jamPresensi, string $sesi, $setting)
    {
        if (!$setting) return 'Tepat Waktu';

        $batasWaktu = $sesi === 'pagi' ? $setting->pagi_selesai : $setting->sore_selesai;
        $waktuMulai = $sesi === 'pagi' ? $setting->pagi_mulai : $setting->sore_mulai;

        if (!$batasWaktu || !$waktuMulai) return 'Tepat Waktu';

        $toleransi = $setting->toleransi_telat ?? 15;

        try {
            $waktuPresensi = Carbon::createFromFormat('H:i:s', $jamPresensi);
            $waktuMulaiCarbon = Carbon::createFromFormat('H:i:s', $waktuMulai);
            $waktuBatasCarbon = Carbon::createFromFormat('H:i:s', $batasWaktu);
            $waktuBatasToleransi = Carbon::createFromFormat('H:i:s', $batasWaktu)->addMinutes($toleransi);

            if ($waktuPresensi->lt($waktuMulaiCarbon)) {
                return 'Terlalu Awal';
            }

            if ($waktuPresensi->between($waktuMulaiCarbon, $waktuBatasCarbon)) {
                return 'Tepat Waktu';
            }

            if ($waktuPresensi->between($waktuBatasCarbon->copy()->addSecond(), $waktuBatasToleransi)) {
                return 'Terlambat';
            }

            if ($waktuPresensi->gt($waktuBatasToleransi)) {
                return 'Sangat Terlambat';
            }

            return 'Tepat Waktu';
        } catch (\Exception $e) {
            Log::error('Error in getStatusByTime: ' . $e->getMessage());
            return 'Tepat Waktu';
        }
    }

    // Keep existing methods for other functionalities...
    public function generateAlpa()
    {
        if (Auth::user()->group_id !== 2) {
            return back()->with('error', 'Hanya admin yang dapat generate presensi alpa.');
        }

        $today = now()->toDateString();
        $students = User::where('group_id', 4)->pluck('id');
        $presentStudents = Presensi::where('tanggal_presensi', $today)
            ->pluck('user_id')
            ->unique();

        $absentStudents = $students->diff($presentStudents);

        if ($absentStudents->isEmpty()) {
            return back()->with('info', 'Semua siswa sudah melakukan presensi hari ini.');
        }

        $alpaStatusId = PresensiStatus::where('status', 'Alpa')->first()?->id;
        $count = 0;

        foreach ($absentStudents as $userId) {
            foreach (['pagi', 'sore'] as $sesi) {
                Presensi::create([
                    'user_id' => $userId,
                    'tanggal_presensi' => $today,
                    'sesi' => $sesi,
                    'status' => 'Alpa',
                    'presensi_status_id' => $alpaStatusId,
                    'jam_presensi' => null,
                    'keterangan' => 'Generated automatically',
                ]);
                $count++;
            }
        }

        return back()->with('success', "Berhasil generate {$count} presensi alpa untuk " . $absentStudents->count() . " siswa.");
    }

    /**
     * Admin view untuk approval requests
     */
    public function approvalIndex()
    {
        // Hanya admin yang bisa akses
        if (Auth::user()->group_id !== 2) {
            return redirect()->route('presensi.index')->with('error', 'Akses ditolak');
        }

        $pendingApprovals = Presensi::with(['user.sekolah'])
            ->pendingApproval()
            ->orderBy('updated_at', 'desc')
            ->get();

        $approvalHistory = Presensi::with(['user', 'approvedBy'])
            ->whereIn('approval_status', ['approved', 'rejected'])
            ->where('approved_at', '>=', now()->subDays(7))
            ->orderBy('approved_at', 'desc')
            ->get();

        return view('administrator.presensi.approval', compact('pendingApprovals', 'approvalHistory'));
    }

    // Other existing methods...
}
