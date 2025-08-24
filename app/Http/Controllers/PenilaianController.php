<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DetailNilai;
use App\Models\Penilaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables as FacadesDataTables;

class PenilaianController extends Controller
{
    /**
     * Menampilkan halaman index dengan DataTables.
     */
    public function index()
    {
        return view('administrator.penilaian.index');
    }

    /**
     * Mengambil data untuk server-side DataTables.
     */
    public function fetch()
    {
        // Asumsi ada relasi 'siswa' dan 'penilai' di Model Penilaian
        $query = Penilaian::with(['siswa', 'penilai']);

        return FacadesDataTables::of($query)
            ->addIndexColumn()
            ->addColumn('siswa_nama', function ($row) {
                return $row->siswa->nama ?? 'N/A'; // Ganti 'nama' sesuai kolom di tabel siswa
            })
            ->addColumn('penilai_nama', function ($row) {
                return $row->penilai->name ?? 'N/A'; // Ganti 'name' sesuai kolom di tabel users (penilai)
            })
            ->editColumn('tanggal_penilaian', function ($row) {
                return date('d F Y', strtotime($row->tanggal_penilaian));
            })
            ->make(true);
    }

    /**
     * Menampilkan form untuk membuat data baru.
     */
    public function create()
    {
        // Ambil semua user yang memiliki group_id = 4
        // Sesuaikan 'group_id' dengan nama kolom di tabel users Anda
        $siswas = User::where('group_id', 4)->orderBy('name')->get();

        return view('administrator.penilaian.create', compact('siswas'));
    }

    /**
     * Menyimpan data baru ke database.
     */
    public function store(Request $request)
    {
        // Hapus 'penilai_id' dari validasi karena akan diambil otomatis
        $validated = $request->validate([
            'siswa_id' => 'required|integer',
            // 'penilai_id' => 'required|integer', // <-- HAPUS ATAU KOMENTARI BARIS INI
            'tanggal_penilaian' => 'required|date',
            'komentar' => 'nullable|string',
            'nilai' => 'required|array',
            'nilai.*' => 'required|integer|min:0|max:100',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $penilaian = Penilaian::create([
                    'siswa_id' => $validated['siswa_id'],
                    // Ambil ID penilai dari user yang sedang login
                    'penilai_id' => Auth::id(), // <-- UBAH BARIS INI
                    'tanggal_penilaian' => $validated['tanggal_penilaian'],
                    'komentar' => $validated['komentar'],
                ]);

                // ... (sisa fungsi store tetap sama) ...
                foreach ($validated['nilai'] as $variabel => $skor) {
                    $penilaian->detailNilai()->create([
                        'variabel' => $variabel,
                        'nilai' => $skor,
                    ]);
                }
            });
        } catch (\Exception $e) {
            // ... (error handling tetap sama) ...
        }

        return redirect()->route('admin.penilaian.index')->with([
            'dataSaved' => true,
            'message' => 'Penilaian berhasil disimpan!'
        ]);
    }
    /**
     * Menampilkan form untuk mengedit data.
     */
    public function edit(Penilaian $penilaian)
    {
        $penilaian->load('detailNilai');
        $detailNilai = $penilaian->detailNilai->pluck('nilai', 'variabel');

        // Ambil semua user yang memiliki group_id = 4
        $siswas = User::where('group_id', 4)->orderBy('name')->get();

        return view('administrator.penilaian.edit', compact('penilaian', 'detailNilai', 'siswas'));
    }

    /**
     * Memperbarui data di database.
     */
    public function update(Request $request, Penilaian $penilaian)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|integer',
            'penilai_id' => 'required|integer',
            'tanggal_penilaian' => 'required|date',
            'komentar' => 'nullable|string',
            'nilai' => 'required|array',
            'nilai.*' => 'required|integer|min:0|max:100',
        ]);

        try {
            DB::transaction(function () use ($validated, $penilaian) {
                // 1. Update data utama
                $penilaian->update([
                    'siswa_id' => $validated['siswa_id'],
                    'penilai_id' => $validated['penilai_id'],
                    'tanggal_penilaian' => $validated['tanggal_penilaian'],
                    'komentar' => $validated['komentar'],
                ]);

                // 2. Update atau buat rincian nilai
                foreach ($validated['nilai'] as $variabel => $skor) {
                    $penilaian->detailNilai()->updateOrCreate(
                        ['variabel' => $variabel], // Kondisi pencarian
                        ['nilai' => $skor]          // Nilai yang diupdate atau dibuat
                    );
                }
            });
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui penilaian: ' . $e->getMessage());
            return back()->withInput()->with([
                'dataSaved' => false,
                'message' => 'Gagal memperbarui data, silakan coba lagi.'
            ]);
        }

        return redirect()->route('admin.penilaian.index')->with([
            'dataSaved' => true,
            'message' => 'Penilaian berhasil diperbarui!'
        ]);
    }

    /**
     * Menghapus data dari database.
     */
    public function destroy(Penilaian $penilaian)
    {
        $penilaian->delete(); // Karena onDelete('cascade'), detail_penilaian akan ikut terhapus

        return redirect()->route('admin.penilaian.index')->with([
            'dataSaved' => true,
            'message' => 'Penilaian berhasil dihapus!'
        ]);
    }
}
