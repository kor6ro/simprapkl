<?php

namespace App\Http\Controllers;

use App\Models\PresensiJenis;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PresensiJenisController extends Controller
{
    public function index()
    {
        return view('administrator.presensi_jenis.index');
    }

    public function create()
    {
        return view('administrator.presensi_jenis.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'butuh_bukti' => 'nullable|boolean',
            'otomatis' => 'nullable|boolean',
        ]);

        PresensiJenis::create([
            'nama' => $request->nama,
            'butuh_bukti' => $request->boolean('butuh_bukti'),
            'otomatis' => $request->boolean('otomatis'),
        ]);


        return redirect()->route('presensi_jenis.index')->with([
            'dataSaved' => true,
            'message' => 'Data berhasil disimpan',
        ]);
    }

    public function edit($id)
    {
        $jenis = PresensiJenis::findOrFail($id);
        return view('administrator.presensi_jenis.edit', compact('jenis'));
    }

    public function update(Request $request, $id)
    {
        $jenis = PresensiJenis::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'butuh_bukti' => 'nullable|boolean',
            'otomatis' => 'nullable|boolean',
        ]);

        $jenis->update([
            'nama' => $request->nama,
            'butuh_bukti' => $request->boolean('butuh_bukti'),
            'otomatis' => $request->boolean('otomatis'),
        ]);


        return redirect()->route('presensi_jenis.index')->with([
            'dataSaved' => true,
            'message' => 'Data berhasil diupdate',
        ]);
    }

    public function destroy($id)
    {
        PresensiJenis::findOrFail($id)->delete();

        return redirect()->route('presensi_jenis.index')->with([
            'dataSaved' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }

    public function fetch(Request $request)
    {
        return DataTables::of(PresensiJenis::query())
            ->addIndexColumn()
            ->editColumn('butuh_bukti', fn($row) => $row->butuh_bukti ? 'Ya' : 'Tidak')
            ->editColumn('otomatis', fn($row) => $row->otomatis ? 'Ya' : 'Tidak')
            ->make(true);
    }
}
