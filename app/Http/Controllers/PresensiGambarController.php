<?php

namespace App\Http\Controllers;

use App\Models\PresensiGambar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use Yajra\DataTables\Facades\DataTables;

class PresensiGambarController extends Controller
{
    public function index()
    {
        return view("administrator.presensi_gambar.index");
    }

    public function create()
    {
        return view("administrator.presensi_gambar.create");
    }

    public function edit($id)
    {
        $presensiGambar = PresensiGambar::where("id", $id)->first();
        if (!$presensiGambar) {
            return abort(404);
        }

        return view("administrator.presensi_gambar.edit", [
            "presensi_gambar" => $presensiGambar,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "gmbr_presensi_pagi" => "required",
            "gmbr_presensi_sore" => "required",
            "presensi_id" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("presensi_gambar.create"))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "gmbr_presensi_pagi" => $request->input("gmbr_presensi_pagi"),
            "gmbr_presensi_sore" => $request->input("gmbr_presensi_sore"),
            "presensi_id" => $request->input("presensi_id"),
        ];

        try {
            PresensiGambar::create($dataSave);
            return redirect(route("presensi_gambar.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil disimpan",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("presensi_gambar.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menyimpan data",
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $presensiGambar = PresensiGambar::query();

        return DataTables::of($presensiGambar)->addIndexColumn()->make(true);
    }

    public function update(Request $request, $id)
    {
        $presensiGambar = PresensiGambar::where("id", $id)->first();
        if (!$presensiGambar) {
            return abort(404);
        }

        $validator = Validator::make($request->all(), [
            "gmbr_presensi_pagi" => "required",
            "gmbr_presensi_sore" => "required",
            "presensi_id" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("presensi_gambar.edit", $id))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "gmbr_presensi_pagi" => $request->input("gmbr_presensi_pagi"),
            "gmbr_presensi_sore" => $request->input("gmbr_presensi_sore"),
            "presensi_id" => $request->input("presensi_id"),
        ];

        try {
            $presensiGambar->update($dataSave);
            return redirect(route("presensi_gambar.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil diupdate",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("presensi_gambar.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data",
            ]);
        }
    }

    public function destroy($id)
    {
        $presensiGambar = PresensiGambar::where("id", $id)->first();
        if (!$presensiGambar) {
            return abort(404);
        }

        try {
            $presensiGambar->delete();
            return redirect(route("presensi_gambar.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("presensi_gambar.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }
}
