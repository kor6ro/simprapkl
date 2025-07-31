<?php

namespace App\Http\Controllers;

use App\Models\PresensiStatus;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PresensiStatusController extends Controller
{
    public function index()
    {
        return view('administrator.presensi_status.index');
    }

    public function create()
    {
        return view('administrator.presensi_status.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'status' => 'required|string|max:255',
        ]);

        PresensiStatus::create([
            'status' => $request->status,
        ]);


        return redirect()->route('presensi_status.index')->with([
            'dataSaved' => true,
            'message' => 'Data berhasil disimpan',
        ]);
    }

    public function edit($id)
    {
        $status = PresensiStatus::findOrFail($id);
        return view('administrator.presensi_status.edit', compact('status'));
    }

    public function update(Request $request, $id)
    {
        $status = PresensiStatus::findOrFail($id);

        $request->validate([
            'status' => 'required|string|max:255',
        ]);

        $status->update([
            'status' => $request->status,
        ]);


        return redirect()->route('presensi_status.index')->with([
            'dataSaved' => true,
            'message' => 'Data berhasil diupdate',
        ]);
    }

    public function destroy($id)
    {
        PresensiStatus::findOrFail($id)->delete();

        return redirect()->route('presensi_status.index')->with([
            'dataSaved' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }

    public function fetch(Request $request)
    {
        return DataTables::of(PresensiStatus::query())
            ->addIndexColumn()
            ->make(true);
    }
}
