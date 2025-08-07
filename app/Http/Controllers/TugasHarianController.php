<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SettingTugas;
use App\Models\TugasHarian;

class TugasHarianController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $divisi = optional($user->divisiHariIni)->divisi;

        $template = SettingTugas::where('divisi', $divisi)
            ->whereDate('tanggal', today())
            ->get();

        $tugasHariIni = TugasHarian::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->first();

        return view('administrator.tugas_harian.index', compact('template', 'divisi', 'tugasHariIni'));
    }

    public function mulaiTugas(Request $request)
    {
        TugasHarian::updateOrCreate(
            ['user_id' => auth()->id(), 'tanggal' => today()],
            ['mulai' => now()]
        );

        return back()->with('success', 'Tugas dimulai.');
    }

    public function laporTugas(Request $request)
    {
        $request->validate(['laporan' => 'required|string']);

        $tugas = TugasHarian::where('user_id', auth()->id())
            ->whereDate('tanggal', today())->first();

        if ($tugas) {
            $tugas->update([
                'laporan' => $request->laporan,
                'selesai' => now()
            ]);
        }

        return back()->with('success', 'Laporan dikirim.');
    }
}
