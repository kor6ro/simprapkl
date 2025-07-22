<?php

namespace App\Http\Controllers;

use App\Models\LaporanGambar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use Yajra\DataTables\Facades\DataTables;

class LaporanGambarController extends Controller
{
    public function index()
    {
        return view("administrator.laporan_gambar.index");
    }

    public function create()
    {
        return view("administrator.laporan_gambar.create");
    }

    public function edit($id)
    {
        $laporanGambar = LaporanGambar::where("id", $id)->first();
        if (!$laporanGambar) {
            return abort(404);
        }

        return view("administrator.laporan_gambar.edit", [
            "laporan_gambar" => $laporanGambar,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "gambar" => "required",
            "laporan_id" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("laporan_gambar.create"))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "gambar" => $request->input("gambar"),
            "laporan_id" => $request->input("laporan_id"),
        ];

        try {
            LaporanGambar::create($dataSave);
            return redirect(route("laporan_gambar.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil disimpan",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("laporan_gambar.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menyimpan data",
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $laporanGambar = LaporanGambar::query();

        return DataTables::of($laporanGambar)->addIndexColumn()->make(true);
    }

    public function update(Request $request, $id)
    {
        $laporanGambar = LaporanGambar::where("id", $id)->first();
        if (!$laporanGambar) {
            return abort(404);
        }

        $validator = Validator::make($request->all(), [
            "gambar" => "required",
            "laporan_id" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("laporan_gambar.edit", $id))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "gambar" => $request->input("gambar"),
            "laporan_id" => $request->input("laporan_id"),
        ];

        try {
            $laporanGambar->update($dataSave);
            return redirect(route("laporan_gambar.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil diupdate",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("laporan_gambar.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data",
            ]);
        }
    }

    public function destroy($id)
    {
        $laporanGambar = LaporanGambar::where("id", $id)->first();
        if (!$laporanGambar) {
            return abort(404);
        }

        try {
            $laporanGambar->delete();
            return redirect(route("laporan_gambar.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("laporan_gambar.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }
}
