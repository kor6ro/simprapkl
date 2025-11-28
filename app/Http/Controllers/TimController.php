<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tim;
use App\Models\User;
use App\Models\Divisi;
use App\Models\PeriodePkl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use App\Models\TaskBreakdown;
use Carbon\Carbon;
use App\Notifications\TugasPerluRevisi;
use App\Notifications\TugasDisetujui;
use App\Notifications\SiswaDitambahkanKeTim;


class TimController extends Controller
{
    public function index()
    {
        $karyawan = User::where('group_id', 5)->orderBy('name')->get();
        $daftar_divisi = Divisi::orderBy('nama_divisi')->get();
        $todaysTask = TaskBreakdown::whereDate('applicable_date', Carbon::today())->first();
        $periodePkls = PeriodePkl::orderBy('awal_periode', 'desc')->get();

        return view('administrator.tim.index', [
            'karyawan' => $karyawan,
            'daftar_divisi' => $daftar_divisi,
            'todaysTask' => $todaysTask,
            'periodePkls' => $periodePkls,
        ]);
    }

  // app/Http/Controllers/TimController.php
// app/Http/Controllers/TimController.php

public function data(Request $request)
{
    // [PERBAIKAN] Inisialisasi cache statis untuk task
    static $taskCache = [];

    // Query awal sudah benar
    $query = Tim::with('ketua', 'anggota', 'divisi')->withCount('laporan')->select('tim.*');

    // BLOK FILTER (Semua filter Anda sudah benar)
    $user = Auth::user();
    if ($user->group_id == 4) {
        $query->whereHas('anggota', fn ($q) => $q->where('user_id', $user->id));
      }  elseif ($user->group_id == 5) {
        $query->whereHas('ketua', fn ($q) => $q->where('user_id', $user->id));
        
    } elseif ($user->group_id == 3) {
        $query->whereHas('anggota', function ($q) use ($user) {
            $q->where('sekolah_id', $user->sekolah_id)
              ->where('program_keahlian_id', $user->program_keahlian_id);
        });
    }
    if (!$request->filled('bulan') && !$request->filled('periode_id')) {
        $query->whereDate('tanggal', today());
    }
    if ($request->filled('bulan')) {
        try {
            $date = Carbon::parse($request->bulan);
            $query->whereYear('tanggal', $date->year)->whereMonth('tanggal', $date->month);
        } catch (\Exception $e) {
            Log::error('Invalid month format: ' . $request->bulan);
        }
    }
    if ($request->filled('ketua_ids') && is_array($request->ketua_ids)) {
        $query->whereHas('ketua', function ($q) use ($request) {
            $q->whereIn('user.id', $request->ketua_ids);
        });
    } elseif ($request->filled('ketua_id')) {
        $query->whereHas('ketua', function ($q) use ($request) {
            $q->where('user.id', $request->ketua_id);
        });
    }
    if ($request->filled('divisi_id')) $query->where('divisi_id', $request->divisi_id);
    if ($request->filled('status')) {
        $query->where('status_approval', $request->status);
    }
    if ($request->filled('periode_id')) {
        $query->whereHas('anggota.periodePkl', function ($q) use ($request) {
            $q->where('periode_pkl.id', $request->periode_id);
        });
    }
    
    // [PERBAIKAN] HAPUS baris .get() dan pengambilan task di sini
    // $filteredTeams = $query->orderBy('created_at', 'desc')->get(); // <-- DIHAPUS
    // $dates = $filteredTeams->pluck('tanggal')->unique(); // <-- DIHAPUS
    // $tasks = TaskBreakdown::whereIn(...); // <-- DIHAPUS

    // [PERBAIKAN] Kembalikan $query, BUKAN $filteredTeams
    return DataTables::of($query) // <-- KEMBALIKAN KE $query
        ->addIndexColumn()
        ->addColumn('ketua_names', fn($tim) => $tim->ketua->pluck('name')->toArray())
        ->addColumn('divisi_name', fn($tim) => $tim->divisi?->nama_divisi ?? 'N/A')
        ->addColumn('anggota_names', fn($tim) => $tim->anggota ? $tim->anggota->pluck('name')->toArray() : [])
        ->editColumn('created_at', fn($tim) => $tim->created_at?->toIso8601String())
        ->addColumn('task_breakdown_data', function ($tim) use (&$taskCache) {
            // [PERBAIKAN] Logika pengambilan task dipindah ke sini
            $taskDate = Carbon::parse($tim->tanggal)->toDateString();
            
            // Cek cache dulu agar tidak N+1 query
            if (isset($taskCache[$taskDate])) {
                $task = $taskCache[$taskDate];
            } else {
                $task = TaskBreakdown::where('applicable_date', $taskDate)->first();
                $taskCache[$taskDate] = $task; // Simpan di cache (meskipun null)
            }

            if (!$task) return null;

            return [
                'tipe' => $task->tipe,
                'content' => $task->tipe == 'file' 
                    ? asset('uploads/daily_tasks/' . $task->task_breakdown) 
                    : $task->deskripsi_tugas,
            ];
        })
        ->addColumn('status_data', function ($tim) {
            return [
                'text' => $tim->status_text,
                'badge_class' => $tim->status_badge_class,
                'raw_status' => $tim->status_approval,
            ];
        })
       ->addColumn('action_data', function ($row) {
            return [
                'id' => $row->id,
                'has_laporan' => $row->laporan_count > 0, // Ini sudah benar
                'ketua_ids' => $row->ketua->pluck('id')->toArray(), 
                'edit_url' => route('admin.tim.edit', $row->id),
                'destroy_url' => route('admin.tim.destroy', $row->id),
                'status_approval' => $row->status_approval,
            ];
        })
        ->make(true);
}
 public function create()
    {
        if (!in_array(auth()->user()->group_id, [1, 2,5])) {
            abort(403, 'Anda tidak memiliki wewenang untuk membuat tim.');
        }
        $data = $this->getAvailableUsersAndDivisiForForm();
        return view('administrator.tim.create', $data);
    }
    
public function store(Request $request)
{
    try {
        if (!in_array(auth()->user()->group_id, [1, 2, 5])) {
            abort(403, 'Anda tidak memiliki wewenang untuk menyimpan tim.');
        }

        $validated = $this->validateTeamRequest($request);
        $team = null; // Inisialisasi variabel team

        DB::transaction(function () use ($validated, &$team) {
            $team = Tim::create([
                'divisi_id' => $validated['divisi_id'],
                'tanggal'   => today(),
            ]);
            $team->ketua()->sync($validated['ketua_ids']);
            $team->anggota()->sync($validated['anggota']);
        });

        // [LOGIKA NOTIFIKASI BARU DIMULAI DI SINI]
        // -------------------------------------------------------------
        if ($team) {
            // Ambil semua anggota dari tim yang baru dibuat
            $anggotaTim = $team->anggota;

            // Kirim notifikasi ke setiap anggota
            foreach ($anggotaTim as $anggota) {
                $anggota->notify(new SiswaDitambahkanKeTim($team));
            }
        }
        // -------------------------------------------------------------
        // [AKHIR DARI LOGIKA NOTIFIKASI]

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
        $tim->load('ketua', 'anggota'); // load relasi ketua
        return view('administrator.tim.edit', array_merge($data, ['team' => $tim]));
    }
// app/Http/Controllers/TimController.php
// app/Http/Controllers/TimController.php

public function update(Request $request, Tim $tim)
{
    try {
        $user = auth()->user();

        // Otorisasi
        if ($user->group_id == 5 && !$tim->ketua->contains('id', $user->id)) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengedit tim ini.');
        }

        $validated = $this->validateTeamRequest($request);

        // 1. Ambil ID anggota LAMA (SEBELUM update)
        $anggotaLamaIds = $tim->anggota()->pluck('user.id')->toArray();

        // 2. Ambil ID anggota BARU dari request
        $anggotaBaruIds = (array) $validated['anggota'];
        
        \DB::transaction(function () use ($tim, $validated, $anggotaBaruIds) {
            $tim->update([
                'divisi_id' => $validated['divisi_id'],
            ]);
            $tim->ketua()->sync($validated['ketua_ids']);
            $tim->anggota()->sync($anggotaBaruIds); // Update tim
        });

        // 3. Cari tahu siapa yang BARU ditambahkan
        $anggotaYangBaruDitambahkan = array_diff($anggotaBaruIds, $anggotaLamaIds);

        // 4. Kirim notifikasi ke mereka
        if (!empty($anggotaYangBaruDitambahkan)) {
            $anggotaUntukNotifikasi = User::whereIn('id', $anggotaYangBaruDitambahkan)->get();
            foreach ($anggotaUntukNotifikasi as $anggota) {
                $anggota->notify(new SiswaDitambahkanKeTim($tim));
            }
        }

        // --- Logika Anda yang sudah ada sebelumnya ---
        $jumlahAnggotaSebelumnya = count($anggotaLamaIds);
        $jumlahAnggotaSekarang = count($anggotaBaruIds);

        if ($jumlahAnggotaSekarang > $jumlahAnggotaSebelumnya && $tim->status_approval === 'tugas_selesai') {
            $tim->update(['status_approval' => 'belum_selesai']);
        }
        // --- Selesai ---

    } catch (ValidationException $e) {
        return redirect()->back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        \Log::error("Update Team Error (ID: {$tim->id}): " . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal memperbarui data. Terjadi kesalahan server.');
    }
    return redirect()->route('admin.tim.index')->with('success', 'Tim berhasil diperbarui!');
}
    public function destroy(Tim $tim)
{
    $user = auth()->user();
    $isKetua = $tim->ketua->contains('id', $user->id);
    if (!in_array($user->group_id, [1, 2]) && !($user->group_id == 5 && $isKetua)) {
        abort(403, 'Anda tidak memiliki wewenang untuk menghapus tim ini.');
    }

    try {
        if ($tim->laporan()->exists()) {
            return redirect()->route('admin.tim.index')->with('error', 'Gagal menghapus tim. Tim ini sudah memiliki laporan terkait.');
        }
        DB::transaction(function () use ($tim) {
            $tim->ketua()->detach();
            $tim->anggota()->detach();
            $tim->delete();
        });

    } catch (\Exception $e) {
        Log::error("Delete Team Error (ID: {$tim->id}): " . $e->getMessage());
        return redirect()->route('admin.tim.index')->with('error', 'Gagal menghapus tim.');
    }

    return redirect()->route('admin.tim.index')->with('success', 'Tim berhasil dihapus!');
}    private function getAvailableUsersAndDivisiForForm()
    {
        $availableAdmins = User::where('group_id', 5)->orderBy('name')->get();
        $availableSiswa = User::where('group_id', 4)->orderBy('name')->get();
        $daftarDivisi = Divisi::orderBy('nama_divisi')->get();
        return compact('availableAdmins', 'availableSiswa', 'daftarDivisi');
    }

    private function validateTeamRequest(Request $request)
    {
        // [UBAH] Validasi untuk multi-ketua
        return $request->validate([
            'ketua_ids'   => 'required|array|min:1',
            'ketua_ids.*' => 'exists:user,id,group_id,5',
            'divisi_id'  => 'required|exists:divisi,id',
            'anggota'    => 'required|array|min:1',
            'anggota.*'  => 'exists:user,id,group_id,4',
        ], [
            'ketua_ids.required'  => 'Ketua tim harus dipilih.',
            'ketua_ids.*.exists'  => 'Semua ketua tim yang dipilih harus seorang karyawan.',
            'divisi_id.exists'    => 'Divisi yang dipilih tidak valid.',
            'anggota.*.exists'    => 'Semua anggota tim yang dipilih harus siswa.',
        ]);
    }
}