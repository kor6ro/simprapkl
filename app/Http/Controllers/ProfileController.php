<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $profile = User::with("sekolah", "group")
            ->where("id", Auth::id())
            ->first();
        if (!$profile) {
            return abort(404);
        }

        return view("administrator.profile.index", ["profile" => $profile]);
    }

    public function edit()
    {
        $profile = User::where("id", Auth::id())->first();
        if (!$profile) {
            return abort(404);
        }

        $sekolah = Sekolah::all();
        $group = Group::all();

        $data = ["sekolah" => $sekolah, "group" => $group];

        return view("administrator.profile.edit", [
            "profile" => $profile,
            ...$data,
        ]);
    }

    public function save(Request $request)
    {
        $profile = User::where("id", Auth::id())->first();
        if (!$profile) {
            return abort(404);
        }

        $validator = Validator::make($request->all(), [
            "name" => "required",
            "username" => "required",
            "email" => "required",
            "sekolah_id" => "required",
            "alamat" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("profile.edit"))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "name" => $request->input("name"),
            "username" => $request->input("username"),
            "email" => $request->input("email"),
            "sekolah_id" => $request->input("sekolah_id"),
            "alamat" => $request->input("alamat"),
        ];

        try {
            $profile->update($dataSave);
            return redirect(route("profile.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil diupdate",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("profile.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data",
            ]);
        }
    }
}
