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
                'nama' => 'required|string|max:255|unique:sekolah,nama', // PERBAIKAN: Ubah dari sekolahs ke sekolah
            ], [
                'nama.required' => 'Nama sekolah wajib diisi.',
                'nama.string' => 'Nama sekolah harus berupa teks.',
                'nama.max' => 'Nama sekolah maksimal 255 karakter.',
                'nama.unique' => 'Nama sekolah sudah ada, silakan gunakan nama lain.',
            ]);

            DB::beginTransaction();

            Sekolah::create([
                'nama' => trim($request->nama),
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
                'nama' => 'required|string|max:255|unique:sekolah,nama,' . $sekolah->id, // PERBAIKAN: Ubah dari sekolahs ke sekolah
            ], [
                'nama.required' => 'Nama sekolah wajib diisi.',
                'nama.string' => 'Nama sekolah harus berupa teks.',
                'nama.max' => 'Nama sekolah maksimal 255 karakter.',
                'nama.unique' => 'Nama sekolah sudah ada, silakan gunakan nama lain.',
            ]);

            DB::beginTransaction();

            $sekolah->update([
                'nama' => trim($request->nama),
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
            // Get the sekolah data with simple select
            $sekolah = Sekolah::select(['id', 'nama', 'created_at']);

            return DataTables::of($sekolah)
                ->addIndexColumn()
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('d/m/Y H:i') : '-';
                })
                ->make(true);
        } catch (Exception $e) {
            \Log::error('SekolahController fetch error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal memuat data sekolah: ' . $e->getMessage()
            ], 500);
        }
    }
}
