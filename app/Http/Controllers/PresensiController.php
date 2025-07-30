<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\PresensiJenis;
use App\Models\PresensiGambar;
use App\Models\PresensiSetting;
use Illuminate\Database\UniqueConstraintViolationException; // Import class exception
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\User; // Added this import for User model

class PresensiController extends Controller
{

    public function index()
    {
        // Siswa hanya bisa lihat presensi sendiri
        if (isSiswa()) {
            return view("administrator.dashboard.index");
        }

        // Admin, Developer, Pembimbing bisa lihat semua
        return view("administrator.presensi.index");
    }

    public function create()
    {
        // Hanya siswa yang bisa input presensi
        if (!canInputPresensi()) {
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false,
                'message' => 'Anda tidak memiliki akses untuk input presensi',
            ]);
        }

        // Get active presensi setting
        $activeSetting = PresensiSetting::where('is_active', true)->first();

        // Get hanya siswa untuk dropdown (jika admin/developer input untuk siswa lain)
        $students = User::where('group_id', 4)->get();

        return view("administrator.presensi.create", [
            "user" => $students,
            "PresensiJenis" => \App\Models\PresensiJenis::all(),
            "activeSetting" => $activeSetting
        ]);
    }


    public function edit($id)
    {
        $presensi = Presensi::with('gambar')->findOrFail($id);

        // Siswa hanya bisa edit presensi sendiri
        if (isSiswa() && $presensi->user_id !== Auth::id()) {
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false,
                'message' => 'Anda hanya dapat mengedit presensi sendiri',
            ]);
        }

        // Get hanya siswa untuk dropdown
        $students = User::where('group_id', 4)->get();

        return view("administrator.presensi.edit", [
            "presensi" => $presensi,
            "user" => $students,
            "PresensiJenis" => \App\Models\PresensiJenis::all(),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Hanya siswa yang bisa input presensi
        if (!canInputPresensi()) {
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false,
                'message' => 'Anda tidak memiliki akses untuk input presensi',
            ]);
        }

        $request->validate([
            'user_id' => 'required|exists:user,id', // Pastikan tabelnya 'user' bukan 'user'
            'presensi_jenis_id' => 'required|exists:presensi_jenis,id',
            'sesi' => 'required|string|in:pagi,sore', // Lebih baik definisikan valuenya
            'bukti' => 'nullable|image|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        // Siswa hanya bisa input presensi untuk dirinya sendiri
        if (isSiswa() && $request->user_id != Auth::id()) {
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false,
                'message' => 'Anda hanya dapat input presensi untuk diri sendiri',
            ]);
        }

        // --- PERBAIKAN DIMULAI DI SINI ---

        $tanggalSekarang = now()->toDateString();
        $jamSekarang = now()->format('H:i:s');

        // 1. Cek apakah data presensi untuk user, tanggal, dan sesi ini sudah ada
        $existingPresensi = Presensi::where('user_id', $request->user_id)
            ->where('tanggal_presensi', $tanggalSekarang)
            ->where('sesi', $request->sesi)
            ->first();

        // 2. Jika sudah ada, kembalikan dengan pesan error
        if ($existingPresensi) {
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false,
                'message' => 'Gagal! Pengguna sudah melakukan presensi untuk sesi ini pada hari ini.',
            ]);
        }

        // 3. Cek apakah jenis presensi membutuhkan bukti
        $PresensiJenis = PresensiJenis::find($request->presensi_jenis_id);
        if ($PresensiJenis && $PresensiJenis->butuh_bukti && !$request->hasFile('bukti')) {
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false,
                'message' => 'Jenis presensi ini membutuhkan bukti. Silakan upload bukti.',
            ]);
        }

        // 4. Cek apakah presensi telat berdasarkan setting aktif
        $activeSetting = PresensiSetting::where('is_active', true)->first();
        $jenisTelat = PresensiJenis::where('nama', 'telat')->first();
        $isTelat = false;
        $message = '';

        if ($activeSetting && $jenisTelat) {
            $isTelat = $this->checkIfTelat($jamSekarang, $request->sesi, $activeSetting);
            if ($isTelat) {
                $message = 'Presensi dilakukan di luar jam yang ditentukan. Akan dicatat sebagai telat.';
            }
        }

        // 5. Jika belum ada, lanjutkan proses penyimpanan (gunakan try-catch untuk keamanan ekstra)
        try {
            // Jika telat, gunakan jenis presensi telat
            $presensiJenisId = $isTelat ? $jenisTelat->id : $request->presensi_jenis_id;

            // Simpan presensi dulu
            $presensi = Presensi::create([
                'user_id' => $request->user_id,
                'presensi_jenis_id' => $presensiJenisId,
                'sesi' => $request->sesi,
                'jam_presensi' => $jamSekarang,
                'tanggal_presensi' => $tanggalSekarang, // Gunakan variabel tanggal yg sudah didefinisikan
                'keterangan' => $request->keterangan . ($isTelat ? ' (Otomatis telat)' : ''),
                'status_verifikasi' => $isTelat ? 'valid' : 'pending',
                'catatan_verifikasi' => $isTelat ? 'Sistem otomatis - Telat' : null,
            ]);

            // Jika ada gambar, simpan ke presensi_gambar
            if ($request->hasFile('bukti') && $request->file('bukti')->isValid()) {
                $path = $request->file('bukti')->store('bukti_presensi', 'public');

                PresensiGambar::create([
                    'presensi_id' => $presensi->id,
                    'bukti' => $path,
                ]);
            }

            $successMessage = 'Presensi berhasil ditambahkan';
            if ($isTelat) {
                $successMessage .= ' - ' . $message;
            }

            return redirect()->route('presensi.index')->with([
                'dataSaved' => true,
                'message' => $successMessage,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // Ini sebagai jaring pengaman jika ada race condition (2 request bersamaan)
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false,
                'message' => 'Gagal! Data presensi ini sudah ada.',
            ]);
        } catch (\Exception $e) {
            // Menangani error umum lainnya
            // Sebaiknya log error ini untuk debugging
            // \Log::error($e->getMessage());
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi.',
            ]);
        }
    }

    /**
     * Check if presensi is telat based on active setting
     */
    private function checkIfTelat($currentTime, $sesi, $setting)
    {
        try {
            $current = Carbon::createFromFormat('H:i:s', $currentTime);

            if ($sesi === 'pagi') {
                $end = Carbon::createFromFormat('H:i:s', $setting->pagi_selesai);
            } else {
                $end = Carbon::createFromFormat('H:i:s', $setting->sore_selesai);
            }

            // Jika presensi dilakukan setelah jam selesai, dianggap telat
            return $current->gt($end);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate presensi time based on active setting
     */
    private function validatePresensiTime($currentTime, $sesi, $setting)
    {
        $current = Carbon::createFromFormat('H:i:s', $currentTime);

        if ($sesi === 'pagi') {
            $start = Carbon::createFromFormat('H:i:s', $setting->pagi_mulai);
            $end = Carbon::createFromFormat('H:i:s', $setting->pagi_selesai);
        } else {
            $start = Carbon::createFromFormat('H:i:s', $setting->sore_mulai);
            $end = Carbon::createFromFormat('H:i:s', $setting->sore_selesai);
        }

        if ($current->between($start, $end)) {
            return ['valid' => true, 'message' => ''];
        } else {
            $sesiText = $sesi === 'pagi' ? 'Pagi' : 'Sore';
            $startTime = $start->format('H:i');
            $endTime = $end->format('H:i');

            return [
                'valid' => false,
                'message' => "Presensi sesi {$sesiText} hanya dapat dilakukan antara jam {$startTime} - {$endTime}"
            ];
        }
    }

    /**
     * Get presensi statistics for API
     */
    public function getStatistics()
    {
        $today = Carbon::today();

        $statistics = [
            'today_presensi' => Presensi::whereDate('tanggal_presensi', $today)->count(),
            'pagi_presensi' => Presensi::whereDate('tanggal_presensi', $today)
                ->where('sesi', 'pagi')->count(),
            'sore_presensi' => Presensi::whereDate('tanggal_presensi', $today)
                ->where('sesi', 'sore')->count(),
            'total_users' => \App\Models\User::count(),
            'active_setting' => PresensiSetting::where('is_active', true)->first()
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * Run automatic presensi check
     */
    public function runAutomaticCheck()
    {
        // Hanya admin dan developer yang bisa menjalankan
        if (!canValidatePresensi()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menjalankan fitur ini'
            ], 403);
        }

        try {
            \App\Jobs\CheckPresensiOtomatis::dispatch();
            return response()->json([
                'success' => true,
                'message' => 'Pengecekan presensi otomatis berhasil dijalankan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


    public function fetch(Request $request)
    {
        // Query dengan join untuk membuat semua field searchable
        $presensi = Presensi::query()
            ->leftJoin('user', 'presensi.user_id', '=', 'user.id')
            ->leftJoin('presensi_jenis', 'presensi.presensi_jenis_id', '=', 'presensi_jenis.id')
            ->select([
                'presensi.*',
                'user.name as nama',
                'presensi_jenis.nama as presensi_jenis'
            ]);

        // Siswa hanya bisa lihat presensi sendiri
        if (isSiswa()) {
            $presensi->where('presensi.user_id', Auth::id());
        }

        return DataTables::of($presensi)
            ->addIndexColumn()
            ->addColumn('nama', function ($row) {
                return $row->nama ?? 'N/A';
            })
            ->addColumn('presensi_jenis', function ($row) {
                return $row->presensi_jenis ?? 'N/A';
            })
            ->addColumn('sesi', function ($row) {
                return ucfirst($row->sesi ?? 'N/A');
            })
            ->addColumn('jam_presensi', function ($row) {
                return $row->jam_presensi ? date('H:i:s', strtotime($row->jam_presensi)) : 'N/A';
            })
            ->addColumn('tanggal_presensi', function ($row) {
                return $row->tanggal_presensi ? date('d/m/Y', strtotime($row->tanggal_presensi)) : 'N/A';
            })
            ->addColumn('status_verifikasi', function ($row) {
                $status = $row->status_verifikasi ?? 'Belum Diverifikasi';

                $badgeClass = match (strtolower($status)) {
                    'terverifikasi', 'approved', 'disetujui', 'valid' => 'badge-success',
                    'ditolak', 'rejected' => 'badge-danger',
                    'pending', 'menunggu' => 'badge-warning',
                    default => 'badge-secondary'
                };

                return '<span class="badge ' . $badgeClass . '">' . ucfirst($status) . '</span>';
            })
            // Filter untuk pencarian
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->get('search')['value'])) {
                    $searchValue = $request->get('search')['value'];

                    $query->where(function ($q) use ($searchValue) {
                        $q->where('user.name', 'like', "%{$searchValue}%")
                            ->orWhere('presensi_jenis.nama', 'like', "%{$searchValue}%")
                            ->orWhere('presensi.sesi', 'like', "%{$searchValue}%")
                            ->orWhere('presensi.jam_presensi', 'like', "%{$searchValue}%")
                            ->orWhere('presensi.tanggal_presensi', 'like', "%{$searchValue}%")
                            ->orWhere('presensi.status_verifikasi', 'like', "%{$searchValue}%");
                    });
                }
            })
            ->rawColumns(['status_verifikasi']) // Untuk render HTML pada kolom status
            ->make(true);
    }

    public function update(Request $request, $id)
    {
        $presensi = Presensi::findOrFail($id);

        // Siswa hanya bisa edit presensi sendiri
        if (isSiswa() && $presensi->user_id !== Auth::id()) {
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false,
                'message' => 'Anda hanya dapat mengedit presensi sendiri',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:user,id', // Pastikan tabelnya 'user'
            'presensi_jenis_id' => 'required|exists:presensi_jenis,id',
            'tanggal_presensi' => 'required|date',
            'sesi' => 'required|in:pagi,sore',
            'jam_presensi' => 'required|date_format:H:i:s', // Sesuaikan format jika perlu
            'status_verifikasi' => 'nullable|string',
            'catatan_verifikasi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect(route("presensi.edit", $id))
                ->withErrors($validator)
                ->withInput();
        }

        // Cek duplikasi data saat update, kecuali untuk data itu sendiri
        $isDuplicate = Presensi::where('user_id', $request->user_id)
            ->where('tanggal_presensi', $request->tanggal_presensi)
            ->where('sesi', $request->sesi)
            ->where('id', '!=', $id) // Pengecualian untuk data yang sedang diedit
            ->exists();

        if ($isDuplicate) {
            return redirect(route("presensi.edit", $id))
                ->withErrors(['user_id' => 'Kombinasi pengguna, tanggal, dan sesi ini sudah ada.'])
                ->withInput();
        }

        try {
            $presensi->update([
                'user_id' => $request->user_id,
                'presensi_jenis_id' => $request->presensi_jenis_id,
                'tanggal_presensi' => $request->tanggal_presensi,
                'sesi' => $request->sesi,
                'jam_presensi' => $request->jam_presensi,
                'status_verifikasi' => $request->status_verifikasi,
                'catatan_verifikasi' => $request->catatan_verifikasi,
            ]);

            return redirect()->route('presensi.index')->with([
                'dataSaved' => true,
                'message' => 'Presensi berhasil diperbarui',
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false,
                'message' => 'Gagal memperbarui presensi',
            ]);
        }
    }

    public function destroy($id)
    {
        $presensi = Presensi::where("id", $id)->first();
        if (!$presensi) {
            return abort(404);
        }

        // Siswa hanya bisa hapus presensi sendiri
        if (isSiswa() && $presensi->user_id !== Auth::id()) {
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false,
                'message' => 'Anda hanya dapat menghapus presensi sendiri',
            ]);
        }

        try {
            // Hapus juga gambar terkait jika ada
            if ($presensi->gambar) {
                // Hapus file dari storage
                File::delete(storage_path('app/public/' . $presensi->gambar->bukti));
                // Hapus record dari database
                $presensi->gambar->delete();
            }

            $presensi->delete();

            return redirect(route("presensi.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("presensi.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }
}
