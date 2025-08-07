<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SettingTugas;
use App\Models\SiswaDivisiHarian;
use App\Models\User;

class SettingTugasController extends Controller
{
    public function index()
    {
        $siswa = User::where('group_id', 4)
            ->with(['divisiHarianToday'])
            ->get();
        $settings = \App\Models\SettingTugas::whereDate('tanggal', today())->get();
        return view('administrator.setting_tugas.index', compact('siswa', 'settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'divisi' => 'required|in:teknisi,sales',
            'deskripsi' => 'required',
        ]);

        SettingTugas::create([
            'divisi' => $request->divisi,
            'deskripsi' => $request->deskripsi,
            'tanggal' => today()
        ]);

        return back()->with('success', 'Setting tugas disimpan.');
    }

    public function destroy($id)
    {
        SettingTugas::findOrFail($id)->delete();
        return back()->with('success', 'Setting tugas dihapus.');
    }

    public function setDivisi(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:user,id',
            'divisi' => 'required|in:teknisi,sales',
        ]);

        SiswaDivisiHarian::updateOrCreate(
            ['siswa_id' => $request->siswa_id, 'tanggal' => today()],
            ['divisi' => $request->divisi]
        );

        return redirect()->route('admin.setting_tugas.index')->with('success', 'Divisi siswa berhasil diatur!');
    }

    public function swapDivisi()
    {
        $siswaHariIni = \App\Models\SiswaDivisiHarian::whereDate('tanggal', today())->get();

        foreach ($siswaHariIni as $siswa) {
            $siswa->divisi = $siswa->divisi === 'teknisi' ? 'sales' : 'teknisi';
            $siswa->save();
        }

        return back()->with('success', 'Semua divisi berhasil ditukar!');
    }
}
