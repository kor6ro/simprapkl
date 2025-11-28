<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PeriodePkl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\ValidationException;

class PeriodePklController extends Controller
{
    /**
     * Menampilkan halaman utama (indeks) dengan tabel periode.
     */
    public function index()
    {
        return view('administrator.periode_pkl.index');
    }

    /**
     * Mengambil data untuk diisi ke DataTables secara server-side.
     */
    public function fetch()
    {
        $data = PeriodePkl::withCount('users');

        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('awal_periode', fn($row) => \Carbon\Carbon::parse($row->awal_periode)->format('d F Y'))
            ->editColumn('akhir_periode', fn($row) => \Carbon\Carbon::parse($row->akhir_periode)->format('d F Y'))
            ->addColumn('jumlah_peserta', fn($row) => $row->users_count . ' Peserta')
            ->addColumn('aksi', function ($row) {
                $showUrl = route('admin.periode-pkl.show', $row->id);
                $editUrl = route('admin.periode-pkl.edit', $row->id);
                $destroyUrl = route('admin.periode-pkl.destroy', $row->id);

                $aksi = '
                    <div class="row-action">
                       <a href="'.$showUrl.'" class="btn btn-info btn-action btn-sm mx-1" title="Detail">
                <i class="fa fa-eye"></i>
            </a>
                        <a href="'.$editUrl.'" class="btn btn-warning btn-action btn-sm mx-1" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-danger btn-action btn-sm mx-1 action-hapus" data-url="'.$destroyUrl.'" title="Hapus">
                            <i class="fa fa-trash-alt"></i>
                        </button>
                    </div>
                ';

                return $aksi;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Menampilkan form untuk membuat periode baru, dengan daftar siswa & pembimbing.
     */
    public function create()
    {
        // Penjelasan: Hanya user yang BELUM punya periode yang bisa dipilih.
        $siswas = User::where('group_id', 4)->whereDoesntHave('periodePkl')->orderBy('name')->get();
        $pembimbings = User::where('group_id', 3)->orderBy('name')->get();

        return view('administrator.periode_pkl.create', compact('siswas', 'pembimbings'));
    }

    /**
     * Menyimpan data periode baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'awal_periode'    => 'required|date',
            'akhir_periode'   => 'required|date|after_or_equal:awal_periode',
            'siswa_ids'       => 'required|array|min:1',
            'pembimbing_ids'  => 'required|array|min:1',
            'siswa_ids.*'     => 'exists:user,id',
            'pembimbing_ids.*' => 'exists:user,id'
        ]);

        // Penjelasan: Validasi canggih untuk memastikan siswa tidak terdaftar di dua periode.
        $conflictingSiswaIds = DB::table('periode_pkl_user')->whereIn('user_id', $validated['siswa_ids'])->pluck('user_id')->toArray();
        if (!empty($conflictingSiswaIds)) {
            $conflictingSiswaNames = User::whereIn('id', $conflictingSiswaIds)->pluck('name')->implode(', ');
            throw ValidationException::withMessages([
                'siswa_ids' => 'Siswa berikut sudah terdaftar di periode PKL lain: ' . $conflictingSiswaNames,
            ]);
        }

        $allUserIds = array_merge($validated['siswa_ids'], $validated['pembimbing_ids']);

        // Penjelasan: Menggunakan transaction agar data aman. Jika ada gagal, semua dibatalkan.
        DB::transaction(function () use ($validated, $allUserIds) {
            $periode = PeriodePkl::create([
                'awal_periode'  => $validated['awal_periode'],
                'akhir_periode' => $validated['akhir_periode'],
            ]);
            $periode->users()->attach($allUserIds);
        });

        return redirect()->route('admin.periode-pkl.index')->with('success', 'Periode PKL berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail dari satu periode.
     */
    public function show(PeriodePkl $periodePkl)
    {
        $periodePkl->load('users.group', 'users.sekolah');
        return view('administrator.periode_pkl.show', compact('periodePkl'));
    }

    /**
     * Menampilkan form untuk mengedit periode.
     */
    public function edit(PeriodePkl $periodePkl)
    {
        // Penjelasan: Saat edit, siswa yang bisa dipilih adalah siswa yang belum punya periode ATAU siswa yang sudah ada di periode ini.
        $siswas = User::where('group_id', 4)
            ->where(function ($query) use ($periodePkl) {
                $query->whereDoesntHave('periodePkl')
                    ->orWhereHas('periodePkl', fn($q) => $q->where('periode_pkl.id', $periodePkl->id));
            })
            ->orderBy('name')
            ->get();

        $pembimbings = User::where('group_id', 3)->orderBy('name')->get();
        $selectedSiswaIds = $periodePkl->users()->where('group_id', 4)->pluck('user.id')->toArray();
        $selectedPembimbingIds = $periodePkl->users()->where('group_id', 3)->pluck('user.id')->toArray();

        return view('administrator.periode_pkl.edit', compact('periodePkl', 'siswas', 'pembimbings', 'selectedSiswaIds', 'selectedPembimbingIds'));
    }

    /**
     * Memperbarui data periode yang sudah ada.
     */
    public function update(Request $request, PeriodePkl $periodePkl)
    {
        $validated = $request->validate([
            'awal_periode'    => 'required|date',
            'akhir_periode'   => 'required|date|after_or_equal:awal_periode',
            'siswa_ids'       => 'required|array|min:1',
            'pembimbing_ids'  => 'required|array|min:1',
            'siswa_ids.*'     => 'exists:user,id',
            'pembimbing_ids.*' => 'exists:user,id'
        ]);

        // Penjelasan: Validasi yang sama seperti 'store', namun dengan pengecualian untuk periode yang sedang diedit.
        $conflictingSiswaIds = DB::table('periode_pkl_user')
            ->whereIn('user_id', $validated['siswa_ids'])
            ->where('periode_pkl_id', '!=', $periodePkl->id) // Pengecualian
            ->pluck('user_id')
            ->toArray();

        if (!empty($conflictingSiswaIds)) {
            $conflictingSiswaNames = User::whereIn('id', $conflictingSiswaIds)->pluck('name')->implode(', ');
            throw ValidationException::withMessages([
                'siswa_ids' => 'Siswa berikut sudah terdaftar di periode PKL lain: ' . $conflictingSiswaNames,
            ]);
        }

        $allUserIds = array_merge($validated['siswa_ids'], $validated['pembimbing_ids']);

        DB::transaction(function () use ($periodePkl, $request, $allUserIds) {
            $periodePkl->update($request->only(['awal_periode', 'akhir_periode']));
            // Penjelasan: Sync adalah cara terbaik untuk update relasi many-to-many.
            $periodePkl->users()->sync($allUserIds);
        });

        return redirect()->route('admin.periode-pkl.index')->with('success', 'Periode PKL berhasil diperbarui.');
    }

    /**
     * Menghapus data periode.
     */
    public function destroy(PeriodePkl $periodePkl)
    {
        // Penjelasan: Menggunakan transaction untuk detach relasi dulu sebelum menghapus.
        DB::transaction(function () use ($periodePkl) {
            $periodePkl->users()->detach();
            $periodePkl->delete();
        });

        // Return response untuk AJAX delete
        return response()->json(['success' => true, 'message' => 'Periode PKL berhasil dihapus.']);
    }
}
