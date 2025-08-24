<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tim;
use App\Models\User;
use App\Models\Divisi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;


class TimController extends Controller
{
    public function index()
    {
        $karyawan = User::where('group_id', 5)->orderBy('name')->get();
        $daftar_divisi = Divisi::orderBy('nama_divisi')->get();
        return view('administrator.tim.index', compact('karyawan', 'daftar_divisi'));
    }

    public function data(Request $request)
    {
        $query = Tim::with('ketua', 'anggota', 'divisi')->select('tim.*');

        if ($request->filled('bulan')) {
            try {
                $date = Carbon::parse($request->bulan);
                $query->whereYear('tanggal', $date->year)
                      ->whereMonth('tanggal', $date->month);
            } catch (\Exception $e) {
                Log::error('Invalid month format: ' . $request->bulan);
            }
        } else {
            $query->whereDate('tanggal', today());
        }

        if ($request->filled('ketua_id')) {
            $query->where('ketua_id', $request->ketua_id);
        }
        if ($request->filled('divisi_id')) {
            $query->where('divisi_id', $request->divisi_id);
        }

        return DataTables::of($query)
            ->addColumn('ketua_name', fn($tim) => $tim->ketua->name ?? 'N/A')
            ->addColumn('divisi_name', fn($tim) => $tim->divisi->nama_divisi ?? 'N/A')
            ->addColumn('anggota_names', fn($tim) => $tim->anggota->pluck('name')->implode(', '))
            ->editColumn('created_at', fn($tim) => Carbon::parse($tim->created_at)->toIso8601String())
            ->make(true);
    }

    public function create()
    {
        $data = $this->getAvailableUsersAndDivisiForForm();
        return view('administrator.tim.create', $data);
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validateTeamRequest($request);
            DB::transaction(function () use ($validated) {
                $team = Tim::create([
                    'ketua_id'  => $validated['ketua_id'],
                    'divisi_id' => $validated['divisi_id'],
                    'tanggal'   => today(),
                ]);
                $team->anggota()->sync($validated['anggota']);
            });
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Store Team Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan data. Terjadi kesalahan server.');
        }

        return redirect()->route('admin.tim.index')->with('success', 'Tim baru berhasil dibuat!');
    }

    public function edit(Tim $tim)
    {
        $data = $this->getAvailableUsersAndDivisiForForm();
        $tim->load('ketua', 'anggota');
        return view('administrator.tim.edit', array_merge($data, ['team' => $tim]));
    }

    public function update(Request $request, Tim $tim)
    {
        try {
            $validated = $this->validateTeamRequest($request);
            DB::transaction(function () use ($tim, $validated) {
                $tim->update([
                    'ketua_id'  => $validated['ketua_id'],
                    'divisi_id' => $validated['divisi_id'],
                ]);
                $tim->anggota()->sync($validated['anggota']);
            });
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error("Update Team Error (ID: {$tim->id}): " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data. Terjadi kesalahan server.');
        }

        return redirect()->route('admin.tim.index')->with('success', 'Tim berhasil diperbarui!');
    }

    public function destroy(Tim $tim)
    {
        try {
            DB::transaction(function () use ($tim) {
                $tim->anggota()->detach();
                $tim->delete();
            });
        } catch (\Exception $e) {
            Log::error("Delete Team Error (ID: {$tim->id}): " . $e->getMessage());
            return redirect()->route('admin.tim.index')->with('error', 'Gagal menghapus tim.');
        }

        return redirect()->route('admin.tim.index')->with('success', 'Tim berhasil dihapus!');
    }

    private function getAvailableUsersAndDivisiForForm()
    {
        $availableAdmins = User::where('group_id', 5)->orderBy('name')->get();
        $availableSiswa = User::where('group_id', 4)->orderBy('name')->get();
        $daftarDivisi = Divisi::orderBy('nama_divisi')->get();
        return compact('availableAdmins', 'availableSiswa', 'daftarDivisi');
    }

    private function validateTeamRequest(Request $request)
    {
        return $request->validate([
            'ketua_id'  => 'required|exists:user,id,group_id,5',
            'divisi_id' => 'required|exists:divisi,id',
            'anggota'   => 'required|array|min:1',
            'anggota.*' => 'exists:user,id,group_id,4',
        ], [
            'ketua_id.exists'   => 'Ketua tim yang dipilih harus seorang karyawan.',
            'divisi_id.exists'  => 'Divisi yang dipilih tidak valid.',
            'anggota.*.exists'  => 'Semua anggota tim yang dipilih harus siswa.',
        ]);
    }
}