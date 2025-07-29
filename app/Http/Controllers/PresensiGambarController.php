<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\PresensiGambar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class PresensiGambarController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'presensi_id' => 'required|exists:presensi,id',
            'bukti' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = $request->file('bukti')->store('bukti_presensi', 'public');

        PresensiGambar::create([
            'presensi_id' => $request->presensi_id,
            'bukti' => $path,
        ]);

        return redirect()->back()->with([
            'dataSaved' => true,
            'message' => 'Gambar berhasil diunggah',
        ]);
    }

    public function destroy($id)
    {
        $gambar = PresensiGambar::findOrFail($id);

        // Hapus file dari storage jika ada
        if ($gambar->bukti && Storage::disk('public')->exists($gambar->bukti)) {
            Storage::disk('public')->delete($gambar->bukti);
        }


        $gambar->delete();

        return redirect()->back()->with([
            'dataSaved' => true,
            'message' => 'Gambar berhasil dihapus',
        ]);
    }
}
