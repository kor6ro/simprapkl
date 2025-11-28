<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Models\Tim;
use App\Models\User;
use App\Models\Divisi;
use App\Models\PeriodePkl;
use App\Models\JenisKegiatan;
use Illuminate\Support\Facades\Auth;
use App\Notifications\LaporanDisetujui;
use App\Notifications\LaporanPerluRevisi;
use App\Notifications\LaporanDirevisi;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use PDF;
use App\Notifications\LaporanTimLengkap; 


class LaporanController extends Controller
{
   
    public function index(Request $request)
    {
        $judul = 'Daftar Laporan';
        $isFilteredByTim = false;
        $isFilteredFromDashboard = false;

        if ($request->has('filter_user_id') && $request->has('filter_tanggal')) {
            try {
                $siswa = User::findOrFail($request->filter_user_id);
                $tanggal = Carbon::parse($request->filter_tanggal)->isoFormat('dddd, D MMMM YYYY');
                $judul = "Laporan {$siswa->name} ({$tanggal})";
                $isFilteredFromDashboard = true;
            } catch (\Exception $e) {
                Log::error('Gagal membuat judul dinamis dari dashboard: ' . $e->getMessage());
            }
        }
       elseif ($request->has('tim_id')) {
    $tim = Tim::with('ketua', 'divisi')->find($request->tim_id);
    if ($tim) {
        // UBAH BARIS INI untuk mengambil semua nama ketua
        $ketua = $tim->ketua->pluck('name')->implode(', '); 
        $divisi = $tim->divisi->nama_divisi ?? 'N/A';
        $tanggal = Carbon::parse($tim->tanggal)->isoFormat('D MMMM YYYY');
        // Judul akan menampilkan "PIC: Budi, Ani" jika ada dua ketua
        $judul = "Laporan Tim Divisi {$divisi} ({$tanggal}) - PIC: {$ketua}";
        $isFilteredByTim = true;
    }
}
        elseif (Auth::check() && Auth::user()->group_id == 4) {
            $judul = 'Riwayat Laporan Saya';
        }

        $daftarSiswa = User::where('group_id', 4)->orderBy('name')->get();
        $daftarDivisi = Divisi::orderBy('nama_divisi')->get();
        $periodePkls = PeriodePkl::orderBy('awal_periode', 'desc')->get();

        return view('administrator.laporan.index', compact('judul', 'daftarSiswa', 'daftarDivisi', 'isFilteredByTim', 'isFilteredFromDashboard', 'periodePkls'));
    }

