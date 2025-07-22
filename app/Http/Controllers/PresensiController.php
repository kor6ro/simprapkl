<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use Yajra\DataTables\Facades\DataTables;

class PresensiController extends Controller
{
    public function index()
    {
        return view("administrator.presensi.index");
    }

    public function create()
    {
        return view("administrator.presensi.create");
    }

    public function edit($id)
    {
        $presensi = Presensi::where("id", $id)->first();
        if (!$presensi) {
            return abort(404);
        }

        return view("administrator.presensi.edit", [
            "presensi" => $presensi,
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
            return redirect(route("presensi.create"))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "status_presensi" => $request->input("status_presensi"),
            "tanggal_presensi" => $request->input("tanggal_presensi"),
            "user_id" => $request->input("user_id"),
        ];

        try {
            Presensi::create($dataSave);
            return redirect(route("presensi.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil disimpan",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("presensi.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menyimpan data",
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $presensi = Presensi::query();

        return DataTables::of($presensi)->addIndexColumn()->make(true);
    }

    public function update(Request $request, $id)
    {
        $presensi = Presensi::where("id", $id)->first();
        if (!$presensi) {
            return abort(404);
        }

        $validator = Validator::make($request->all(), [
            "status_presensi" => "required",
            "tanggal_presensi" => "required",
            "user_id" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("presensi.edit", $id))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "status_presensi" => $request->input("status_presensi"),
            "tanggal_presensi" => $request->input("tanggal_presensi"),
            "user_id" => $request->input("user_id"),
        ];

        try {
            $presensi->update($dataSave);
            return redirect(route("presensi.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil diupdate",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("presensi.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data",
            ]);
        }
    }

    public function destroy($id)
    {
        $presensi = Presensi::where("id", $id)->first();
        if (!$presensi) {
            return abort(404);
        }

        try {
            $presensi->delete();
            return redirect(route("presensi.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("presensi.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }
}
