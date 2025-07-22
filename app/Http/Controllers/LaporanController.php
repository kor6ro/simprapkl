<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JenisLaporan;
use App\Models\LaporanGambar;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use Yajra\DataTables\Facades\DataTables;

class LaporanController extends Controller
{
    public function index()
    {
        return view("administrator.laporan.index");
    }

    public function create()
    {
        $user = User::all();
        $jenisLaporan = JenisLaporan::all();
        $laporanGambar = LaporanGambar::all();

        $data = [
            "user" => $user,
            "jenis_laporan" => $jenisLaporan,
            "laporan_gambar" => $laporanGambar,
        ];

        return view("administrator.laporan.create", $data);
    }

    public function edit($id)
    {
        $laporan = Laporan::where("id", $id)->first();
        if (!$laporan) {
            return abort(404);
        }

        $user = User::all();
        $jenisLaporan = JenisLaporan::all();
        $laporanGambar = LaporanGambar::all();

        $data = [
            "user" => $user,
            "jenis_laporan" => $jenisLaporan,
            "laporan_gambar" => $laporanGambar,
        ];

        return view("administrator.laporan.edit", [
            "laporan" => $laporan,
            ...$data,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "jenis_kegiatan" => "required",
            "lokasi" => "required",
            "homepass" => "required",
            "jml_orang_ditemui" => "required",
            "detail_pekerjaan" => "required",
            "hasil_capaian" => "required",
            "user_id" => "required",
            "jenis_laporan_id" => "required",
            "laporan_gambar_id" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("laporan.create"))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "jenis_kegiatan" => $request->input("jenis_kegiatan"),
            "lokasi" => $request->input("lokasi"),
            "homepass" => $request->input("homepass"),
            "jml_orang_ditemui" => $request->input("jml_orang_ditemui"),
            "detail_pekerjaan" => $request->input("detail_pekerjaan"),
            "hasil_capaian" => $request->input("hasil_capaian"),
            "user_id" => $request->input("user_id"),
            "jenis_laporan_id" => $request->input("jenis_laporan_id"),
            "laporan_gambar_id" => $request->input("laporan_gambar_id"),
        ];

        try {
            Laporan::create($dataSave);
            return redirect(route("laporan.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil disimpan",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("laporan.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menyimpan data",
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $laporan = Laporan::with("user", "jenislaporan", "laporangambar");

        return DataTables::of($laporan)->addIndexColumn()->make(true);
    }

    public function update(Request $request, $id)
    {
        $laporan = Laporan::where("id", $id)->first();
        if (!$laporan) {
            return abort(404);
        }

        $validator = Validator::make($request->all(), [
            "jenis_kegiatan" => "required",
            "lokasi" => "required",
            "homepass" => "required",
            "jml_orang_ditemui" => "required",
            "detail_pekerjaan" => "required",
            "hasil_capaian" => "required",
            "user_id" => "required",
            "jenis_laporan_id" => "required",
            "laporan_gambar_id" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("laporan.edit", $id))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "jenis_kegiatan" => $request->input("jenis_kegiatan"),
            "lokasi" => $request->input("lokasi"),
            "homepass" => $request->input("homepass"),
            "jml_orang_ditemui" => $request->input("jml_orang_ditemui"),
            "detail_pekerjaan" => $request->input("detail_pekerjaan"),
            "hasil_capaian" => $request->input("hasil_capaian"),
            "user_id" => $request->input("user_id"),
            "jenis_laporan_id" => $request->input("jenis_laporan_id"),
            "laporan_gambar_id" => $request->input("laporan_gambar_id"),
        ];

        try {
            $laporan->update($dataSave);
            return redirect(route("laporan.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil diupdate",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("laporan.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data",
            ]);
        }
    }

    public function destroy($id)
    {
        $laporan = Laporan::where("id", $id)->first();
        if (!$laporan) {
            return abort(404);
        }

        try {
            $laporan->delete();
            return redirect(route("laporan.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("laporan.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }
}