    public function data(Request $request)
    {
       $query = Laporan::with('user', 'tim.divisi', 'jenisKegiatan', 'tim', 'approver')->orderBy('created_at', 'desc');
    
    $user = Auth::user();
        if ($user->group_id == 4) { // Jika Siswa
            // Skenario B: Jika akses dari detail tim (ada tim_id di URL)
            if ($request->filled('tim_id')) {
                // Langkah keamanan untuk memastikan siswa adalah anggota dari tim yang ingin dilihat.
                $timId = $request->tim_id;
                $isMember = $user->tim()->where('tim_id', $timId)->exists();

                if (!$isMember) {
                    
                    $query->where('user_id', $user->id);
                }
            } else {
                $query->where('user_id', $user->id);
            }
            
        } elseif ($user->group_id == 3) { // Jika Pembimbing Sekolah
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('sekolah_id', $user->sekolah_id)
                  ->where('program_keahlian_id', $user->program_keahlian_id);
            });
} elseif ($user->group_id == 5) { // [TAMBAHKAN BLOK INI]
            // Jika Karyawan/Ketua Tim, tampilkan laporan dari tim yang dia pimpin
            $query->whereHas('tim.ketua', function ($q_ketua) use ($user) {
                $q_ketua->where('user.id', $user->id);
            });
        }
        if ($request->filled('tim_id')) {
            $query->where('tim_id', $request->tim_id);
        }
        
        if ($request->filled('filter_user_id')) {
            $query->where('user_id', $request->filter_user_id);
        }
        if ($request->filled('filter_tanggal')) {
            try {
                $query->whereDate('created_at', Carbon::parse($request->filter_tanggal));
            } catch (\Exception $e) { /* Abaikan tanggal tidak valid */
            }
        }
        
        if ($request->filled('bulan')) {
            try {
                $date = Carbon::parse($request->bulan);
                $query->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month);
            } catch (\Exception $e) { /* Abaikan bulan tidak valid */
            }
        }
        if ($request->filled('periode_id')) {
            $query->whereHas('user.periodePkl', function ($q) use ($request) {
                $q->where('periode_pkl.id', $request->periode_id);
            });
        }
        if ($request->filled('nama_siswa')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->nama_siswa . '%');
            });
        }
        if ($request->filled('divisi_id')) {
            $query->whereHas('tim', function ($q) use ($request) {
                $q->where('divisi_id', $request->divisi_id);
            });
        }
      if ($request->filled('status')) {
    $query->where('status', $request->status);
}

      return DataTables::of($query)
        ->addIndexColumn()
        ->addColumn('nama_siswa', fn($laporan) => $laporan->user->name ?? 'N/A')
        ->addColumn('nama_tim', fn($laporan) => $laporan->tim->divisi->nama_divisi ?? 'N/A')
        ->addColumn('deskripsi_singkat', fn($laporan) => $laporan->deskripsi_kegiatan)
        ->editColumn('bukti_foto', fn($laporan) => $laporan->bukti_foto ? url($laporan->bukti_foto) : null)
       ->addColumn('status_laporan_data', function ($laporan) {
    $status = $laporan->status;
    $badgeClass = '';
    $displayText = '';

    //switch untuk memetakan status ke teks tampilan
    switch ($status) {
        case 'pending':
            $displayText = 'Menunggu Persetujuan ';
            $badgeClass = 'bg-warning';
            break;
        case 'approved':
            $displayText = 'Disetujui';
            $badgeClass = 'bg-success';
            break;
        case 'rejected':
            $displayText = 'Perlu Revisi';
            $badgeClass = 'bg-danger';
            break;
        default:
            $displayText = ucfirst($status);
            $badgeClass = 'bg-secondary';
            break;
    }
    
    return [
        'text' => $displayText,
        'badge_class' => $badgeClass,
        'is_rejected' => $laporan->status === 'rejected',
        'feedback' => $laporan->feedback ?? '',
        'approver_name' => optional($laporan->approver)->name ?? 'Sistem',
    ];
})
        ->editColumn('created_at', fn($laporan) => Carbon::parse($laporan->created_at)->isoFormat('D MMM YYYY, HH:mm'))
        ->addColumn('nama_kegiatan', fn($laporan) => $laporan->jenisKegiatan->nama_kegiatan ?? 'N/A')
        ->addColumn('action', function ($row) {
            $buttonsHtml = '';
            $user = auth()->user();
            $csrfField = csrf_field();
            
            // Tentukan peran
            $isKetuaTim = $row->tim->ketua->contains('id', $user->id);
            $isAdmin = in_array($user->group_id, [1, 2]); // Sesuai logika approve/reject Anda
            $isPemilikLaporan = $user->id == $row->user_id;

            // 1. Logika untuk Admin atau Ketua Tim
            if ($isAdmin || $isKetuaTim) {
                if ($row->status === 'approved') {
                    // Tombol Batalkan Persetujuan (memicu modal feedback)
                    $buttonsHtml .= '<button type="button" class="btn btn-undo-approve btn-action btn-sm mx-1 btn-reject" data-url="'.route('admin.laporan.reject', $row->id).'" title="Batalkan Persetujuan"><i class="fas fa-undo"></i></button>';
                } else if ($row->status === 'pending') {
                    // Tombol Setujui
                    $buttonsHtml .= '<form action="'.route('admin.laporan.approve', $row->id).'" method="POST" class="d-inline">'.$csrfField.'<button type="submit" class="btn btn-success btn-action btn-sm mx-1" title="Setujui"><i class="fas fa-check"></i></button></form>';
                    // Tombol Tolak (memicu modal feedback)
                    $buttonsHtml .= '<button type="button" class="btn btn-danger btn-action btn-sm mx-1 btn-reject" data-url="'.route('admin.laporan.reject', $row->id).'" title="Tolak Laporan"><i class="fas fa-times"></i></button>';
                } else if ($row->status === 'rejected') {
                    // Tombol Setujui Laporan Revisi
                    $buttonsHtml .= '<form action="'.route('admin.laporan.approve', $row->id).'" method="POST" class="d-inline">'.$csrfField.'<button type="submit" class="btn btn-success btn-action btn-sm mx-1" title="Setujui Laporan Revisi"><i class="fas fa-check"></i></button></form>';
                }
            } 
            // 2. Logika untuk Siswa (Pemilik Laporan)
            else if ($isPemilikLaporan) {

                if ($row->status == 'pending' || $row->status == 'rejected') {
                    // Tombol Edit
                    $buttonsHtml .= '<a href="'.route('admin.laporan.edit', $row->id).'" class="btn btn-warning btn-action btn-sm mx-1" title="Edit"><i class="fa fa-edit"></i></a>';
                    // Tombol Hapus (memicu modal delete)
                    $buttonsHtml .= '<button type="button" class="btn btn-danger btn-action btn-sm mx-1 delete-btn" data-url="'.route('admin.laporan.destroy', $row->id).'" title="Hapus"><i class="fa fa-trash-alt"></i></button>';
                }
            }

            // 3. Tampilkan hasil
            if (!empty($buttonsHtml)) {
                return '<div class="row-action">'.$buttonsHtml.'</div>';
            }
            return '<span class="text-muted fst-italic">Terkunci</span>';
        })
        ->rawColumns(['status_tim', 'action'])
        ->make(true);
}
  public function create()
{
    if (Auth::user()->group_id != 4) {
        abort(403, 'Hanya siswa yang dapat membuat laporan.');
    }

    $threeDaysAgo = today()->subDays(2);
    $today = today();

    $daftarTugas = Auth::user()->tim()
        ->whereIn('status_approval', ['belum_selesai', 'perlu_revisi'])
        ->whereBetween('tanggal', [$threeDaysAgo, $today])
        ->orderBy('tanggal', 'desc')
        ->get();

    $jenis_kegiatan = JenisKegiatan::orderBy('nama_kegiatan')->get();

    if ($daftarTugas->isEmpty()) {
        session()->flash('info', 'Saat ini tidak ada tugas yang bisa dilaporkan (status "Belum Selesai" atau "perlu_revisi" dalam 3 hari terakhir).');
    }

    // [TAMBAHKAN INI]
    // Cek apakah halaman ini dibuka dari URL yang sudah terfilter tim_id
    $return_context = request()->has('tim_id') ? 'team' : 'sidebar';

    // [UBAH INI] - Tambahkan 'return_context' ke compact
    return view('administrator.laporan.create', compact('daftarTugas', 'jenis_kegiatan', 'return_context'));
}
 public function store(Request $request)
{
    if (Auth::user()->group_id != 4) {
        abort(403, 'Hanya siswa yang dapat mengirim laporan.');
    }

    $validatedData = $request->validate([
        'tim_id' => 'required|exists:tim,id',
        'jenis_kegiatan_id' => 'required|exists:jenis_kegiatan,id',
        'deskripsi_kegiatan' => 'required|string',
        'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'return_context' => 'nullable|string|in:sidebar,team', // Validasi input tersembunyi
    ]);

    $path = null;
    if ($request->hasFile('bukti_foto')) {
        $file = $request->file('bukti_foto');
        $filename = time() . '_' . $file->getClientOriginalName();
        $folderTujuan = 'uploads/laporan';
        $file->move($folderTujuan, $filename);
        $path = $folderTujuan . '/' . $filename;
    }

    $laporan = Laporan::create([
        'tim_id' => $validatedData['tim_id'],
        'user_id' => Auth::id(),
        'jenis_kegiatan_id' => $validatedData['jenis_kegiatan_id'],
        'deskripsi_kegiatan' => $validatedData['deskripsi_kegiatan'],
        'bukti_foto' => $path,
    ]);

    // Logika notifikasi Anda (sudah benar)
    $tim = Tim::with('ketua', 'anggota')->find($laporan->tim_id);
    if ($tim) {
        $jumlahAnggota = $tim->anggota()->count();
        $jumlahPelapor = Laporan::where('tim_id', $tim->id)->distinct('user_id')->count();

        if ($jumlahPelapor >= $jumlahAnggota) {
            $ketuaTim = $tim->ketua;
            foreach ($ketuaTim as $ketua) {
                $ketua->notify(new LaporanTimLengkap($tim));
            }
        }
    }

    // === [PERBAIKAN LOGIKA REDIRECT] ===
    // Cek nilai 'return_context' yang dikirim dari form
    if ($request->input('return_context') === 'team') {
        // Jika datang dari halaman tim, kembali ke halaman tim
        return redirect()->route('admin.laporan.index', ['tim_id' => $laporan->tim_id])
            ->with('success', 'Laporan berhasil dikirim!');
    }

    // Jika tidak (atau jika 'return_context' kosong), kembali ke sidebar
    return redirect()->route('admin.laporan.index')
        ->with('success', 'Laporan berhasil dikirim!');
}
    public function edit(Laporan $laporan)
{
    // ===== PERBAIKAN OTORISASI =====
    if (Auth::user()->group_id == 4 && $laporan->user_id != Auth::id()) {
        abort(403, 'Anda tidak memiliki izin untuk mengedit laporan ini.');
    }

    $daftarTugas = Auth::user()->tim()->orderBy('tanggal', 'desc')->get();
    $jenis_kegiatan = JenisKegiatan::orderBy('nama_kegiatan')->get();

    // [TAMBAHKAN INI]
    // Cek apakah halaman ini dibuka dari URL yang sudah terfilter tim_id
    $return_context = request()->has('tim_id') ? 'team' : 'sidebar';

    // [UBAH INI] - Tambahkan 'return_context' ke compact
    return view('administrator.laporan.edit', compact('laporan', 'daftarTugas', 'jenis_kegiatan', 'return_context'));
}

