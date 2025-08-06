<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\PresensiSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Presensi::with('user')->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama', fn($row) => $row->user->name)
                ->addColumn('bukti_foto', function ($row) {
                    if ($row->bukti_foto) {
                        return '<a href="' . asset('storage/' . $row->bukti_foto) . '" target="_blank">
                                    <img src="' . asset('storage/' . $row->bukti_foto) . '" width="60" class="rounded">
                                </a>';
                    }
                    return '-';
                })
                ->addColumn('aksi', function ($row) {
                    return '<a href="' . route('presensi.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a>
                            <form action="' . route('presensi.destroy', $row->id) . '" method="POST" style="display:inline;">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button class="btn btn-sm btn-danger" onclick="return confirm(\'Yakin ingin menghapus?\')">Hapus</button>
                            </form>';
                })
                ->rawColumns(['aksi', 'bukti_foto'])
                ->make(true);
        }

        return view('administrator.presensi.index');
    }

    public function create()
    {
        $user = User::all();
        return view('administrator.presensi.create', compact('user'));
    }

    public function checkin(Request $request)
    {
        return $this->processPresensi($request, 'pagi');
    }

    public function checkout(Request $request)
    {
        return $this->processPresensi($request, 'sore');
    }

    private function processPresensi(Request $request, $sesi)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        $user_id = Auth::id();
        $today = now()->toDateString();
        $jamSekarang = now();

        // Check if already present for this session today
        $sudahAbsen = Presensi::where('user_id', $user_id)
            ->where('tanggal_presensi', $today)
            ->where('sesi', $sesi)
            ->exists();

        if ($sudahAbsen) {
            return redirect()->back()->with('error', 'Anda sudah melakukan presensi ' . $sesi . ' hari ini.');
        }

        // Get settings
        $setting = PresensiSetting::first();
        if (!$setting) {
            return redirect()->back()->with('error', 'Pengaturan presensi belum dikonfigurasi.');
        }

        // Check if late
        $batasWaktu = $sesi === 'pagi' ? $setting->pagi_selesai : $setting->sore_selesai;
        $telat = $jamSekarang->format('H:i:s') > $batasWaktu;

        // Save photo from base64
        $buktiPath = $this->simpanFotoBase64($request->image);
        if (!$buktiPath) {
            return redirect()->back()->with('error', 'Gagal menyimpan foto.');
        }

        $status = $telat ? 'Terlambat' : 'Tepat Waktu';

        try {
            $presensi = Presensi::create([
                'user_id' => $user_id,
                'tanggal_presensi' => $today,
                'sesi' => $sesi,
                'jam_presensi' => $jamSekarang->format('H:i:s'),
                'bukti_foto' => $buktiPath,
                'keterangan' => $request->keterangan,
                'status' => $status,
            ]);

            $message = 'Presensi ' . $sesi . ' berhasil! Status: ' . $status;
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            // Delete uploaded file if database save fails
            if ($buktiPath && Storage::disk('public')->exists($buktiPath)) {
                Storage::disk('public')->delete($buktiPath);
            }

            return redirect()->back()->with('error', 'Gagal menyimpan presensi: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_presensi' => 'required|date',
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user_id = Auth::id();
        $jamSekarang = now();
        $tanggal = $request->tanggal_presensi;

        $setting = PresensiSetting::first();
        $sesi = $jamSekarang->format('H:i:s') <= $setting->pagi_selesai ? 'pagi' : 'sore';

        $sudah = Presensi::where('user_id', $user_id)
            ->where('tanggal_presensi', $tanggal)
            ->where('sesi', $sesi)
            ->exists();

        if ($sudah) {
            return back()->with('error', 'Sudah melakukan presensi untuk sesi ini.');
        }

        $telat = ($sesi === 'pagi' && $jamSekarang->format('H:i:s') > $setting->pagi_selesai);

        $buktiPath = $this->simpanFoto($request);
        $status = $telat ? 'Terlambat' : 'Tepat Waktu';

        $presensi = Presensi::create([
            'user_id' => $user_id,
            'tanggal_presensi' => $tanggal,
            'sesi' => $sesi,
            'jam_presensi' => $jamSekarang->format('H:i:s'),
            'bukti_foto' => $buktiPath,
            'keterangan' => $request->keterangan,
            'status' => $status,
        ]);

        return redirect()->route('presensi.index')->with('success', 'Presensi berhasil disimpan' . ($telat ? ' (Terlambat)' : ' (Tepat Waktu)'));
    }

    public function edit($id)
    {
        $presensi = Presensi::findOrFail($id);
        return view('administrator.presensi.edit', compact('presensi'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $presensi = Presensi::findOrFail($id);

        if ($request->hasFile('bukti_foto')) {
            if ($presensi->bukti_foto && Storage::disk('public')->exists($presensi->bukti_foto)) {
                Storage::disk('public')->delete($presensi->bukti_foto);
            }

            $presensi->bukti_foto = $request->file('bukti_foto')->store('uploads/presensi', 'public');
        }

        $presensi->save();

        return redirect()->route('presensi.index')->with('success', 'Presensi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $presensi = Presensi::findOrFail($id);

        if ($presensi->bukti_foto && Storage::disk('public')->exists($presensi->bukti_foto)) {
            Storage::disk('public')->delete($presensi->bukti_foto);
        }

        $presensi->delete();

        return redirect()->route('presensi.index')->with('success', 'Presensi berhasil dihapus.');
    }

    public function sakit(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'jenis' => 'required|in:Sakit,Izin',
            'keterangan' => 'required|string|min:10',
        ]);

        $user = Auth::user();
        $today = now()->toDateString();

        // Check if already submitted today
        if (Presensi::where('user_id', $user->id)->where('tanggal_presensi', $today)->exists()) {
            return redirect()->back()->with('error', 'Anda sudah mengisi presensi hari ini.');
        }

        // Save photo from base64
        $buktiPath = $this->simpanFotoBase64($request->image);
        if (!$buktiPath) {
            return redirect()->back()->with('error', 'Gagal menyimpan foto.');
        }

        try {
            // Create presensi for both sessions
            foreach (['pagi', 'sore'] as $sesi) {
                Presensi::create([
                    'user_id' => $user->id,
                    'tanggal_presensi' => $today,
                    'sesi' => $sesi,
                    'status' => $request->jenis,
                    'keterangan' => $request->keterangan,
                    'bukti_foto' => $buktiPath,
                    'jam_presensi' => null, // No time for sick/permit
                ]);
            }

            return redirect()->back()->with('success', 'Pengajuan ' . strtolower($request->jenis) . ' berhasil disubmit!');
        } catch (\Exception $e) {
            // Delete uploaded file if database save fails
            if ($buktiPath && Storage::disk('public')->exists($buktiPath)) {
                Storage::disk('public')->delete($buktiPath);
            }

            return redirect()->back()->with('error', 'Gagal menyimpan pengajuan: ' . $e->getMessage());
        }
    }

    public function generateAlpa()
    {
        if (Auth::user()->group_id !== 2) {
            return redirect()->back()->with('error', 'Hanya admin yang dapat generate presensi alpa.');
        }

        $today = now()->toDateString();
        $siswa = User::where('group_id', 4)->pluck('id');
        $sudahAbsen = Presensi::where('tanggal_presensi', $today)->pluck('user_id')->unique();
        $belumAbsen = $siswa->diff($sudahAbsen);

        $count = 0;
        foreach ($belumAbsen as $userId) {
            foreach (['pagi', 'sore'] as $sesi) {
                Presensi::create([
                    'user_id' => $userId,
                    'tanggal_presensi' => $today,
                    'sesi' => $sesi,
                    'status' => 'Alpa',
                    'jam_presensi' => null,
                ]);
                $count++;
            }
        }

        return redirect()->back()->with('success', "Berhasil generate {$count} presensi alpa untuk " . $belumAbsen->count() . " siswa.");
    }

    private function simpanFoto(Request $request)
    {
        if ($request->hasFile('bukti_foto')) {
            return $request->file('bukti_foto')->store('uploads/presensi', 'public');
        }
        return null;
    }

    private function simpanFotoBase64($base64Image)
    {
        try {
            // Remove data:image/jpeg;base64, part
            if (strpos($base64Image, 'data:image') === 0) {
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            }

            // Decode base64
            $imageData = base64_decode($base64Image);
            if ($imageData === false) {
                return null;
            }

            // Generate unique filename
            $filename = 'presensi_' . Auth::id() . '_' . time() . '_' . Str::random(8) . '.jpg';
            $filepath = 'uploads/presensi/' . $filename;

            // Save to storage
            if (Storage::disk('public')->put($filepath, $imageData)) {
                return $filepath;
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Error saving base64 image: ' . $e->getMessage());
            return null;
        }
    }

    private function overlayFotoPresensi($fotoPath, $user)
    {
        if (!$fotoPath || !class_exists('\Intervention\Image\ImageManagerStatic')) {
            return;
        }

        try {
            $imagePath = storage_path('app/public/' . $fotoPath);
            if (!file_exists($imagePath)) {
                return;
            }

            $image = \Intervention\Image\ImageManagerStatic::make($imagePath);

            // Make square crop
            $size = min($image->width(), $image->height());
            $image->crop($size, $size, intval(($image->width() - $size) / 2), intval(($image->height() - $size) / 2));

            // Add timestamp
            $timestamp = now()->format('d/m/Y H:i:s');
            $image->text($timestamp, 10, $image->height() - 20, function ($font) {
                $font->size(20);
                $font->color('#ffffff');
                $font->align('left');
                $font->valign('bottom');
            });

            // Add school logo if exists
            if ($user->school && $user->school->logo) {
                $logoPath = storage_path('app/public/' . $user->school->logo);
                if (file_exists($logoPath)) {
                    $logo = \Intervention\Image\ImageManagerStatic::make($logoPath)->resize(80, 80);
                    $image->insert($logo, 'top-left', 10, 10);
                }
            }

            $image->save($imagePath, 80); // Save with 80% quality
        } catch (\Exception $e) {
            \Log::error('Error processing image overlay: ' . $e->getMessage());
        }
    }
}
