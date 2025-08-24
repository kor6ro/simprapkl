<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DivisiController extends Controller
{
    /**
     * Menampilkan halaman utama daftar Divisi.
     */
    public function index()
    {
        return view('administrator.divisi.index');
    }

    /**
     * Mengambil data untuk DataTables.
     */
    public function fetch()
    {
        $divisi = Divisi::query();
        return DataTables::of($divisi)
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Menampilkan form untuk membuat Divisi baru.
     */
    public function create()
    {
        return view('administrator.divisi.create');
    }

    /**
     * Menyimpan Divisi baru ke database.
     */
    public function store(Request $request)
    {
        // Validasi input (hanya nama_divisi)
        $validatedData = $request->validate([
            'nama_divisi' => 'required|string|max:255|unique:divisi',
        ]);

        try {
            Divisi::create($validatedData);
            session()->flash('dataSaved', true);
            session()->flash('message', 'Divisi baru berhasil ditambahkan!');
        } catch (\Exception $e) {
            session()->flash('dataSaved', false);
            session()->flash('message', 'Error: ' . $e->getMessage());
        }

        return redirect()->route('admin.divisi.index');
    }

    /**
     * Menampilkan form untuk mengedit Divisi.
     */
    public function edit(Divisi $divisi)
    {
        return view('administrator.divisi.edit', compact('divisi'));
    }

    /**
     * Memperbarui Divisi di database.
     */
    public function update(Request $request, Divisi $divisi)
    {
        // Validasi input (hanya nama_divisi)
        $validatedData = $request->validate([
            'nama_divisi' => 'required|string|max:255|unique:divisi,nama_divisi,' . $divisi->id,
        ]);

        try {
            $divisi->update($validatedData);
            session()->flash('dataSaved', true);
            session()->flash('message', 'Divisi berhasil diperbarui!');
        } catch (\Exception $e) {
            session()->flash('dataSaved', false);
            session()->flash('message', 'Error: ' . $e->getMessage());
        }

        return redirect()->route('admin.divisi.index');
    }

    /**
     * Menghapus Divisi dari database.
     */
    public function destroy(Divisi $divisi)
    {
        try {
            $divisi->delete();
            session()->flash('dataSaved', true);
            session()->flash('message', 'Divisi berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('dataSaved', false);
            session()->flash('message', 'Error: ' . $e->getMessage());
        }

        return redirect()->route('admin.divisi.index');
    }
}