public function update(Request $request, Laporan $laporan)
{
    if (Auth::user()->group_id == 4 && $laporan->user_id != Auth::id()) {
        abort(403, 'Anda tidak memiliki izin untuk memperbarui laporan ini.');
    }

    $validatedData = $request->validate([
        'tim_id' => 'required|exists:tim,id',
        'jenis_kegiatan_id' => 'required|exists:jenis_kegiatan,id',
        'deskripsi_kegiatan' => 'required|string',
        'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'return_context' => 'nullable|string|in:sidebar,team', // Validasi input tersembunyi
    ]);

    $path = $laporan->bukti_foto;
    if ($request->hasFile('bukti_foto')) {
        if ($laporan->bukti_foto && file_exists(public_path($laporan->bukti_foto))) {
            unlink(public_path($laporan->bukti_foto));
        }

        $file = $request->file('bukti_foto');
        $filename = time() . '_' . $file->getClientOriginalName();
        $folderTujuan = 'uploads/laporan';
        $file->move($folderTujuan, $filename);
        $path = $folderTujuan . '/' . $filename;
    }

    $laporan->update([
        'tim_id' => $validatedData['tim_id'],
        'jenis_kegiatan_id' => $validatedData['jenis_kegiatan_id'],
        'deskripsi_kegiatan' => $validatedData['deskripsi_kegiatan'],
        'bukti_foto' => $path,
    ]);

    // Logika notifikasi revisi Anda (sudah benar)
    if ($laporan->status == 'rejected') {
        $laporan->update(['status' => 'pending']);
        $tim = $laporan->tim;
        if ($tim) {
            $ketuaTim = $tim->ketua;
            foreach ($ketuaTim as $ketua) {
                $ketua->notify(new LaporanDirevisi($laporan));
            }
        }
    }

    // === [PERBAIKAN LOGIKA REDIRECT] ===
    // Cek nilai 'return_context' yang dikirim dari form
    if ($request->input('return_context') === 'team') {
        // Jika datang dari halaman tim, kembali ke halaman tim
        return redirect()->route('admin.laporan.index', ['tim_id' => $laporan->tim_id])
            ->with('success', 'Laporan berhasil diperbarui!');
    }

    // Jika tidak (atau jika 'return_context' kosong), kembali ke sidebar
    return redirect()->route('admin.laporan.index')
        ->with('success', 'Laporan berhasil diperbarui!');
}
    public function destroy(Laporan $laporan)
    {
        if (Auth::user()->group_id == 4 && $laporan->user_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus laporan ini.');
        }
        
        if ($laporan->bukti_foto && file_exists(public_path($laporan->bukti_foto))) {
            unlink(public_path($laporan->bukti_foto));
        }
        $laporan->delete();

        return response()->json(['success' => 'Laporan berhasil dihapus!']);
    }
 public function approve(Laporan $laporan)
{
    $user = auth()->user();
    $isTeamLeader = $user->timKetua()->where('tim_id', $laporan->tim_id)->exists();
    $isAdmin = in_array($user->group_id, [1, 2]);

    if (!$isTeamLeader && !$isAdmin) {
        abort(403, 'Anda tidak memiliki wewenang untuk melakukan tindakan ini.');
    }

    // Update laporan saat ini
    $laporan->update([
        'status' => 'approved',
        'approver_id' => auth()->id(),
        'feedback'=> null
    ]);
    
    // Kirim notifikasi ke pembuat laporan
    $pembuatLaporan = $laporan->user;
    if ($pembuatLaporan) {
        $pembuatLaporan->notify(new LaporanDisetujui($laporan));
    }

    // --- [PERBAIKAN LOGIKA STATUS TIM] ---
    $tim = $laporan->tim;
    
    // 1. Hitung jumlah total anggota dalam tim
    $jumlahAnggota = $tim->anggota()->count();

    // 2. Hitung berapa banyak ANGGOTA UNIK yang sudah punya laporan 'approved'
    $jumlahAnggotaApproved = $tim->laporan()
                                ->where('status', 'approved')
                                ->distinct('user_id')
                                ->count();

    // 3. Cek apakah masih ada laporan lain di tim ini yang statusnya 'pending'
    $adaLaporanPending = $tim->laporan()->where('status', 'pending')->exists();

    // 4. Status tim selesai HANYA JIKA:
    //    - Semua anggota sudah punya setidaknya satu laporan approved
    //    - DAN sudah tidak ada lagi laporan yang pending di tim itu
    if ($jumlahAnggotaApproved >= $jumlahAnggota && !$adaLaporanPending) {
        $tim->update(['status_approval' => 'tugas_selesai']);
    }
    // --- [AKHIR PERBAIKAN] ---

    return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
}
public function reject(Request $request, Laporan $laporan)
{

    $user = auth()->user();
    $isTeamLeader = $user->timKetua()->where('tim_id', $laporan->tim_id)->exists();
    $isAdmin = in_array($user->group_id, [1, 2]);

    if (!$isTeamLeader && !$isAdmin) {
        abort(403, 'Anda tidak memiliki wewenang untuk melakukan tindakan ini.');
    }

    $request->validate(['feedback' => 'required|string|max:1000']);
    $pembuatLaporan = $laporan->user;
    if ($pembuatLaporan) {
        $pembuatLaporan->notify(new LaporanPerluRevisi($laporan));
    }
    // Update status laporan - tidak berubah
    $laporan->update([
        'status' => 'rejected',
        'feedback' => $request->feedback,
        'approver_id' => auth()->id()
    ]);
    $tim = $laporan->tim;
    if ($tim && $tim->status_approval === 'tugas_selesai') {
        $tim->update(['status_approval' => 'belum_selesai']);
    }

    return redirect()->back()->with('success', 'Laporan berhasil ditolak dan dikembalikan untuk revisi.');
}
public function exportPDF(Request $request)
{
    $query = Laporan::with('user', 'tim.divisi', 'jenisKegiatan', 'tim');

    if ($request->filled('tim_id')) {
        $query->where('tim_id', $request->tim_id);
    }
    if ($request->filled('filter_user_id')) {
        $query->where('user_id', $request->filter_user_id);
    }
    if ($request->filled('filter_tanggal')) {
        try {
            $query->whereDate('created_at', Carbon::parse($request->filter_tanggal));
        } catch (\Exception $e) {}
    }
    if ($request->filled('bulan')) {
        try {
            $date = Carbon::parse($request->bulan);
            $query->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month);
        } catch (\Exception $e) {}
    }
    if ($request->filled('periode_id')) {
        $query->whereHas('user.periodePkl', function ($q) use ($request) {
            $q->where('periode_pkl.id', $request->periode_id);
        });
    }
    if ($request->filled('nama_siswa')) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->nama_siswa . '%');
        });
    }
    if ($request->filled('divisi_id')) {
        $query->whereHas('tim', fn($q) => $q->where('divisi_id', $request->divisi_id));
    }
   if ($request->filled('status')) {
    $query->where('status', $request->status);
}

    $laporan = $query->get();

    $judul = 'Laporan Kegiatan';
    $subjudul = ''; 

    if ($request->has('filter_user_id') && $request->has('filter_tanggal')) {
        try {
            $siswa = User::findOrFail($request->filter_user_id);
            $tanggal = Carbon::parse($request->filter_tanggal)->isoFormat('dddd, D MMMM YYYY');
            $judul = "Laporan {$siswa->name}";
            $subjudul = "Pada {$tanggal}";
        } catch (\Exception $e) {}
    }
