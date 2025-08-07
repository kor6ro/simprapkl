<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class SekolahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('administrator.sekolah.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('administrator.sekolah.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_sekolah' => 'required|string|max:255|unique:sekolah,nama',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            DB::beginTransaction();

            $logo = null;
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileName = $file->hashName();
                $file->move(public_path('uploads/sekolah_logo'), $fileName);
                $logo = $fileName;
            }

            Sekolah::create([
                'nama' => trim($request->nama_sekolah),
                'logo' => $logo,
            ]);

            DB::commit();

            return redirect()->route('admin.sekolah.index')
                ->with('dataSaved', true)
                ->with('message', 'Data sekolah berhasil ditambahkan.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('dataSaved', false)
                ->with('message', 'Gagal menyimpan data sekolah. ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sekolah $sekolah)
    {
        return view('administrator.sekolah.edit', compact('sekolah'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sekolah $sekolah)
    {
        try {
            $request->validate([
                'nama_sekolah' => 'required|string|max:255|unique:sekolah,nama,' . $sekolah->id,
                'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            DB::beginTransaction();

            $logo = $sekolah->logo;
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileName = $file->hashName();
                $file->move(public_path('uploads/sekolah_logo'), $fileName);
                $logo = $fileName;
            }

            $sekolah->update([
                'nama' => trim($request->nama_sekolah),
                'logo' => $logo,
            ]);

            DB::commit();

            return redirect()->route('admin.sekolah.index')
                ->with('dataSaved', true)
                ->with('message', 'Data sekolah berhasil diupdate.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('dataSaved', false)
                ->with('message', 'Gagal mengupdate data sekolah. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sekolah $sekolah)
    {
        try {
            DB::beginTransaction();

            $namaSekolah = $sekolah->nama;
            $sekolah->delete();

            DB::commit();

            return redirect()->route('admin.sekolah.index')
                ->with('dataSaved', true)
                ->with('message', "Data sekolah '{$namaSekolah}' berhasil dihapus.");
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('dataSaved', false)
                ->with('message', 'Gagal menghapus data sekolah. ' . $e->getMessage());
        }
    }

    /**
     * Fetch data for DataTables via AJAX.
     */
    public function fetch(Request $request)
    {
        try {
            $sekolah = Sekolah::select(['id', 'nama', 'logo']);

            return DataTables::of($sekolah)
                ->addIndexColumn()
                ->make(true);
        } catch (Exception $e) {
            \Log::error('SekolahController fetch error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal memuat data sekolah: ' . $e->getMessage()
            ], 500);
        }
    }
}
