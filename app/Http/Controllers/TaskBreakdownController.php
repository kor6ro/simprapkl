<?php

namespace App\Http\Controllers;

use App\Models\TaskBreakDown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;

class TaskBreakDownController extends Controller
{
    public function index()
    {

        $tugasHariIni = TaskBreakDown::whereDate('created_at', Carbon::today())->get();

        return view('administrator.task_break_down.index', compact('tugasHariIni'));

        return view("administrator.task_break_down.index");
    }

    public function create()
    {
        return view("administrator.task_break_down.create");
    }

    public function edit($id)
    {
        $taskBreakDown = TaskBreakDown::where("id", $id)->first();
        if (!$taskBreakDown) {
            return abort(404);
        }
        return view("administrator.task_break_down.edit", [
            "task_break_down" => $taskBreakDown,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "nama" => "required",
            "file_upload" => "required",
        ]);
        if ($validator->fails()) {
            return redirect(route("task_break_down.create"))
                ->withErrors($validator)
                ->withInput();
        }
        $dataSave = ["nama" => $request->input("nama")];
        if ($request->file("file_upload") != null) {
            $file = $request->file("file_upload");
            $fileName = $file->hashName();
            $file->move("uploads/task_break_down_file_upload", $fileName);
            $dataSave["file_upload"] = $fileName;
        }
        try {
            TaskBreakDown::create($dataSave);
            return redirect(route("task_break_down.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil disimpan",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("task_break_down.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menyimpan data",
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $taskBreakDown = TaskBreakDown::query();
        return DataTables::of($taskBreakDown)->addIndexColumn()->make(true);
    }

    public function update(Request $request, $id)
    {
        $taskBreakDown = TaskBreakDown::where("id", $id)->first();
        if (!$taskBreakDown) {
            return abort(404);
        }
        $validator = Validator::make($request->all(), [
            "nama" => "required",
            "file_upload" => "required",
        ]);
        if ($validator->fails()) {
            return redirect(route("task_break_down.edit", $id))
                ->withErrors($validator)
                ->withInput();
        }
        $dataSave = ["nama" => $request->input("nama")];
        if ($request->file("file_upload") != null) {
            File::delete(
                public_path(
                    "uploads/task_break_down_file_upload/" .
                        @$taskBreakDown->file_upload
                )
            );
            $file = $request->file("file_upload");
            $fileName = $file->hashName();
            $file->move("uploads/task_break_down_file_upload", $fileName);
            $dataSave["file_upload"] = $fileName;
        }
        try {
            $taskBreakDown->update($dataSave);
            return redirect(route("task_break_down.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil diupdate",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("task_break_down.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data",
            ]);
        }
    }

    public function destroy($id)
    {
        $taskBreakDown = TaskBreakDown::where("id", $id)->first();
        if (!$taskBreakDown) {
            return abort(404);
        }
        File::delete(
            public_path(
                "uploads/task_break_down_file_upload/" .
                    @$taskBreakDown->file_upload
            )
        );
        try {
            $taskBreakDown->delete();
            return redirect(route("task_break_down.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("task_break_down.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }
}
