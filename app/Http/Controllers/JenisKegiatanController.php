<?php

namespace App\Http\Controllers;

use App\Models\JenisKegiatan;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class JenisKegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Hanya menampilkan halaman view utama
      return view('administrator.jenis_kegiatan.index');
    }

    /**
     * Fetch data for DataTables.
     */
    public function fetch()
    {
        $jenisKegiatan = JenisKegiatan::query();

        return DataTables::of($jenisKegiatan)
            ->addIndexColumn()
            ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Menampilkan form tambah data
        return view('administrator.jenis_kegiatan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'nama_kegiatan' => 'required|string|max:255|unique:jenis_kegiatan',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            // Simpan data baru
            JenisKegiatan::create($validatedData);

            // Set session untuk notifikasi sukses
            session()->flash('dataSaved', true);
            session()->flash('message', 'Jenis Kegiatan berhasil ditambahkan!');
        } catch (\Exception $e) {
            // Set session untuk notifikasi error
            session()->flash('dataSaved', false);
            session()->flash('message', 'Error: ' . $e->getMessage());
        }

        return redirect()->route('admin.jenis_kegiatan.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(JenisKegiatan $jenisKegiatan)
    {
        // Tidak digunakan untuk CRUD standar ini, bisa dikosongkan
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JenisKegiatan $jenisKegiatan)
    {
        // Menampilkan form edit dengan data yang sudah ada
        return view('administrator.jenis_kegiatan.edit', compact('jenisKegiatan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JenisKegiatan $jenisKegiatan)
    {
        // Validasi input, pastikan 'unique' mengabaikan data saat ini
        $validatedData = $request->validate([
            'nama_kegiatan' => 'required|string|max:255|unique:jenis_kegiatan,nama_kegiatan,' . $jenisKegiatan->id,
            'deskripsi' => 'nullable|string',
        ]);

        try {
            // Update data
            $jenisKegiatan->update($validatedData);

            // Set session untuk notifikasi sukses
            session()->flash('dataSaved', true);
            session()->flash('message', 'Jenis Kegiatan berhasil diperbarui!');
        } catch (\Exception $e) {
            // Set session untuk notifikasi error
            session()->flash('dataSaved', false);
            session()->flash('message', 'Error: ' . $e->getMessage());
        }

        return redirect()->route('admin.jenis_kegiatan.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JenisKegiatan $jenisKegiatan)
    {
        try {
            // Hapus data
            $jenisKegiatan->delete();

            // Set session untuk notifikasi sukses
            session()->flash('dataSaved', true);
            session()->flash('message', 'Jenis Kegiatan berhasil dihapus!');
        } catch (\Exception $e) {
            // Set session untuk notifikasi error
            session()->flash('dataSaved', false);
            session()->flash('message', 'Error: ' . $e->getMessage());
        }
        
        return redirect()->route('admin.jenis_kegiatan.index');
    }
}