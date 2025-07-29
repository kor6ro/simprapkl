<?php

namespace App\Http\Controllers;

use App\Models\PresensiGambar;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PresensiGambarController extends Controller
{
    public function index()
    {
        return view("administrator.presensi_gambar.index");
    }

    public function create()
    {
        return view("administrator.presensi_gambar.create", [
            "presensi" => Presensi::with(['user', 'jenisPresensi'])->get()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'presensi_id' => 'required|exists:presensi,id',
            'bukti' => 'required|image|max:2048|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Cek apakah presensi sudah punya gambar
            $existingGambar = PresensiGambar::where('presensi_id', $request->presensi_id)->first();

            if ($existingGambar) {
                return redirect()->route('presensi_gambar.index')->with([
                    'dataSaved' => false,
                    'message' => 'Presensi ini sudah memiliki bukti gambar',
                ]);
            }

            // Upload gambar
            $path = $request->file('bukti')->store('bukti_presensi', 'public');

            PresensiGambar::create([
                'presensi_id' => $request->presensi_id,
                'bukti' => $path,
            ]);

            return redirect()->route('presensi_gambar.index')->with([
                'dataSaved' => true,
                'message' => 'Bukti presensi berhasil ditambahkan',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('presensi_gambar.index')->with([
                'dataSaved' => false,
                'message' => 'Terjadi kesalahan saat menyimpan bukti',
            ]);
        }
    }

    public function edit($id)
    {
        $presensiGambar = PresensiGambar::with('presensi.user', 'presensi.jenisPresensi')->findOrFail($id);

        return view("administrator.presensi_gambar.edit", [
            "presensiGambar" => $presensiGambar,
            "presensi" => Presensi::with(['user', 'jenisPresensi'])->get()
        ]);
    }

    public function update(Request $request, $id)
    {
        $presensiGambar = PresensiGambar::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'presensi_id' => 'required|exists:presensi,id',
            'bukti' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = ['presensi_id' => $request->presensi_id];

            // Jika ada gambar baru
            if ($request->hasFile('bukti') && $request->file('bukti')->isValid()) {
                // Hapus gambar lama
                if ($presensiGambar->bukti) {
                    File::delete(storage_path('app/public/' . $presensiGambar->bukti));
                }

                // Upload gambar baru
                $data['bukti'] = $request->file('bukti')->store('bukti_presensi', 'public');
            }

            $presensiGambar->update($data);

            return redirect()->route('presensi_gambar.index')->with([
                'dataSaved' => true,
                'message' => 'Bukti presensi berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('presensi_gambar.index')->with([
                'dataSaved' => false,
                'message' => 'Terjadi kesalahan saat memperbarui bukti',
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $presensiGambar = PresensiGambar::query()
            ->leftJoin('presensi', 'presensi_gambar.presensi_id', '=', 'presensi.id')
            ->leftJoin('user', 'presensi.user_id', '=', 'user.id')
            ->leftJoin('presensi_jenis', 'presensi.presensi_jenis_id', '=', 'presensi_jenis.id')
            ->select([
                'presensi_gambar.*',
                'user.name as nama_user',
                'presensi_jenis.nama as jenis_presensi',
                'presensi.tanggal_presensi',
                'presensi.sesi',
                'presensi.jam_presensi'
            ]);

        return DataTables::of($presensiGambar)
            ->addIndexColumn()
            ->addColumn('nama_user', function ($row) {
                return $row->nama_user ?? 'N/A';
            })
            ->addColumn('jenis_presensi', function ($row) {
                return $row->jenis_presensi ?? 'N/A';
            })
            ->addColumn('tanggal_presensi', function ($row) {
                return $row->tanggal_presensi ? date('d/m/Y', strtotime($row->tanggal_presensi)) : 'N/A';
            })
            ->addColumn('sesi', function ($row) {
                return ucfirst($row->sesi ?? 'N/A');
            })
            ->addColumn('bukti_preview', function ($row) {
                if ($row->bukti) {
                    return '<img src="' . asset('storage/' . $row->bukti) . '" alt="Bukti" style="width: 50px; height: 50px; object-fit: cover;" class="rounded">';
                }
                return '<span class="text-muted">Tidak ada</span>';
            })
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->get('search')['value'])) {
                    $searchValue = $request->get('search')['value'];

                    $query->where(function ($q) use ($searchValue) {
                        $q->where('user.name', 'like', "%{$searchValue}%")
                            ->orWhere('presensi_jenis.nama', 'like', "%{$searchValue}%")
                            ->orWhere('presensi.sesi', 'like', "%{$searchValue}%")
                            ->orWhere('presensi.tanggal_presensi', 'like', "%{$searchValue}%");
                    });
                }
            })
            ->rawColumns(['bukti_preview'])
            ->make(true);
    }

    public function destroy($id)
    {
        $presensiGambar = PresensiGambar::findOrFail($id);

        try {
            // Hapus file gambar
            if ($presensiGambar->bukti) {
                File::delete(storage_path('app/public/' . $presensiGambar->bukti));
            }

            $presensiGambar->delete();

            return redirect()->route('presensi_gambar.index')->with([
                'dataSaved' => true,
                'message' => 'Bukti presensi berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('presensi_gambar.index')->with([
                'dataSaved' => false,
                'message' => 'Terjadi kesalahan saat menghapus bukti',
            ]);
        }
    }

    public function show($id)
    {
        $presensiGambar = PresensiGambar::with('presensi.user', 'presensi.jenisPresensi')->findOrFail($id);

        return view("administrator.presensi_gambar.index", [
            "presensiGambar" => $presensiGambar
        ]);
    }
}
