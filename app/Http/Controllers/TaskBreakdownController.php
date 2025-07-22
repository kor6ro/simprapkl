<?php

namespace App\Http\Controllers;

use App\Models\TaskBreakdown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use Yajra\DataTables\Facades\DataTables;

class TaskBreakdownController extends Controller
{
    public function index()
    {
        return view("administrator.task_breakdown.index");
    }

    public function create()
    {
        return view("administrator.task_breakdown.create");
    }

    public function edit($id)
    {
        $taskBreakdown = TaskBreakdown::where("id", $id)->first();
        if (!$taskBreakdown) {
            return abort(404);
        }

        return view("administrator.task_breakdown.edit", [
            "task_breakdown" => $taskBreakdown,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ["file" => "required"]);

        if ($validator->fails()) {
            return redirect(route("task_breakdown.create"))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = ["file" => $request->input("file")];

        try {
            TaskBreakdown::create($dataSave);
            return redirect(route("task_breakdown.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil disimpan",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("task_breakdown.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menyimpan data",
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $taskBreakdown = TaskBreakdown::query();

        return DataTables::of($taskBreakdown)->addIndexColumn()->make(true);
    }

    public function update(Request $request, $id)
    {
        $taskBreakdown = TaskBreakdown::where("id", $id)->first();
        if (!$taskBreakdown) {
            return abort(404);
        }

        $validator = Validator::make($request->all(), ["file" => "required"]);

        if ($validator->fails()) {
            return redirect(route("task_breakdown.edit", $id))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = ["file" => $request->input("file")];

        try {
            $taskBreakdown->update($dataSave);
            return redirect(route("task_breakdown.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil diupdate",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("task_breakdown.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data",
            ]);
        }
    }

    public function destroy($id)
    {
        $taskBreakdown = TaskBreakdown::where("id", $id)->first();
        if (!$taskBreakdown) {
            return abort(404);
        }

        try {
            $taskBreakdown->delete();
            return redirect(route("task_breakdown.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("task_breakdown.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }
}
