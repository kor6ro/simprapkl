<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use Yajra\DataTables\Facades\DataTables;

class GroupController extends Controller
{
    public function index()
    {
        return view("administrator.group.index");
    }

    public function create()
    {
        return view("administrator.group.create");
    }

    public function edit($id)
    {
        $group = Group::where("id", $id)->first();
        if (!$group) {
            return abort(404);
        }

        return view("administrator.group.edit", [
            "group" => $group,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ["nama" => "required"]);

        if ($validator->fails()) {
            return redirect(route("group.create"))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = ["nama" => $request->input("nama")];

        try {
            Group::create($dataSave);
            return redirect(route("group.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil disimpan",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("group.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menyimpan data",
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $group = Group::query();

        return DataTables::of($group)->addIndexColumn()->make(true);
    }

    public function update(Request $request, $id)
    {
        $group = Group::where("id", $id)->first();
        if (!$group) {
            return abort(404);
        }

        $validator = Validator::make($request->all(), ["nama" => "required"]);

        if ($validator->fails()) {
            return redirect(route("group.edit", $id))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = ["nama" => $request->input("nama")];

        try {
            $group->update($dataSave);
            return redirect(route("group.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil diupdate",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("group.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data",
            ]);
        }
    }

    public function destroy($id)
    {
        $group = Group::where("id", $id)->first();
        if (!$group) {
            return abort(404);
        }

        try {
            $group->delete();
            return redirect(route("group.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("group.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }
}
