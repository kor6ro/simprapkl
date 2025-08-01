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

        // Jika user adalah siswa, cek apakah data ini milik mereka
        if (isRole('Siswa') && $colectData->user_id != auth()->id()) {
            return abort(403, 'Anda tidak memiliki akses untuk mengedit data ini.');
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
            "user_id" => auth()->id(),
        ];

        if ($request->file("gambar_foto") != null) {
            $file = $request->file("gambar_foto");
            $fileName = $file->hashName();
            $file->move("uploads/colect_data_gambar_foto", $fileName);
            $dataSave["gambar_foto"] = $fileName;
        }

       
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
        // Modifikasi query berdasarkan role user
        $colectData = ColectData::with("user");

        // Jika user adalah siswa, hanya tampilkan data milik mereka sendiri
        if (isRole('Siswa')) {
            $colectData = $colectData->where('user_id', auth()->id());
        }
        // Jika admin atau pembimbing, tampilkan semua data (tidak perlu filter tambahan)

        return DataTables::of($colectData)->addIndexColumn()->make(true);
    }

    public function update(Request $request, $id)
    {
        $colectData = ColectData::where("id", $id)->first();
        if (!$colectData) {
            return abort(404);
        }

        // Jika user adalah siswa, cek apakah data ini milik mereka
        if (isRole('Siswa') && $colectData->user_id != auth()->id()) {
            return abort(403, 'Anda tidak memiliki akses untuk mengupdate data ini.');
        }

        // Validation rules - gambar_foto tidak required untuk update
        $validator = Validator::make($request->all(), [
            "tanggal" => "required",
            "nama_cus" => "required", 
            "no_telp" => "required",
            "alamat_cus" => "required",
            "provider_sekarang" => "required",
            "kelebihan" => "required",
            "kekurangan" => "required",
            "serlok" => "required",
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
            "user_id" => auth()->id(),
        ];

        // Handle upload foto baru jika ada
        if ($request->file("gambar_foto") != null) {
            // Hapus foto lama jika ada
            if ($colectData->gambar_foto && File::exists("uploads/colect_data_gambar_foto/" . $colectData->gambar_foto)) {
                File::delete("uploads/colect_data_gambar_foto/" . $colectData->gambar_foto);
            }
            
            // Upload foto baru
            $file = $request->file("gambar_foto");
            $fileName = $file->hashName();
            $file->move("uploads/colect_data_gambar_foto", $fileName);
            $dataSave["gambar_foto"] = $fileName;
        }

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

        // Jika user adalah siswa, cek apakah data ini milik mereka
        if (isRole('Siswa') && $colectData->user_id != auth()->id()) {
            return abort(403, 'Anda tidak memiliki akses untuk menghapus data ini.');
        }

        try {
            // Hapus foto jika ada sebelum hapus data
            if ($colectData->gambar_foto && File::exists("uploads/colect_data_gambar_foto/" . $colectData->gambar_foto)) {
                File::delete("uploads/colect_data_gambar_foto/" . $colectData->gambar_foto);
            }
            
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