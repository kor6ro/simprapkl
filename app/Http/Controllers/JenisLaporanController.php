<?php

namespace App\Http\Controllers;

use App\Models\JenisLaporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use Yajra\DataTables\Facades\DataTables;

class JenisLaporanController extends Controller
{
    public function index()
    {
        return view("administrator.jenis_laporan.index");
    }

    public function create()
    {
        return view("administrator.jenis_laporan.create");
    }

    public function edit($id)
    {
        $jenisLaporan = JenisLaporan::where("id", $id)->first();
        if (!$jenisLaporan) {
            return abort(404);
        }

        return view("administrator.jenis_laporan.edit", [
            "jenis_laporan" => $jenisLaporan,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ["nama" => "required"]);

        if ($validator->fails()) {
            return redirect(route("jenis_laporan.create"))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = ["nama" => $request->input("nama")];

        try {
            JenisLaporan::create($dataSave);
            return redirect(route("jenis_laporan.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil disimpan",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("jenis_laporan.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menyimpan data",
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $jenisLaporan = JenisLaporan::query();

        return DataTables::of($jenisLaporan)->addIndexColumn()->make(true);
    }

    public function update(Request $request, $id)
    {
        $jenisLaporan = JenisLaporan::where("id", $id)->first();
        if (!$jenisLaporan) {
            return abort(404);
        }

        $validator = Validator::make($request->all(), ["nama" => "required"]);

        if ($validator->fails()) {
            return redirect(route("jenis_laporan.edit", $id))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = ["nama" => $request->input("nama")];

        try {
            $jenisLaporan->update($dataSave);
            return redirect(route("jenis_laporan.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil diupdate",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("jenis_laporan.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data",
            ]);
        }
    }

    public function destroy($id)
    {
        $jenisLaporan = JenisLaporan::where("id", $id)->first();
        if (!$jenisLaporan) {
            return abort(404);
        }

        try {
            $jenisLaporan->delete();
            return redirect(route("jenis_laporan.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("jenis_laporan.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }
}
