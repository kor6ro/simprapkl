<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProgramKeahlian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class ProgramKeahlianController extends Controller
{
    public function index()
    {
        return view("administrator.program_keahlian.index");
    }

    public function create()
    {
        return view("administrator.program_keahlian.create");
    }

    public function edit(ProgramKeahlian $programKeahlian)
    {
        return view("administrator.program_keahlian.edit", [
            "programKeahlian" => $programKeahlian,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ["nama" => "required|string|max:255"]);

        if ($validator->fails()) {
            return redirect(route("admin.program-keahlian.create"))
                ->withErrors($validator)
                ->withInput();
        }

        ProgramKeahlian::create($request->only('nama'));

        return redirect(route("admin.program-keahlian.index"))->with([
            "dataSaved" => true,
            "message" => "Data berhasil disimpan",
        ]);
    }

    public function fetch(Request $request)
    {
        $query = ProgramKeahlian::query();
        return DataTables::of($query)->addIndexColumn()->make(true);
    }

    public function update(Request $request, ProgramKeahlian $programKeahlian)
    {
        $validator = Validator::make($request->all(), ["nama" => "required|string|max:255"]);

        if ($validator->fails()) {
            return redirect(route('admin.program-keahlian.edit', $programKeahlian->id))
                ->withErrors($validator)
                ->withInput();
        }

        $programKeahlian->update($request->only('nama'));

        return redirect(route("admin.program-keahlian.index"))->with([
            "dataSaved" => true,
            "message" => "Data berhasil diupdate",
        ]);
    }

    public function destroy(ProgramKeahlian $programKeahlian)
    {
        $programKeahlian->delete();
        return redirect(route("admin.program-keahlian.index"))->with([
            "dataSaved" => true,
            "message" => "Data berhasil dihapus",
        ]);
    }

    public function ajaxStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255|unique:program_keahlian,nama',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $program = ProgramKeahlian::create($request->only('nama'));

            return response()->json(['success' => true, 'program' => $program]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan data. ' . $e->getMessage()]);
        }
    }
}
