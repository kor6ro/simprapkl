<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PresensiSetting;

class PresensiSettingController extends Controller
{
    public function index()
    {
        $setting = PresensiSetting::firstOrNew(['id' => 1]);

        return view('administrator.presensi_setting.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'pagi_mulai' => 'nullable|date_format:H:i',
            'pagi_selesai' => 'nullable|date_format:H:i|after:pagi_mulai',
            'sore_mulai' => 'nullable|date_format:H:i',
            'sore_selesai' => 'nullable|date_format:H:i|after:sore_mulai',
            'toleransi_telat' => 'required|integer|min:0|max:60', // Toleransi tetap wajib diisi
        ]);

        $data = $request->only([
            'pagi_mulai',
            'pagi_selesai',
            'sore_mulai',
            'sore_selesai',
            'toleransi_telat'
        ]);

        // Logika updateOrCreate sudah tepat, tidak perlu diubah.
        // Ia akan mengisi kolom dengan null jika input dikosongkan.
        PresensiSetting::updateOrCreate(['id' => 1], $data);

        return redirect()->route('admin.presensi_setting.index')
            ->with('success', 'Pengaturan presensi berhasil diperbarui.');
    }
}