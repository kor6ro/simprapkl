<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DetailPenilaian;
use App\Models\KriteriaPenilaian;
use App\Models\Penilaian;
use App\Models\PeriodePkl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PenilaianController extends Controller
{

    public function index()
    {
        // [PERBAIKAN] Sesuaikan data periode berdasarkan grup user
        $user = Auth::user();
        if ($user->group_id == 3) {
            // Jika pembimbing, hanya ambil periode yang terkait dengannya
            $periodes = $user->periodePkl()->orderBy('awal_periode', 'desc')->get();
        } else {
            // Jika admin atau grup lain, ambil semua periode
            $periodes = PeriodePkl::orderBy('awal_periode', 'desc')->get();
        }
        return view('administrator.penilaian.index', compact('periodes'));
    }

    public function fetch(Request $request)
    {
        // [PERBAIKAN] Terapkan filter data utama untuk pembimbing
        $user = Auth::user();
        $periodeId = $request->input('periode_id');
        $query = Penilaian::with(['siswa', 'penilai.group', 'detailPenilaian']);

        if ($user->group_id == 3) {
            // Jika pembimbing, saring penilaian berdasarkan sekolah dan program keahlian siswa
            $query->whereHas('siswa', function ($q) use ($user) {
                $q->where('sekolah_id', $user->sekolah_id)
                  ->where('program_keahlian_id', $user->program_keahlian_id);
            });
        }

        if ($user->group_id == 4) {
            // Filter untuk siswa tetap ada
            $query->where('siswa_id', $user->id);
        }

        if ($periodeId) {
            $query->whereHas('siswa.periodePkl', function ($q) use ($periodeId) {
                $q->where('periode_pkl.id', $periodeId);
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('siswa_nama', function ($row) {
                return $row->siswa?->name ?? 'N/A';
            })
            ->addColumn('penilai_nama', function ($row) {
                $namaPenilai = $row->penilai?->name ?? 'N/A';
                $jabatan = $row->penilai?->group?->name ?? 'Penilai';
                return $namaPenilai . ' (' . $jabatan . ')';
            })
            ->editColumn('tanggal_penilaian', function ($row) {
                return $row->tanggal_penilaian ? \Carbon\Carbon::parse($row->tanggal_penilaian)->locale('id')->isoFormat('D MMMM YYYY') : 'N/A';
            })
            ->editColumn('nilai_rata_rata', function ($row) {
                return $row->nilai_rata_rata !== null ? number_format($row->nilai_rata_rata, 2) : 'Belum Dinilai';
            })
            ->addColumn('id', function($row){
                return $row->id;
            })
            ->rawColumns(['penilai_nama'])
            ->make(true);
    }

    public function create()
    {
        $siswas = User::where('group_id', 4)->orderBy('name')->get();
        $kriteria = KriteriaPenilaian::all();
        return view('administrator.penilaian.create', compact('siswas', 'kriteria'));
    }

    public function getPeriodeBySiswa(User $user)   
    {
        $periode = $user->periodePkl()->first();
        if ($periode) {
            return response()->json([
                'success'       => true,
                'awal_periode'  => $periode->awal_periode->format('Y-m-d'),
                'akhir_periode' => $periode->akhir_periode->format('Y-m-d'),
            ]);
        }
        return response()->json(['success' => false]);
    }

    /**
     * Membuat draf penilaian untuk siswa dalam periode tertentu.
     */
    public function batchCreate(Request $request)
    {
        $request->validate(['periode_id' => 'required|exists:periode_pkl,id']);

        try {
            $penilai = User::where('group_id', 6)->first();
            $penilaiId = $penilai ? $penilai->id : Auth::id();

            $siswaSudahDinilai = Penilaian::pluck('siswa_id')->unique();

            $siswasToCreate = User::where('group_id', 4)
                ->whereHas('periodePkl', function ($q) use ($request) {
                    $q->where('periode_pkl.id', $request->input('periode_id'));
                })
                ->whereNotIn('id', $siswaSudahDinilai)
                ->with('periodePkl')
                ->get();

            if ($siswasToCreate->isEmpty()) {
                return redirect('/admin/penilaian')->with('success', 'Tidak ada siswa baru yang perlu dibuatkan draf penilaian.');
            }

            $dataToInsert = [];
            $now = now();

            foreach ($siswasToCreate as $siswa) {
                $periode = $siswa->periodePkl->first();
                $tanggalMulai = $periode ? $periode->awal_periode : null;
                $tanggalSelesai = $periode ? $periode->akhir_periode : null;

                $dataToInsert[] = [
                    'siswa_id' => $siswa->id,
                    'penilai_id' => $penilaiId,
                    'tanggal_penilaian' => $now->toDateString(),
                    'pkl_tanggal_mulai' => $tanggalMulai,
                    'pkl_tanggal_selesai' => $tanggalSelesai,
                    'komentar_saran' => null,
                    'nilai_rata_rata' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('penilaian')->insert($dataToInsert);
            $message = 'Berhasil membuat ' . count($dataToInsert) . ' draf penilaian baru.';

            return redirect('/admin/penilaian')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Gagal membuat draf penilaian massal: ' . $e->getMessage());
            return redirect('/admin/penilaian')->with([
                'dataSaved' => false,
                'message' => 'Terjadi kesalahan saat membuat draf penilaian.'
            ]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|integer',
            'pkl_tanggal_mulai' => 'required|date',
            'pkl_tanggal_selesai' => 'required|date|after_or_equal:pkl_tanggal_mulai',
            'tanggal_penilaian' => 'required|date',
            'komentar_saran' => 'nullable|string',
            'nilai' => 'required|array',
            'nilai.*' => 'required|integer|min:0|max:100',
        ]);
        try {
            DB::transaction(function () use ($validated) {
                $nilaiRataRata = collect($validated['nilai'])->avg();
                $penilai = User::whereHas('group', function ($q) {
                    $q->whereIn('name', ['kepala', 'wakil']);
                })->first();
                $penilaiId = $penilai ? $penilai->id : Auth::id();

                $penilaian = Penilaian::create([
                    'siswa_id' => $validated['siswa_id'],
                    'pkl_tanggal_mulai' => $validated['pkl_tanggal_mulai'],
                    'pkl_tanggal_selesai' => $validated['pkl_tanggal_selesai'],
                    'penilai_id' => $penilaiId,
                    'tanggal_penilaian' => $validated['tanggal_penilaian'],
                    'komentar_saran' => $validated['komentar_saran'],
                    'nilai_rata_rata' => $nilaiRataRata,
                ]);
                foreach ($validated['nilai'] as $variabel => $skor) {
                    $penilaian->detailPenilaian()->create([
                        'variabel' => $variabel,
                        'nilai' => $skor,
                    ]);
                }
            });
          } catch (\Exception $e) {
            Log::error('Gagal menyimpan penilaian: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }

        return redirect('/admin/penilaian')->with('success', 'Penilaian berhasil disimpan!');
    }

    public function show(Penilaian $penilaian)
    {

        $allowedGroupIds = [1, 2, 3, 6]; // Tambahkan grup 3 (Pembimbing)
        if (!in_array(Auth::user()->group_id, $allowedGroupIds)) {
            abort(403, 'ANDA TIDAK MEMILIKI IZIN UNTUK MELAKUKAN AKSI INI.');
        }


        $penilaian->load(['siswa', 'penilai', 'detailPenilaian']);
        $kriteria = KriteriaPenilaian::all()->pluck('nama_variabel', 'kode_variabel');
        return view('administrator.penilaian.show', compact('penilaian', 'kriteria'));
    }

    public function edit(Penilaian $penilaian)
    {
        $penilaian->load('detailPenilaian');
        $detailPenilaian = $penilaian->detailPenilaian->pluck('nilai', 'variabel');
        $siswas = User::where('group_id', 4)->orderBy('name')->get();
        $kriteria = KriteriaPenilaian::all();
        return view('administrator.penilaian.edit', compact('penilaian', 'detailPenilaian', 'siswas', 'kriteria'));
    }
    public function update(Request $request, Penilaian $penilaian)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|integer',
            'tanggal_penilaian' => 'required|date',
            'komentar_saran' => 'nullable|string',
            'nilai' => 'required|array',
            'nilai.*' => 'required|integer|min:0|max:100',
        ]);
        try {
            DB::transaction(function () use ($validated, $penilaian) {
                $nilaiRataRata = collect($validated['nilai'])->avg();
                $penilaian->update([
                    'siswa_id' => $validated['siswa_id'],
                    'tanggal_penilaian' => $validated['tanggal_penilaian'],
                    'komentar_saran' => $validated['komentar_saran'],
                    'nilai_rata_rata' => $nilaiRataRata,
                ]);
                foreach ($validated['nilai'] as $variabel => $skor) {
                    $penilaian->detailPenilaian()->updateOrCreate(
                        ['variabel' => $variabel],
                        ['nilai' => $skor]
                    );
                }
            });
       } catch (\Exception $e) {
            Log::error('Gagal memperbarui penilaian: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui data, silakan coba lagi.');
        }
        return redirect('/admin/penilaian')->with('success', 'Penilaian berhasil diperbarui!');
    }

    public function destroy(Penilaian $penilaian)
    {
        $allowedGroupIds = [1, 2, 6];
        if (!in_array(Auth::user()->group_id, $allowedGroupIds)) {

            return response()->json(['error' => 'Anda tidak memiliki izin untuk melakukan aksi ini.'], 403);
        }

        try {
            $penilaian->delete();
            return response()->json(['success' => 'Penilaian berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus penilaian: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menghapus data.'], 500);
        }
    }
    
    public function cetakPDF(Penilaian $penilaian)
    {
        $penilaian->load(['siswa.programKeahlian', 'penilai', 'detailPenilaian']);
        $detailPenilaian = $penilaian->detailPenilaian;
        $kriteria = \App\Models\KriteriaPenilaian::all()->pluck('nama_variabel', 'kode_variabel');
        $watermark = null;
        $imagePath = public_path('assets/images/SandyaNet-Corp-Logo_Horizontal_Transparent-hires.png');
        if (file_exists($imagePath)) {
            $type = pathinfo($imagePath, PATHINFO_EXTENSION);
            $data = file_get_contents($imagePath);
            $watermark = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        $data = [
            'penilaian' => $penilaian,
            'detailPenilaian' => $detailPenilaian,
            'kriteria' => $kriteria,
            'watermark' => $watermark,
        ];
        $pdf = PDF::loadView('administrator.penilaian.cetak', $data);

        $f4_paper_size = [0, 0, 595.28, 935.43];
        $pdf->setPaper($f4_paper_size, 'landscape');
        return $pdf->stream('penilaian-' . $penilaian->siswa->name . '.pdf');
    }

    public function cetakSertifikat(Penilaian $penilaian)
    {
        $penilaian->load(['siswa.sekolah', 'siswa.programKeahlian', 'penilai.group']);

        $logo_base64 = null;
        $imagePath = public_path('assets/images/SandyaNet-Corp-Logo_Horizontal_Transparent-hires.png');
        if (file_exists($imagePath)) {
            $type = pathinfo($imagePath, PATHINFO_EXTENSION);
            $data = file_get_contents($imagePath);
            $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $background_base64 = null;
        $bgImagePath = public_path('assets/images/bg-sertifikat.png');
        if (file_exists($bgImagePath)) {
            $bgType = pathinfo($bgImagePath, PATHINFO_EXTENSION);
            $bgData = file_get_contents($bgImagePath);
            $background_base64 = 'data:image/' . $bgType . ';base64,' . base64_encode($bgData);
        }

        $data = [
            'nama_penerima'         => $penilaian->siswa->name,
            'asal_sekolah'          => $penilaian->siswa->sekolah->nama ?? 'Data Sekolah Tidak Ditemukan',
            'tanggal_mulai'         => \Carbon\Carbon::parse($penilaian->pkl_tanggal_mulai),
            'tanggal_selesai'       => \Carbon\Carbon::parse($penilaian->pkl_tanggal_selesai),
            'kota_penerbitan'       => 'Pacitan',
            'tanggal_penerbitan'    => \Carbon\Carbon::parse($penilaian->tanggal_penilaian),
            'nama_penandatangan'    => $penilaian->penilai->name ?? 'Nama Penilai Tidak Ditemukan',
            'jabatan_penandatangan' => $penilaian->penilai->group->nama ?? 'Jabatan Tidak Ditemukan',
            'logo_base64'           => $logo_base64,
            'background_base64'     => $background_base64,
        ];

        $pdf = PDF::loadView('administrator.penilaian.sertifikat', $data);

        $f4_paper_size = [0, 0, 595.28, 935.43];
        $pdf->setPaper($f4_paper_size, 'landscape');

        return $pdf->stream('Sertifikat - ' . $data['nama_penerima'] . '.pdf');
    }
}