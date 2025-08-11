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

        // Data untuk tampilan biasa
        $data = Presensi::with(['user.sekolah', 'presensiStatus'])
            ->where('tanggal_presensi', now()->toDateString())
            ->latest()
            ->get();

        return view('administrator.presensi.index', compact('data'));
    }

    /**
     * Check-in (Absen Masuk)
     */
    public function checkin(Request $request)
    {
        $request->validate([
            'bukti_foto' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        return $this->processPresensi($request, 'checkin');
    }

    /**
     * Check-out (Absen Pulang) 
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'bukti_foto' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        return $this->processPresensi($request, 'checkout');
    }

    /**
     * Izin/Sakit
     */
    public function sakit(Request $request)
    {
        $request->validate([
            'bukti_foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'jenis' => 'required|in:Izin,Sakit',
            'keterangan' => 'required|string|min:10|max:255'
        ]);

        return $this->processPresensi($request, 'sakit');
    }

    /**
     * Main presensi processing logic
     */
    private function processPresensi(Request $request, string $type)
    {
        $user = Auth::user();
        $today = now()->toDateString();
        $now = now();

        // Check existing presensi
        if ($this->hasExistingPresensi($user->id, $today, $type)) {
            return back()->with('error', $this->getExistingPresensiMessage($type));
        }

        try {
            // Handle file upload
            $buktiPath = null;
            if ($request->hasFile('bukti_foto')) {
                $buktiPath = $request->file('bukti_foto')->store('uploads/presensi', 'public');
            } elseif ($request->has('bukti_foto_path')) {
                $buktiPath = $request->get('bukti_foto_path');
            }

            if (!$buktiPath) {
                return back()->with('error', 'Bukti foto diperlukan.');
            }

            // Build presensi data
            $presensiData = [
                'user_id' => $user->id,
                'tanggal_presensi' => $today,
                'bukti_foto' => $buktiPath,
                'keterangan' => $request->get('keterangan'),
            ];

            if ($type === 'sakit') {
                // Untuk sakit/izin, buat untuk kedua sesi
                $jenis = $request->get('jenis', 'Sakit');
                $statusId = PresensiStatus::where('status', $jenis)->first()?->id;

                foreach (['pagi', 'sore'] as $sesi) {
                    Presensi::create(array_merge($presensiData, [
                        'sesi' => $sesi,
                        'status' => $jenis,
                        'presensi_status_id' => $statusId,
                        'jam_presensi' => null,
                    ]));
                }

                return back()->with('success', "Pengajuan {$jenis} berhasil disubmit!");
            } else {
                // Untuk checkin/checkout
                $sesi = $type === 'checkin' ? 'pagi' : 'sore';
                $jamPresensi = $now->format('H:i:s');

                // Cek apakah terlambat
                $setting = PresensiSetting::first();
                $status = $this->getStatusByTime($jamPresensi, $sesi, $setting);
                $statusId = PresensiStatus::where('status', $status)->first()?->id;

                Presensi::create(array_merge($presensiData, [
                    'sesi' => $sesi,
                    'jam_presensi' => $jamPresensi,
                    'status' => $status,
                    'presensi_status_id' => $statusId,
                ]));

                $message = $type === 'checkin' ? 'Absen masuk' : 'Absen pulang';
                return back()->with('success', "{$message} berhasil! Status: {$status}");
            }
        } catch (\Exception $e) {
            Log::error('Presensi error: ' . $e->getMessage());

            // Cleanup uploaded file if exists
            if (isset($buktiPath) && Storage::disk('public')->exists($buktiPath)) {
                Storage::disk('public')->delete($buktiPath);
            }

            return back()->with('error', 'Gagal menyimpan presensi. Silakan coba lagi.');
        }
    }

    private function hasExistingPresensi(int $userId, string $date, string $type): bool
    {
        if ($type === 'sakit') {
            return Presensi::where('user_id', $userId)
                ->where('tanggal_presensi', $date)
                ->exists();
        }

        $sesi = $type === 'checkin' ? 'pagi' : 'sore';
        return Presensi::where('user_id', $userId)
            ->where('tanggal_presensi', $date)
            ->where('sesi', $sesi)
            ->exists();
    }

    private function getExistingPresensiMessage(string $type): string
    {
        return match ($type) {
            'checkin' => 'Anda sudah melakukan absen masuk hari ini.',
            'checkout' => 'Anda sudah melakukan absen pulang hari ini.',
            'sakit' => 'Anda sudah mengisi presensi hari ini.',
            default => 'Presensi sudah ada.'
        };
    }

    /**
     * FIXED: Method untuk menentukan status berdasarkan waktu (time only)
     */
    private function getStatusByTime(string $jamPresensi, string $sesi, $setting)
    {
        if (!$setting) {
            Log::info('No setting found, returning Tepat Waktu');
            return 'Tepat Waktu';
        }

        // Tentukan batas waktu berdasarkan sesi
        $batasWaktu = $sesi === 'pagi' ? $setting->pagi_selesai : $setting->sore_selesai;
        $waktuMulai = $sesi === 'pagi' ? $setting->pagi_mulai : $setting->sore_mulai;

        if (!$batasWaktu || !$waktuMulai) {
            Log::info('No time boundaries found', [
                'sesi' => $sesi,
                'batas_waktu' => $batasWaktu,
                'waktu_mulai' => $waktuMulai,
                'setting' => $setting->toArray()
            ]);
            return 'Tepat Waktu';
        }

        // Ambil toleransi keterlambatan (default 15 menit jika tidak ada)
        $toleransi = $setting->toleransi_telat ?? 15;

        // Debug logging yang lebih detail
        Log::info('Status calculation started', [
            'jam_presensi' => $jamPresensi,
            'sesi' => $sesi,
            'waktu_mulai' => $waktuMulai,
            'batas_waktu' => $batasWaktu,
            'toleransi' => $toleransi,
        ]);

        try {
            // Parse waktu langsung sebagai time, bukan datetime
            $waktuPresensi = Carbon::createFromFormat('H:i:s', $jamPresensi);
            $waktuMulaiCarbon = Carbon::createFromFormat('H:i:s', $waktuMulai);
            $waktuBatasCarbon = Carbon::createFromFormat('H:i:s', $batasWaktu);
            $waktuBatasToleransi = Carbon::createFromFormat('H:i:s', $batasWaktu)->addMinutes($toleransi);

            Log::info('Parsed times for comparison', [
                'waktu_presensi' => $waktuPresensi->format('H:i:s'),
                'waktu_mulai' => $waktuMulaiCarbon->format('H:i:s'),
                'waktu_batas' => $waktuBatasCarbon->format('H:i:s'),
                'waktu_batas_toleransi' => $waktuBatasToleransi->format('H:i:s'),
            ]);

            // Logika penentuan status:
            // 1. Jika presensi sebelum waktu mulai = Terlalu Awal
            if ($waktuPresensi->lt($waktuMulaiCarbon)) {
                Log::info('Status: Terlalu Awal', [
                    'reason' => 'Presensi sebelum waktu mulai',
                    'presensi' => $waktuPresensi->format('H:i:s'),
                    'mulai' => $waktuMulaiCarbon->format('H:i:s')
                ]);
                return 'Terlalu Awal';
            }

            // 2. Jika presensi dalam rentang waktu normal (mulai s/d selesai) = Tepat Waktu
            if ($waktuPresensi->between($waktuMulaiCarbon, $waktuBatasCarbon)) {
                Log::info('Status: Tepat Waktu', [
                    'reason' => 'Presensi dalam rentang waktu normal',
                    'presensi' => $waktuPresensi->format('H:i:s'),
                    'range' => $waktuMulaiCarbon->format('H:i:s') . ' - ' . $waktuBatasCarbon->format('H:i:s')
                ]);
                return 'Tepat Waktu';
            }

            // 3. Jika presensi dalam masa toleransi = Terlambat
            if ($waktuPresensi->between($waktuBatasCarbon->copy()->addSecond(), $waktuBatasToleransi)) {
                $menitTerlambat = $waktuPresensi->diffInMinutes($waktuBatasCarbon);
                Log::info('Status: Terlambat', [
                    'reason' => 'Presensi dalam masa toleransi',
                    'presensi' => $waktuPresensi->format('H:i:s'),
                    'batas' => $waktuBatasCarbon->format('H:i:s'),
                    'toleransi_sampai' => $waktuBatasToleransi->format('H:i:s'),
                    'menit_terlambat' => $menitTerlambat
                ]);
                return 'Terlambat';
            }

            // 4. Jika presensi melewati batas toleransi = Sangat Terlambat
            if ($waktuPresensi->gt($waktuBatasToleransi)) {
                $menitTerlambat = $waktuPresensi->diffInMinutes($waktuBatasCarbon);
                Log::info('Status: Sangat Terlambat', [
                    'reason' => 'Presensi melewati batas toleransi',
                    'presensi' => $waktuPresensi->format('H:i:s'),
                    'batas_toleransi' => $waktuBatasToleransi->format('H:i:s'),
                    'menit_terlambat' => $menitTerlambat
                ]);
                return 'Sangat Terlambat';
            }

            // Fallback (seharusnya tidak pernah terjadi)
            Log::warning('Fallback to Tepat Waktu - unexpected condition');
            return 'Tepat Waktu';
        } catch (\Exception $e) {
            Log::error('Error in getStatusByTime calculation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 'Tepat Waktu';
        }
    }

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

        $alpaStatusId = PresensiStatus::where('kode', 'ALPA')->first()?->id;
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
     * Process presensi dengan camera upload - VERSI DIPERBAIKI
     */
    public function PresensiCamera(Request $request)
    {
        Log::info('=== CAMERA PRESENSI DEBUG START ===', [
            'user_id' => auth()->id(),
            'request_method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'request_size' => strlen($request->getContent()),
        ]);

        try {
            // Validasi request awal
            $request->validate([
                'image_data' => 'required|string',
                'jenis' => 'required|in:masuk,keluar,izin,sakit',
                'keterangan' => 'nullable|string|max:255'
            ]);

            Log::info('Validation passed', [
                'jenis' => $request->jenis,
                'has_keterangan' => !empty($request->keterangan),
                'keterangan_length' => strlen($request->keterangan ?? ''),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', array_flatten($e->errors()))
            ], 422);
        }

        // Validasi keterangan untuk izin/sakit
        if (in_array($request->jenis, ['izin', 'sakit']) && strlen($request->keterangan ?? '') < 10) {
            return response()->json([
                'success' => false,
                'message' => 'Keterangan minimal 10 karakter untuk izin/sakit'
            ], 422);
        }

        $user = Auth::user();
        $today = now()->toDateString();
        $type = $request->jenis === 'masuk' ? 'checkin' : ($request->jenis === 'keluar' ? 'checkout' : 'sakit');

        // Check existing presensi
        if ($this->hasExistingPresensi($user->id, $today, $type)) {
            return response()->json([
                'success' => false,
                'message' => $this->getExistingPresensiMessage($type)
            ], 422);
        }

        try {
            // Process image data
            $imageFile = $this->processBase64Image($request->image_data);
            if (!$imageFile) {
                throw new \Exception('Failed to process image data');
            }

            // Save image file
            $fileName = 'camera_' . date('Y-m-d_H-i-s') . '_' . $user->id . '_' . uniqid() . '.jpg';
            $path = 'uploads/presensi/' . $fileName;

            if (!Storage::disk('public')->put($path, $imageFile)) {
                throw new \Exception('Failed to save image to storage');
            }

            // Verify file exists
            if (!Storage::disk('public')->exists($path)) {
                throw new \Exception('File was not saved properly');
            }

            // Save presensi data
            $presensiData = [
                'user_id' => $user->id,
                'tanggal_presensi' => $today,
                'bukti_foto' => $path,
                'keterangan' => $request->keterangan ?? null,
            ];

            if ($type === 'sakit') {
                $jenis = ucfirst($request->jenis);
                $statusId = PresensiStatus::where('status', $jenis)->first()?->id;

                foreach (['pagi', 'sore'] as $sesi) {
                    Presensi::create(array_merge($presensiData, [
                        'sesi' => $sesi,
                        'status' => $jenis,
                        'presensi_status_id' => $statusId,
                        'jam_presensi' => null,
                    ]));
                }

                return response()->json([
                    'success' => true,
                    'message' => "Pengajuan {$jenis} berhasil disubmit!"
                ]);
            } else {
                $sesi = $type === 'checkin' ? 'pagi' : 'sore';
                $jamPresensi = now()->format('H:i:s');

                $setting = PresensiSetting::first();
                $status = $this->getStatusByTime($jamPresensi, $sesi, $setting);
                $statusId = PresensiStatus::where('status', $status)->first()?->id;

                Presensi::create(array_merge($presensiData, [
                    'sesi' => $sesi,
                    'jam_presensi' => $jamPresensi,
                    'status' => $status,
                    'presensi_status_id' => $statusId,
                ]));

                $message = $type === 'checkin' ? 'Absen masuk' : 'Absen pulang';
                return response()->json([
                    'success' => true,
                    'message' => "{$message} berhasil! Status: {$status}"
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Camera presensi error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
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
     * FIXED: Process base64 image data
     */
    private function processBase64Image(string $imageData): ?string
    {
        try {
            // Handle data URL format
            if (str_starts_with($imageData, 'data:')) {
                if (!str_contains($imageData, ',')) {
                    throw new \Exception('Invalid data URL format');
                }
                $imageData = explode(',', $imageData, 2)[1];
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

    /**
     * Validate image data by checking magic bytes
     */
    private function isValidImageData(string $data): bool
    {
        if (strlen($data) < 10) {
            return false;
        }

        // Check for common image formats
        $signatures = [
            'JPEG' => "\xFF\xD8\xFF",
            'PNG' => "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A",
            'GIF87a' => "GIF87a",
            'GIF89a' => "GIF89a",
        ];

        foreach ($signatures as $format => $signature) {
            if (str_starts_with($data, $signature)) {
                return true;
            }
        }

        // Check WebP
        if (strlen($data) >= 12) {
            $header = substr($data, 0, 4);
            $webpSignature = substr($data, 8, 4);
            if ($header === "RIFF" && $webpSignature === "WEBP") {
                return true;
            }
        }

        return false;
    }

    /**
     * Get image type from data
     */
    private function getImageType(string $data): string
    {
        if (strlen($data) < 3) return 'Unknown';

        if (str_starts_with($data, "\xFF\xD8\xFF")) return 'JPEG';
        if (str_starts_with($data, "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A")) return 'PNG';
        if (str_starts_with($data, "GIF87a") || str_starts_with($data, "GIF89a")) return 'GIF';

        if (strlen($data) >= 12 && substr($data, 0, 4) === "RIFF" && substr($data, 8, 4) === "WEBP") {
            return 'WebP';
        }

        return 'Unknown';
    }
}
