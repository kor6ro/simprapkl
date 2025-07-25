<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use app\Helpers\UserRoles;


use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        return view("administrator.user.index");
    }

    public function create()
    {
        $sekolah = Sekolah::all();
        $group = Group::all();

        $data = ["sekolah" => $sekolah, "group" => $group];

        return view("administrator.user.create", $data);
    }

    public function edit($id)
    {
        $user = User::where("id", $id)->first();
        if (!$user) {
            return abort(404);
        }

        $sekolah = Sekolah::all();
        $group = Group::all();

        $data = ["sekolah" => $sekolah, "group" => $group];

        return view("administrator.user.edit", [
            "user" => $user,
            ...$data,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "username" => "required",
            "email" => "required",
            "password" => "required",
            "validasi" => "required",
            "sekolah_id" => "required",
            "group_id" => "required",
            "alamat" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("user.create"))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "name" => $request->input("name"),
            "username" => $request->input("username"),
            "email" => $request->input("email"),
            "password" => $request->input("password"),
            "validasi" => $request->input("validasi"),
            "sekolah_id" => $request->input("sekolah_id"),
            "group_id" => $request->input("group_id"),
            "alamat" => $request->input("alamat"),
        ];

        try {
            User::create($dataSave);
            return redirect(route("user.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil disimpan",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("user.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menyimpan data",
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $user = User::where("id", "<>", 1)->with("sekolah", "group");

        return DataTables::of($user)->addIndexColumn()->make(true);
    }

    public function update(Request $request, $id)
    {
        $user = User::where("id", $id)->first();
        if (!$user) {
            return abort(404);
        }

        $validator = Validator::make($request->all(), [
            "name" => "required",
            "username" => "required",
            "email" => "required",
            "password" => "required",
            "validasi" => "required",
            "sekolah_id" => "required",
            "group_id" => "required",
            "alamat" => "required",
        ]);

        if ($validator->fails()) {
            return redirect(route("user.edit", $id))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "name" => $request->input("name"),
            "username" => $request->input("username"),
            "email" => $request->input("email"),
            "password" => $request->input("password"),
            "validasi" => $request->input("validasi"),
            "sekolah_id" => $request->input("sekolah_id"),
            "group_id" => $request->input("group_id"),
            "alamat" => $request->input("alamat"),
        ];

        try {
            $user->update($dataSave);
            return redirect(route("user.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil diupdate",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("user.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data",
            ]);
        }
    }

    public function destroy($id)
    {
        $user = User::where("id", $id)->first();
        if (!$user) {
            return abort(404);
        }

        try {
            $user->delete();
            return redirect(route("user.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("user.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }
}
