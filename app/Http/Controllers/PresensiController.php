<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\PresensiJenis;
use App\Models\PresensiGambar;
use Illuminate\Database\UniqueConstraintViolationException; // Import class exception
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PresensiController extends Controller
{

    public function index()
    {
        return view("administrator.presensi.index");
    }

    public function create()
    {
        return view("administrator.presensi.create", [
            "user" => \App\Models\User::all(),
            "jenisPresensi" => \App\Models\PresensiJenis::all()
        ]);
    }


    public function edit($id)
    {
        $presensi = Presensi::with('gambar')->findOrFail($id);

        return view("administrator.presensi.edit", [
            "presensi" => $presensi,
            "user" => \App\Models\User::all(),
            "jenisPresensi" => \App\Models\PresensiJenis::all(),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:user,id', // Pastikan tabelnya 'user' bukan 'user'
            'presensi_jenis_id' => 'required|exists:presensi_jenis,id',
            'sesi' => 'required|string|in:pagi,sore', // Lebih baik definisikan valuenya
            'bukti' => 'nullable|image|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        // --- PERBAIKAN DIMULAI DI SINI ---

        $tanggalSekarang = now()->toDateString();

        // 1. Cek apakah data presensi untuk user, tanggal, dan sesi ini sudah ada
        $existingPresensi = Presensi::where('user_id', $request->user_id)
            ->where('tanggal_presensi', $tanggalSekarang)
            ->where('sesi', $request->sesi)
            ->first();

        // 2. Jika sudah ada, kembalikan dengan pesan error
        if ($existingPresensi) {
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false, // Gunakan 'dataSaved' => false atau key lain yg sesuai
                'message' => 'Gagal! Pengguna sudah melakukan presensi untuk sesi ini pada hari ini.',
            ]);
        }

        // 3. Jika belum ada, lanjutkan proses penyimpanan (gunakan try-catch untuk keamanan ekstra)
        try {
            // Simpan presensi dulu
            $presensi = Presensi::create([
                'user_id' => $request->user_id,
                'presensi_jenis_id' => $request->presensi_jenis_id,
                'sesi' => $request->sesi,
                'jam_presensi' => now()->format('H:i:s'),
                'tanggal_presensi' => $tanggalSekarang, // Gunakan variabel tanggal yg sudah didefinisikan
                'keterangan' => $request->keterangan,
            ]);

            // Jika ada gambar, simpan ke presensi_gambar
            if ($request->hasFile('bukti') && $request->file('bukti')->isValid()) {
                $path = $request->file('bukti')->store('bukti_presensi', 'public');

                PresensiGambar::create([
                    'presensi_id' => $presensi->id,
                    'bukti' => $path,
                ]);
            }

            return redirect()->route('presensi.index')->with([
                'dataSaved' => true,
                'message' => 'Presensi berhasil ditambahkan',
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // Ini sebagai jaring pengaman jika ada race condition (2 request bersamaan)
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false,
                'message' => 'Gagal! Data presensi ini sudah ada.',
            ]);
        } catch (\Exception $e) {
            // Menangani error umum lainnya
            // Sebaiknya log error ini untuk debugging
            // \Log::error($e->getMessage());
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi.',
            ]);
        }
        // --- PERBAIKAN SELESAI ---
    }


    public function fetch(Request $request)
    {
        // Perbaikan kecil: Gunakan with() untuk Eager Loading agar lebih efisien
        $presensi = Presensi::with(['user', 'jenisPresensi'])->select('presensi.*');

        return DataTables::of($presensi)
            ->addIndexColumn()
            ->addColumn('username', function ($row) {
                return $row->user ? $row->user->name : 'N/A';
            })
            ->addColumn('jenis_presensi_nama', function ($row) {
                return $row->jenisPresensi ? $row->jenisPresensi->nama : 'N/A';
            })
            ->make(true);
    }

    public function update(Request $request, $id)
    {
        $presensi = Presensi::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:user,id', // Pastikan tabelnya 'user'
            'presensi_jenis_id' => 'required|exists:presensi_jenis,id',
            'tanggal_presensi' => 'required|date',
            'sesi' => 'required|in:pagi,sore',
            'jam_presensi' => 'required|date_format:H:i:s', // Sesuaikan format jika perlu
            'status_verifikasi' => 'nullable|string',
            'catatan_verifikasi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect(route("presensi.edit", $id))
                ->withErrors($validator)
                ->withInput();
        }

        // Cek duplikasi data saat update, kecuali untuk data itu sendiri
        $isDuplicate = Presensi::where('user_id', $request->user_id)
            ->where('tanggal_presensi', $request->tanggal_presensi)
            ->where('sesi', $request->sesi)
            ->where('id', '!=', $id) // Pengecualian untuk data yang sedang diedit
            ->exists();

        if ($isDuplicate) {
            return redirect(route("presensi.edit", $id))
                ->withErrors(['user_id' => 'Kombinasi pengguna, tanggal, dan sesi ini sudah ada.'])
                ->withInput();
        }

        try {
            $presensi->update([
                'user_id' => $request->user_id,
                'presensi_jenis_id' => $request->presensi_jenis_id,
                'tanggal_presensi' => $request->tanggal_presensi,
                'sesi' => $request->sesi,
                'jam_presensi' => $request->jam_presensi,
                'status_verifikasi' => $request->status_verifikasi,
                'catatan_verifikasi' => $request->catatan_verifikasi,
            ]);

            return redirect()->route('presensi.index')->with([
                'dataSaved' => true,
                'message' => 'Presensi berhasil diperbarui',
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('presensi.index')->with([
                'dataSaved' => false,
                'message' => 'Gagal memperbarui presensi',
            ]);
        }
    }

    public function destroy($id)
    {
        $presensi = Presensi::where("id", $id)->first();
        if (!$presensi) {
            return abort(404);
        }

        try {
            // Hapus juga gambar terkait jika ada
            if ($presensi->gambar) {
                // Hapus file dari storage
                File::delete(storage_path('app/public/' . $presensi->gambar->bukti));
                // Hapus record dari database
                $presensi->gambar->delete();
            }

            $presensi->delete();

            return redirect(route("presensi.index"))->with([
                "dataSaved" => true,
                "message" => "Data berhasil dihapus",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("presensi.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat menghapus data",
            ]);
        }
    }
}
