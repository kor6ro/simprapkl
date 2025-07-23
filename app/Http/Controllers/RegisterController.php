<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function index()
    {
        $sekolahList = Sekolah::all();
        return view('auth.register', [
            'sekolahList' => $sekolahList,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "email" => "required",
            "password" => "required",
            "username" => "required",
            "sekolah_id" => "required",
            "validasi" => "required",
            "alamat" => "required",
            "group_id" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("login"))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "name" => $request->input("name"),
            "email" => $request->input("email"),
            "password" => Hash::make($request->input("password")),
            "username" => $request->input("username"),
            "sekolah_id" => $request->input("sekolah_id"),
            "validasi" => $request->input("validasi"),
            "alamat" => $request->input("alamat"),
            "group_id" => $request->input("group_id"),
        ];

        try {
            User::create($dataSave);
            return redirect(route("login"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil disimpan",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("login"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menyimpan data",
            ]);
        }
    }
}
