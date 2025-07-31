<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\PresensiSetting;
use App\Models\PresensiStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Presensi::with(['user', 'presensiStatus'])->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama', fn($row) => $row->user->name)
                ->addColumn('status', fn($row) => $row->presensiStatus->status)
                ->addColumn('bukti_foto', function ($row) {
                    if ($row->bukti_foto) {
                        return '<a href="' . asset('storage/' . $row->bukti_foto) . '" target="_blank">
                                    <img src="' . asset('storage/' . $row->bukti_foto) . '" width="60">
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
        $presensistatus = PresensiStatus::all();
        return view('administrator.presensi.create', compact('presensistatus', 'user'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'presensi_status_id' => 'required|exists:presensi_status,id',
            'tanggal_presensi' => 'required|date',
            'bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user_id = Auth::id();
        $jamSekarang = Carbon::now();
        $tanggal = $request->tanggal_presensi;

        $status = PresensiStatus::findOrFail($request->presensi_status_id);
        $setting = PresensiSetting::first();

        // Jika status adalah izin atau sakit → input dua sesi (pagi & sore)
        if (in_array(strtolower($status->status), ['izin', 'sakit'])) {
            foreach (['pagi', 'sore'] as $sesi) {
                $sudah = Presensi::where('user_id', $user_id)
                    ->where('tanggal_presensi', $tanggal)
                    ->where('sesi', $sesi)
                    ->exists();

                if (!$sudah) {
                    $buktiPath = null;
                    if ($status->butuh_bukti && $request->hasFile('bukti')) {
                        $buktiPath = $request->file('bukti')->store('uploads/presensi', 'public');
                    }

                    Presensi::create([
                        'user_id' => $user_id,
                        'presensi_status_id' => $request->presensi_status_id,
                        'tanggal_presensi' => $tanggal,
                        'sesi' => $sesi,
                        'jam_presensi' => $jamSekarang->format('H:i:s'),
                        'bukti_foto' => $buktiPath,
                        'keterangan' => $request->keterangan,
                        'status_verifikasi' => 'pending',
                    ]);
                }
            }

            return redirect()->route('presensi.index')->with('success', 'Presensi Izin/Sakit berhasil disimpan untuk dua sesi.');
        }

        // Status Hadir otomatis
        $sesi = $jamSekarang->format('H:i:s') <= $setting->pagi_selesai ? 'pagi' : 'sore';

        $sudah = Presensi::where('user_id', $user_id)
            ->where('tanggal_presensi', $tanggal)
            ->where('sesi', $sesi)
            ->exists();

        if ($sudah) {
            return back()->with('error', 'Sudah melakukan presensi untuk sesi ini.');
        }

        $telat = ($sesi === 'pagi' && $jamSekarang->format('H:i:s') > $setting->pagi_selesai) ||
            ($sesi === 'sore' && $jamSekarang->format('H:i:s') > $setting->sore_selesai);

        $buktiPath = null;
        if ($status->butuh_bukti && $request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('uploads/presensi', 'public');
        }

        Presensi::create([
            'user_id' => $user_id,
            'presensi_status_id' => $request->presensi_status_id,
            'tanggal_presensi' => $tanggal,
            'sesi' => $sesi,
            'jam_presensi' => $jamSekarang->format('H:i:s'),
            'bukti_foto' => $buktiPath,
            'keterangan' => $request->keterangan,
            'status_verifikasi' => 'pending',
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
            'presensi_status_id' => 'required|exists:presensi_status,id',
            'status_verifikasi' => 'required|in:pending,valid,tidak valid',
            'bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $presensi = Presensi::findOrFail($id);
        $data = $request->only(['presensi_status_id', 'status_verifikasi', 'catatan_verifikasi', 'keterangan']);

        if ($request->hasFile('bukti')) {
            if ($presensi->bukti_foto && Storage::disk('public')->exists($presensi->bukti_foto)) {
                Storage::disk('public')->delete($presensi->bukti_foto);
            }

            $data['bukti_foto'] = $request->file('bukti')->store('uploads/presensi', 'public');
        } else {
            $data['bukti_foto'] = $presensi->bukti_foto;
        }

        $presensi->update($data);

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
}
