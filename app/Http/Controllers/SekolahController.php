<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use Yajra\DataTables\Facades\DataTables;

class SekolahController extends Controller
{
    public function index()
    {
        return view("administrator.sekolah.index");
    }

    public function create()
    {
        return view("administrator.sekolah.create");
    }

    public function edit($id)
    {
        $sekolah = Sekolah::where("id", $id)->first();
        if (!$sekolah) {
            return abort(404);
        }

        return view("administrator.sekolah.edit", [
            "sekolah" => $sekolah,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ["nama" => "required"]);

        if ($validator->fails()) {
            return redirect(route("sekolah.create"))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = ["nama" => $request->input("nama")];

        try {
            Sekolah::create($dataSave);
            return redirect(route("sekolah.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil disimpan",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("sekolah.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menyimpan data",
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $sekolah = Sekolah::query();

        return DataTables::of($sekolah)->addIndexColumn()->make(true);
    }

    public function update(Request $request, $id)
    {
        $sekolah = Sekolah::where("id", $id)->first();
        if (!$sekolah) {
            return abort(404);
        }

        $validator = Validator::make($request->all(), ["nama" => "required"]);

        if ($validator->fails()) {
            return redirect(route("sekolah.edit", $id))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = ["nama" => $request->input("nama")];

        try {
            $sekolah->update($dataSave);
            return redirect(route("sekolah.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil diupdate",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("sekolah.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data",
            ]);
        }
    }

    public function destroy($id)
    {
        $sekolah = Sekolah::where("id", $id)->first();
        if (!$sekolah) {
            return abort(404);
        }

        try {
            $sekolah->delete();
            return redirect(route("sekolah.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("sekolah.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }
}
