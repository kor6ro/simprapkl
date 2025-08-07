<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\PresensiSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Presensi::with(['user.sekolah'])->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama', fn($row) => $row->user->name)
                ->addColumn('sekolah', fn($row) => $row->user->sekolah_id->nama ?? '-')
                ->addColumn('bukti_foto', function ($row) {
                    if ($row->bukti_foto) {
                        return '<a href="' . asset('storage/' . $row->bukti_foto) . '" target="_blank">
                                    <img src="' . asset('storage/' . $row->bukti_foto) . '" width="60" class="rounded">
                                </a>';
                    }
                    return '-';
                })
                ->addColumn('status_badge', function ($row) {
                    $badgeClass = match ($row->status) {
                        'Tepat Waktu' => 'success',
                        'Terlambat' => 'warning',
                        'Sakit' => 'secondary',
                        'Izin' => 'info',
                        'Alpa' => 'danger',
                        default => 'light'
                    };
                    return '<span class="badge bg-' . $badgeClass . '">' . $row->status . '</span>';
                })
                ->addColumn('aksi', function ($row) {
                    $actions = '';
                    if (auth()->user()->group_id === 2) { // Admin only
                        $actions = '<a href="' . route('presensi.edit', $row->id) . '" class="btn btn-sm btn-primary me-1">Edit</a>
                                   <form action="' . route('presensi.destroy', $row->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Yakin ingin menghapus?\')">
                                       ' . csrf_field() . method_field('DELETE') . '
                                       <button class="btn btn-sm btn-danger">Hapus</button>
                                   </form>';
                    }
                    return $actions;
                })
                ->rawColumns(['aksi', 'bukti_foto', 'status_badge'])
                ->make(true);
        }

        $data = Presensi::with(['user.sekolah'])->latest()->paginate(10);
        return view('administrator.presensi.index', compact('data'));
    }

    public function create()
    {
        $users = User::where('group_id', 4)->get(); // Only students
        return view('administrator.presensi.create', compact('users'));
    }

    /**
     * Unified method for handling all presensi types
     */
    public function processPresensi(Request $request, string $type)
    {
        // Validate based on type
        $rules = $this->getValidationRules($type);
        $request->validate($rules);

        $user = Auth::user();
        $today = now()->toDateString();

        // Check existing presensi
        if ($this->hasExistingPresensi($user->id, $today, $type)) {
            return back()->with('error', $this->getExistingPresensiMessage($type));
        }

        try {
            $presensiData = $this->buildPresensiData($request, $user, $type);

            if ($type === 'sakit') {
                // Create for both sessions
                $this->createSakitPresensi($presensiData);
            } else {
                Presensi::create($presensiData);
            }

            return back()->with('success', $this->getSuccessMessage($type, $presensiData['status'] ?? null));
        } catch (\Exception $e) {
            Log::error('Presensi error: ' . $e->getMessage());

            // Cleanup uploaded file if exists
            if (isset($presensiData['bukti_foto']) && Storage::disk('public')->exists($presensiData['bukti_foto'])) {
                Storage::disk('public')->delete($presensiData['bukti_foto']);
            }

            return back()->with('error', 'Gagal menyimpan presensi. Silakan coba lagi.');
        }
    }

    /**
     * Check-in method
     */
    public function checkin(Request $request)
    {
        return $this->processPresensi($request, 'checkin');
    }

    /**
     * Check-out method  
     */
    public function checkout(Request $request)
    {
        return $this->processPresensi($request, 'checkout');
    }

    /**
     * Sick/Permission method
     */
    public function sakit(Request $request)
    {
        return $this->processPresensi($request, 'sakit');
    }

    /**
     * Store method for admin/manual entry
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal_presensi' => 'required|date',
            'jam_presensi' => 'required',
            'sesi' => 'required|in:pagi,sore',
            'status' => 'required|string',
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'keterangan' => 'nullable|string|max:255'
        ]);

        // Check for existing presensi
        $existing = Presensi::where('user_id', $request->user_id)
            ->where('tanggal_presensi', $request->tanggal_presensi)
            ->where('sesi', $request->sesi)
            ->exists();

        if ($existing) {
            return back()->with('error', 'Presensi untuk sesi ini sudah ada.');
        }

        $buktiPath = null;
        if ($request->hasFile('bukti_foto')) {
            $buktiPath = $request->file('bukti_foto')->store('uploads/presensi', 'public');
        }

        Presensi::create([
            'user_id' => $request->user_id,
            'tanggal_presensi' => $request->tanggal_presensi,
            'sesi' => $request->sesi,
            'jam_presensi' => $request->jam_presensi,
            'bukti_foto' => $buktiPath,
            'keterangan' => $request->keterangan,
            'status' => $request->status,
        ]);

        return redirect()->route('presensi.index')->with('success', 'Presensi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $presensi = Presensi::with('user')->findOrFail($id);
        $users = User::where('group_id', 4)->get();
        return view('administrator.presensi.edit', compact('presensi', 'users'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'keterangan' => 'nullable|string|max:255'
        ]);

        $presensi = Presensi::findOrFail($id);

        if ($request->hasFile('bukti_foto')) {
            // Delete old file
            if ($presensi->bukti_foto && Storage::disk('public')->exists($presensi->bukti_foto)) {
                Storage::disk('public')->delete($presensi->bukti_foto);
            }

            $presensi->bukti_foto = $request->file('bukti_foto')->store('uploads/presensi', 'public');
        }

        if ($request->filled('keterangan')) {
            $presensi->keterangan = $request->keterangan;
        }

        $presensi->save();

        return redirect()->route('presensi.index')->with('success', 'Presensi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $presensi = Presensi::findOrFail($id);

        // Delete associated file
        if ($presensi->bukti_foto && Storage::disk('public')->exists($presensi->bukti_foto)) {
            Storage::disk('public')->delete($presensi->bukti_foto);
        }

        $presensi->delete();

        return redirect()->route('presensi.index')->with('success', 'Presensi berhasil dihapus.');
    }

    /**
     * Generate absence for students who haven't checked in
     */
    public function generateAlpa()
    {
        // Only admin can generate
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

        $count = 0;
        foreach ($absentStudents as $userId) {
            foreach (['pagi', 'sore'] as $sesi) {
                Presensi::create([
                    'user_id' => $userId,
                    'tanggal_presensi' => $today,
                    'sesi' => $sesi,
                    'status' => 'Alpa',
                    'jam_presensi' => null,
                    'keterangan' => 'Generated automatically',
                ]);
                $count++;
            }
        }

        return back()->with('success', "Berhasil generate {$count} presensi alpa untuk " . $absentStudents->count() . " siswa.");
    }

    // ===== PRIVATE HELPER METHODS =====

    private function getValidationRules(string $type): array
    {
        $baseRules = [
            'bukti_foto' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ];

        return match ($type) {
            'sakit' => array_merge($baseRules, [
                'jenis' => 'required|in:Sakit,Izin',
                'keterangan' => 'required|string|min:10|max:255'
            ]),
            'checkin', 'checkout' => $baseRules,
            default => $baseRules
        };
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

    private function buildPresensiData(Request $request, $user, string $type): array
    {
        $today = now()->toDateString();
        $now = now();

        // Save uploaded file
        $buktiPath = $request->file('bukti_foto')->store('uploads/presensi', 'public');

        $data = [
            'user_id' => $user->id,
            'tanggal_presensi' => $today,
            'bukti_foto' => $buktiPath,
            'keterangan' => $request->get('keterangan'),
        ];

        if ($type === 'sakit') {
            $data['status'] = $request->get('jenis'); // Sakit or Izin
            $data['jam_presensi'] = null;
        } else {
            // Check if late
            $setting = PresensiSetting::first();
            $sesi = $type === 'checkin' ? 'pagi' : 'sore';
            $batasWaktu = $sesi === 'pagi' ? $setting?->pagi_selesai : $setting?->sore_selesai;

            $data['sesi'] = $sesi;
            $data['jam_presensi'] = $now->format('H:i:s');
            $data['status'] = ($batasWaktu && $now->format('H:i:s') > $batasWaktu) ? 'Terlambat' : 'Tepat Waktu';
        }

        return $data;
    }

    private function createSakitPresensi(array $data): void
    {
        foreach (['pagi', 'sore'] as $sesi) {
            Presensi::create(array_merge($data, ['sesi' => $sesi]));
        }
    }

    private function getSuccessMessage(string $type, ?string $status): string
    {
        return match ($type) {
            'checkin' => "Absen masuk berhasil! Status: {$status}",
            'checkout' => "Absen pulang berhasil! Status: {$status}",
            'sakit' => "Pengajuan {$status} berhasil disubmit!",
            default => 'Presensi berhasil disimpan.'
        };
    }
}