elseif ($request->has('tim_id')) {
    // Eager load relasi ketua secara penuh
    $tim = Tim::with('ketua', 'divisi')->find($request->tim_id); 
    if ($tim) {
        $tanggal = Carbon::parse($tim->tanggal)->isoFormat('D MMMM YYYY');
        $judul = "Laporan Tim Divisi {$tim->divisi->nama_divisi}";
        // UBAH BARIS INI untuk mengambil semua nama ketua
        $ketuaNames = $tim->ketua->pluck('name')->implode(', ');
        $subjudul = "PIC: {$ketuaNames} - Pada {$tanggal}";
    }
}
// ...
    else {
        $subjudul = 'Filter: ';
        $filters = [];
        if($request->filled('bulan')) $filters[] = 'Bulan ' . Carbon::parse($request->bulan)->isoFormat('MMMM YYYY');
        if($request->filled('periode_id')) {
            $periode = PeriodePkl::find($request->periode_id);
            if ($periode) {
                 $filters[] = 'Periode: ' . Carbon::parse($periode->awal_periode)->format('d M Y');
            }
        }
        if($request->filled('nama_siswa')) $filters[] = 'Nama Siswa: "' . $request->nama_siswa . '"';
        if($request->filled('divisi_id')) $filters[] = 'Divisi ' . Divisi::find($request->divisi_id)->nama_divisi;
        if($request->filled('status')) $filters[] = 'Status ' . ucfirst(str_replace('_', ' ', $request->status));
        $subjudul .= count($filters) > 0 ? implode(', ', $filters) : 'Semua Data';
    }

    $pdf = \PDF::loadView('administrator.laporan.pdf', [
        'laporan' => $laporan,
        'judul' => $judul,
        'subjudul' => $subjudul
    ]);

    return $pdf->stream('laporan-kegiatan-' . date('Y-m-d') . '.pdf');
}
}