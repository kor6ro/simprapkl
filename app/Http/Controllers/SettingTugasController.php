<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SettingTugas;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon; 
class SettingTugasController extends Controller
{
    /**
     * Display a listing of teams.
     * PERUBAHAN: Mengirim daftar karyawan untuk filter.
     */
    public function index()
    {
        // Mengambil semua user karyawan untuk dropdown filter
        $karyawan = User::where('group_id', 5)->orderBy('name')->get();
        return view('administrator.setting_tugas.index', compact('karyawan'));
    }

    /**
     * DataTables server-side processing.
     * PERUBAHAN: Menambahkan logika untuk memfilter data.
     */
   // app/Http/Controllers/SettingTugasController.php

// Ganti method data() Anda dengan yang ini
public function data(Request $request)
{
    // Query dasar tetap sama
    $query = SettingTugas::with('ketua', 'anggota')
        ->select('setting_tugas.*');

    // Filter berdasarkan bulan/tahun
    if ($request->filled('bulan')) {
        try {
            $date = Carbon::parse($request->bulan);
            $query->whereYear('tanggal', $date->year)
                  ->whereMonth('tanggal', $date->month);
        } catch (\Exception $e) {
            // Abaikan jika format salah
            Log::error('Invalid month format: ' . $request->bulan);
        }
    } else {
        // Jika tidak ada filter bulan, default ke hari ini
        $query->whereDate('tanggal', today());
    }

    // Terapkan filter lain jika ada
    if ($request->filled('ketua_id')) {
        $query->where('ketua_id', $request->ketua_id);
    }
    if ($request->filled('divisi')) {
        $query->where('divisi', $request->divisi);
    }

    return DataTables::of($query)
        ->addColumn('ketua_name', fn($tim) => $tim->ketua->name ?? 'N/A')
        ->addColumn('anggota_names', fn($tim) => $tim->anggota->pluck('name')->toArray())
        ->editColumn('created_at', fn($tim) => $tim->created_at->toIso8601String()) // Kirim format standar ke JS
        ->make(true);
}

    // ... (Sisa fungsi lainnya seperti create, store, edit, update, destroy tetap sama) ...

    public function create() {
        $data = $this->getAvailableUsersForForm();
        return view('administrator.setting_tugas.create', $data);
    }
    public function edit($id) {
        $team = SettingTugas::with('ketua', 'anggota')->findOrFail($id);
        $data = $this->getAvailableUsersForForm();
        return view('administrator.setting_tugas.edit', array_merge($data, ['team' => $team]));
    }
    public function store(Request $request) {
        try {
            $validated = $this->validateTeamRequest($request);
            DB::transaction(function () use ($validated, $request) {
                $team = SettingTugas::create(['ketua_id'  => $validated['ketua_id'],'divisi'    => $validated['divisi'],'tanggal'   => today(),'deskripsi' => $request->deskripsi ?? null, ]);
                $team->anggota()->sync($validated['anggota']);
            });
            return response()->json(['success' => true, 'message' => 'Tim berhasil dibuat!']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            Log::error('Store Team Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id) {
        try {
            $team = SettingTugas::findOrFail($id);
            $validated = $this->validateTeamRequest($request);
            DB::transaction(function () use ($team, $validated, $request) {
                $team->update(['ketua_id'  => $validated['ketua_id'],'divisi'    => $validated['divisi'],'deskripsi' => $request->deskripsi ?? $team->deskripsi,]);
                $team->anggota()->sync($validated['anggota']);
            });
            return response()->json(['success' => true, 'message' => 'Tim berhasil diperbarui!']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            Log::error("Update Team Error (ID: $id): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function destroy($id) {
        try {
            DB::transaction(function () use ($id) {
                $team = SettingTugas::findOrFail($id);
                $team->anggota()->detach();
                $team->delete();
            });
            return response()->json(['success' => true, 'message' => 'Tim berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error("Delete Team Error (ID: $id): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus tim.'], 500);
        }
    }
    private function getAvailableUsersForForm() {
        $availableAdmins = User::where('group_id', 5)->orderBy('name')->get();
        $availableSiswa = User::where('group_id', 4)->orderBy('name')->get();
        return compact('availableAdmins', 'availableSiswa');
    }
    private function validateTeamRequest(Request $request) {
        return $request->validate([
            'ketua_id'  => 'required|exists:user,id,group_id,5',
            'divisi'    => 'required|in:sales,teknisi',
            'anggota'   => 'required|array|min:1',
            'anggota.*' => 'exists:user,id,group_id,4',
            'deskripsi' => 'nullable|string|max:255',
        ], [
            'ketua_id.exists'   => 'Ketua tim yang dipilih harus seorang karyawan (group_id 5).',
            'anggota.*.exists'  => 'Semua anggota tim yang dipilih harus siswa (group_id 4).',
        ]);
    }
}
