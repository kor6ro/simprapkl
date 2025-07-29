<?php

namespace App\Http\Controllers;

use App\Models\PresensiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class PresensiSettingController extends Controller
{
    public function index()
    {
        return view("administrator.presensi_setting.index");
    }

    public function create()
    {
        return view("administrator.presensi_setting.create");
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pagi_mulai' => 'required|date_format:H:i',
            'pagi_selesai' => 'required|date_format:H:i|after:pagi_mulai',
            'sore_mulai' => 'required|date_format:H:i|after:pagi_selesai',
            'sore_selesai' => 'required|date_format:H:i|after:sore_mulai',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Jika setting baru diaktifkan, nonaktifkan yang lain
            if ($request->is_active) {
                PresensiSetting::where('is_active', true)->update(['is_active' => false]);
            }

            PresensiSetting::create([
                'pagi_mulai' => $request->pagi_mulai . ':00',
                'pagi_selesai' => $request->pagi_selesai . ':00',
                'sore_mulai' => $request->sore_mulai . ':00',
                'sore_selesai' => $request->sore_selesai . ':00',
                'is_active' => $request->is_active ?? false,
            ]);

            return redirect()->route('presensi_setting.index')->with([
                'dataSaved' => true,
                'message' => 'Setting presensi berhasil ditambahkan',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('presensi_setting.index')->with([
                'dataSaved' => false,
                'message' => 'Terjadi kesalahan saat menyimpan setting',
            ]);
        }
    }

    public function edit($id)
    {
        $presensiSetting = PresensiSetting::findOrFail($id);

        return view("administrator.presensi_setting.edit", [
            "presensiSetting" => $presensiSetting
        ]);
    }

    public function update(Request $request, $id)
    {
        $presensiSetting = PresensiSetting::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'pagi_mulai' => 'required|date_format:H:i',
            'pagi_selesai' => 'required|date_format:H:i|after:pagi_mulai',
            'sore_mulai' => 'required|date_format:H:i|after:pagi_selesai',
            'sore_selesai' => 'required|date_format:H:i|after:sore_mulai',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Jika setting ini diaktifkan, nonaktifkan yang lain
            if ($request->is_active) {
                PresensiSetting::where('is_active', true)
                    ->where('id', '!=', $id)
                    ->update(['is_active' => false]);
            }

            $presensiSetting->update([
                'pagi_mulai' => $request->pagi_mulai . ':00',
                'pagi_selesai' => $request->pagi_selesai . ':00',
                'sore_mulai' => $request->sore_mulai . ':00',
                'sore_selesai' => $request->sore_selesai . ':00',
                'is_active' => $request->is_active ?? false,
            ]);

            return redirect()->route('presensi_setting.index')->with([
                'dataSaved' => true,
                'message' => 'Setting presensi berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('presensi_setting.index')->with([
                'dataSaved' => false,
                'message' => 'Terjadi kesalahan saat memperbarui setting',
            ]);
        }
    }

    public function fetch(Request $request)
    {
        $presensiSetting = PresensiSetting::select('presensi_setting.*');

        return DataTables::of($presensiSetting)
            ->addIndexColumn()
            ->addColumn('pagi_mulai', function ($row) {
                return date('H:i', strtotime($row->pagi_mulai));
            })
            ->addColumn('pagi_selesai', function ($row) {
                return date('H:i', strtotime($row->pagi_selesai));
            })
            ->addColumn('sore_mulai', function ($row) {
                return date('H:i', strtotime($row->sore_mulai));
            })
            ->addColumn('sore_selesai', function ($row) {
                return date('H:i', strtotime($row->sore_selesai));
            })
            ->addColumn('status_active', function ($row) {
                if ($row->is_active) {
                    return '<span class="badge badge-success">Aktif</span>';
                } else {
                    return '<span class="badge badge-secondary">Tidak Aktif</span>';
                }
            })
            ->addColumn('durasi_pagi', function ($row) {
                $pagiMulai = Carbon::createFromFormat('H:i:s', $row->pagi_mulai);
                $pagiSelesai = Carbon::createFromFormat('H:i:s', $row->pagi_selesai);
                $durasi = $pagiSelesai->diff($pagiMulai);

                return $durasi->format('%H jam %I menit');
            })
            ->addColumn('durasi_sore', function ($row) {
                $soreMulai = Carbon::createFromFormat('H:i:s', $row->sore_mulai);
                $soreSelesai = Carbon::createFromFormat('H:i:s', $row->sore_selesai);
                $durasi = $soreSelesai->diff($soreMulai);

                return $durasi->format('%H jam %I menit');
            })
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->get('search')['value'])) {
                    $searchValue = $request->get('search')['value'];

                    $query->where(function ($q) use ($searchValue) {
                        $q->where('pagi_mulai', 'like', "%{$searchValue}%")
                            ->orWhere('pagi_selesai', 'like', "%{$searchValue}%")
                            ->orWhere('sore_mulai', 'like', "%{$searchValue}%")
                            ->orWhere('sore_selesai', 'like', "%{$searchValue}%");
                    });
                }
            })
            ->rawColumns(['status_active'])
            ->make(true);
    }

    public function destroy($id)
    {
        $presensiSetting = PresensiSetting::findOrFail($id);

        try {
            // Cek apakah ini setting yang aktif
            if ($presensiSetting->is_active) {
                return redirect()->route('presensi_setting.index')->with([
                    'dataSaved' => false,
                    'message' => 'Tidak dapat menghapus setting yang sedang aktif',
                ]);
            }

            $presensiSetting->delete();

            return redirect()->route('presensi_setting.index')->with([
                'dataSaved' => true,
                'message' => 'Setting presensi berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('presensi_setting.index')->with([
                'dataSaved' => false,
                'message' => 'Terjadi kesalahan saat menghapus setting',
            ]);
        }
    }

    public function activate($id)
    {
        try {
            // Nonaktifkan semua setting
            PresensiSetting::where('is_active', true)->update(['is_active' => false]);

            // Aktifkan setting yang dipilih
            $presensiSetting = PresensiSetting::findOrFail($id);
            $presensiSetting->update(['is_active' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Setting berhasil diaktifkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengaktifkan setting'
            ], 500);
        }
    }

    public function getActiveSetting()
    {
        $activeSetting = PresensiSetting::where('is_active', true)->first();

        if (!$activeSetting) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada setting aktif'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $activeSetting
        ]);
    }
}
