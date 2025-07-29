<?php

namespace App\Http\Controllers;

use App\Models\PresensiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PresensiSettingController extends Controller
{
    public function index()
    {
        return view("administrator.presensi_setting.index");
    }

    public function create()
    {
        return view("administrator.presensi_setting.create");
    }

    public function edit($id)
    {
        $setting = PresensiSetting::findOrFail($id);
        return view("administrator.presensi_setting.edit", [
            "presensi_setting" => $setting,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jam_masuk' => 'required|date_format:H:i',
            'jam_pulang' => 'required|date_format:H:i',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->route("presensi_setting.create")
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->has('is_active')) {
            PresensiSetting::query()->update(['is_active' => false]);
        }

        try {
            PresensiSetting::create([
                'jam_masuk' => $request->input('jam_masuk'),
                'jam_pulang' => $request->input('jam_pulang'),
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route("presensi_setting.index")->with([
                "dataSaved" => true,
                "message" => "Data berhasil disimpan",
            ]);
        } catch (\Throwable $th) {
            return redirect()->route("presensi_setting.index")->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menyimpan data",
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $setting = PresensiSetting::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'jam_masuk' => 'required|date_format:H:i',
            'jam_pulang' => 'required|date_format:H:i',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->route("presensi_setting.edit", $id)
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->has('is_active')) {
            PresensiSetting::query()->update(['is_active' => false]);
        }

        try {
            $setting->update([
                'jam_masuk' => $request->input('jam_masuk'),
                'jam_pulang' => $request->input('jam_pulang'),
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route("presensi_setting.index")->with([
                "dataSaved" => true,
                "message" => "Data berhasil diupdate",
            ]);
        } catch (\Throwable $th) {
            return redirect()->route("presensi_setting.index")->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data",
            ]);
        }
    }

    public function destroy($id)
    {
        $setting = PresensiSetting::findOrFail($id);

        try {
            $setting->delete();
            return redirect()->route("presensi_setting.index")->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect()->route("presensi_setting.index")->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $data = PresensiSetting::query();

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('is_active', fn($row) => $row->is_active ? 'Aktif' : 'Tidak Aktif')
            ->make(true);
    }
}
