<?php

namespace App\Http\Controllers;

use App\Models\SettingPresensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use Yajra\DataTables\Facades\DataTables;

class SettingPresensiController extends Controller
{
    public function index()
    {
        return view("administrator.setting_presensi.index");
    }

    public function create()
    {
        return view("administrator.setting_presensi.create");
    }

    public function edit($id)
    {
        $settingPresensi = SettingPresensi::where("id", $id)->first();
        if (!$settingPresensi) {
            return abort(404);
        }

        return view("administrator.setting_presensi.edit", [
            "setting_presensi" => $settingPresensi,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "status_presensi" => "required",
            "tanggal_presensi" => "required",
            "user_id" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("setting_presensi.create"))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "status_presensi" => $request->input("status_presensi"),
            "tanggal_presensi" => $request->input("tanggal_presensi"),
            "user_id" => $request->input("user_id"),
        ];

        try {
            SettingPresensi::create($dataSave);
            return redirect(route("setting_presensi.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil disimpan",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("setting_presensi.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menyimpan data",
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $settingPresensi = SettingPresensi::query();

        return DataTables::of($settingPresensi)->addIndexColumn()->make(true);
    }

    public function update(Request $request, $id)
    {
        $settingPresensi = SettingPresensi::where("id", $id)->first();
        if (!$settingPresensi) {
            return abort(404);
        }

        $validator = Validator::make($request->all(), [
            "status_presensi" => "required",
            "tanggal_presensi" => "required",
            "user_id" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("setting_presensi.edit", $id))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "status_presensi" => $request->input("status_presensi"),
            "tanggal_presensi" => $request->input("tanggal_presensi"),
            "user_id" => $request->input("user_id"),
        ];

        try {
            $settingPresensi->update($dataSave);
            return redirect(route("setting_presensi.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil diupdate",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("setting_presensi.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data",
            ]);
        }
    }

    public function destroy($id)
    {
        $settingPresensi = SettingPresensi::where("id", $id)->first();
        if (!$settingPresensi) {
            return abort(404);
        }

        try {
            $settingPresensi->delete();
            return redirect(route("setting_presensi.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("setting_presensi.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }
}
