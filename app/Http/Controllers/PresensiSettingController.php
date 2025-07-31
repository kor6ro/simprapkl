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
        $settings = PresensiSetting::all();
        return view("administrator.presensi_setting.index", compact('settings'));
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Hapus semua setting lain sebelum membuat yang baru
            PresensiSetting::truncate();

            PresensiSetting::create([
                'pagi_mulai' => $request->pagi_mulai . ':00',
                'pagi_selesai' => $request->pagi_selesai . ':00',
                'sore_mulai' => $request->sore_mulai . ':00',
                'sore_selesai' => $request->sore_selesai . ':00',
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $presensiSetting->update([
                'pagi_mulai' => $request->pagi_mulai . ':00',
                'pagi_selesai' => $request->pagi_selesai . ':00',
                'sore_mulai' => $request->sore_mulai . ':00',
                'sore_selesai' => $request->sore_selesai . ':00',
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
            ->make(true);
    }

    public function destroy($id)
    {
        $presensiSetting = PresensiSetting::findOrFail($id);

        try {
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
}
