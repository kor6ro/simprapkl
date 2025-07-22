<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ColectData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use Yajra\DataTables\Facades\DataTables;

class ColectDataController extends Controller
{
    public function index()
    {
        return view("administrator.colect_data.index");
    }

    public function create()
    {
        $user = User::all();

        $data = ["user" => $user];

        return view("administrator.colect_data.create", $data);
    }

    public function edit($id)
    {
        $colectData = ColectData::where("id", $id)->first();
        if (!$colectData) {
            return abort(404);
        }

        $user = User::all();

        $data = ["user" => $user];

        return view("administrator.colect_data.edit", [
            "colect_data" => $colectData,
            ...$data,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "tanggal" => "required",
            "nama_cus" => "required",
            "no_telp" => "required",
            "alamat_cus" => "required",
            "provider_sekarang" => "required",
            "kelebihan" => "required",
            "kekurangan" => "required",
            "serlok" => "required",
            "gambar_foto" => "required",
            "user_id" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("colect_data.create"))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "tanggal" => $request->input("tanggal"),
            "nama_cus" => $request->input("nama_cus"),
            "no_telp" => $request->input("no_telp"),
            "alamat_cus" => $request->input("alamat_cus"),
            "provider_sekarang" => $request->input("provider_sekarang"),
            "kelebihan" => $request->input("kelebihan"),
            "kekurangan" => $request->input("kekurangan"),
            "serlok" => $request->input("serlok"),
            "gambar_foto" => $request->input("gambar_foto"),
            "user_id" => $request->input("user_id"),
        ];

        try {
            ColectData::create($dataSave);
            return redirect(route("colect_data.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil disimpan",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("colect_data.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menyimpan data",
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $colectData = ColectData::with("user");

        return DataTables::of($colectData)->addIndexColumn()->make(true);
    }

    public function update(Request $request, $id)
    {
        $colectData = ColectData::where("id", $id)->first();
        if (!$colectData) {
            return abort(404);
        }

        $validator = Validator::make($request->all(), [
            "tanggal" => "required",
            "nama_cus" => "required",
            "no_telp" => "required",
            "alamat_cus" => "required",
            "provider_sekarang" => "required",
            "kelebihan" => "required",
            "kekurangan" => "required",
            "serlok" => "required",
            "gambar_foto" => "required",
            "user_id" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("colect_data.edit", $id))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "tanggal" => $request->input("tanggal"),
            "nama_cus" => $request->input("nama_cus"),
            "no_telp" => $request->input("no_telp"),
            "alamat_cus" => $request->input("alamat_cus"),
            "provider_sekarang" => $request->input("provider_sekarang"),
            "kelebihan" => $request->input("kelebihan"),
            "kekurangan" => $request->input("kekurangan"),
            "serlok" => $request->input("serlok"),
            "gambar_foto" => $request->input("gambar_foto"),
            "user_id" => $request->input("user_id"),
        ];

        try {
            $colectData->update($dataSave);
            return redirect(route("colect_data.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil diupdate",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("colect_data.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data",
            ]);
        }
    }

    public function destroy($id)
    {
        $colectData = ColectData::where("id", $id)->first();
        if (!$colectData) {
            return abort(404);
        }

        try {
            $colectData->delete();
            return redirect(route("colect_data.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("colect_data.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }
}
