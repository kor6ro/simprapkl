<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PresensiSetting;

class PresensiSettingController extends Controller
{
    public function index()
    {
        $setting = PresensiSetting::first();
        return view('administrator.presensi_setting.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'pagi_mulai' => 'required|date_format:H:i',
            'pagi_selesai' => 'required|date_format:H:i|after:pagi_mulai',
            'sore_mulai' => 'required|date_format:H:i',
            'sore_selesai' => 'required|date_format:H:i|after:sore_mulai',
        ]);

        $setting = PresensiSetting::first();
        $setting->update($request->only([
            'pagi_mulai',
            'pagi_selesai',
            'sore_mulai',
            'sore_selesai'
        ]));

        return redirect()->route('presensi-setting.index')->with('success', 'Presensi setting berhasil diperbarui.');
    }
}
