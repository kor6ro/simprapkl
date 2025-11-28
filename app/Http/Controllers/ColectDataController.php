<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ColectData;
use Illuminate\Http\Request;
use App\Exports\ColectDataExport;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ColectDataController extends Controller
{
    public function index()
    {
        // Kirim daftar siswa ke view untuk filter
        $siswa = User::where('group_id', 4)->orderBy('name')->get();
        return view("administrator.colect_data.index", compact('siswa'));
    }

    public function fetch(Request $request)
    {
      $query = ColectData::with("user")->orderBy('created_at', 'desc');

        // Filter berdasarkan bulan dan tahun
        if ($request->filled('filter_bulan')) {
            try {
                $date = Carbon::parse($request->filter_bulan);
                $query->whereYear('tanggal', $date->year)
                      ->whereMonth('tanggal', $date->month);
            } catch (\Exception $e) {
                // Abaikan jika format bulan tidak valid
            }
        }

        // Filter berdasarkan nama siswa
        if ($request->filled('filter_nama_siswa')) {
            $namaSiswa = $request->filter_nama_siswa;
            $query->whereHas('user', function ($q) use ($namaSiswa) {
                $q->where('name', 'like', '%' . $namaSiswa . '%');
            });
        }

        // [DIUBAH] Mengganti isRole('Siswa') dengan pengecekan group_id langsung
        if (auth()->check() && auth()->user()->group_id == 4) {
            $query->where('user_id', Auth::id());
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('tanggal', function ($row) {
                 // Menambahkan pengecekan untuk memastikan tanggal tidak null
                return $row->tanggal ? Carbon::parse($row->tanggal)->isoFormat('D MMMM YYYY') : '-';
            })
            ->addColumn('aksi', function ($row) {
                $editUrl = route('admin.colect_data.edit', $row->id);
                $destroyUrl = route('admin.colect_data.destroy', $row->id);
                $user = Auth::user(); // Ambil user yang sedang login

                $buttons = '<div class="row-action">';
                $buttons .= '<button type="button" class="btn btn-info btn-action btn-sm mx-1 action-detail" title="Detail"><i class="fa fa-eye"></i></button>';

                // Logika Baru: Cek jika user adalah Admin (group_id 1 atau 2) ATAU pemilik data
                if (in_array($user->group_id, [1, 2]) || $row->user_id == $user->id) {
                    $buttons .= '<a href="' . $editUrl . '" class="btn btn-warning btn-action btn-sm mx-1 action-edit" title="Edit"><i class="fa fa-edit"></i></a>';
                }

                // Logika Baru: Cek jika user adalah Admin (group_id 1 atau 2)
                if (in_array($user->group_id, [1, 2])) {
                    $buttons .= '<button class="btn btn-danger btn-action btn-sm mx-1 action-hapus" data-url="' . $destroyUrl . '" title="Hapus"><i class="fa fa-trash-alt"></i></button>';
                }

                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }
    
    public function exportExcel(Request $request)
    {
        $bulan = $request->input('filter_bulan');
        $namaSiswa = $request->input('filter_nama_siswa');
        $fileName = 'collect_data_' . date('Y-m-d') . '.xlsx';
        return Excel::download(new ColectDataExport($bulan, $namaSiswa), $fileName);
    }

    public function create()
    {
        return view("administrator.colect_data.create");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "tanggal" => "required|date",
            "nama_cus" => "required|string|max:255",
            "no_telp" => "nullable|string",
            "alamat_cus" => "required|string",
            "provider_sekarang" => "required|string",
            "kelebihan" => "nullable|string",
            "kekurangan" => "nullable|string",
            "serlok" => "nullable|string",
            "gambar_foto" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",
        ]);
        $validated['user_id'] = Auth::id();
        if ($request->hasFile("gambar_foto")) {
            $file = $request->file("gambar_foto");
            $fileName = $file->hashName();
            $file->move("uploads/colect_data_gambar_foto", $fileName);
            $validated["gambar_foto"] = $fileName;
        }
        ColectData::create($validated);
        return redirect(route("admin.colect_data.index"))->with("success", "Data berhasil disimpan");
    }

    public function edit(ColectData $colectData)
    {
        return view("administrator.colect_data.edit", ['colect_data' => $colectData]);
    }

    public function update(Request $request, ColectData $colectData)
    {
        // [DIUBAH] Mengganti isRole('Siswa') dengan pengecekan group_id langsung
        if ((auth()->check() && auth()->user()->group_id == 4) && $colectData->user_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengupdate data ini.');
        }

        $validated = $request->validate([
            "tanggal" => "required|date",
            "nama_cus" => "required|string|max:255",
            "no_telp" => "nullable|string",
            "alamat_cus" => "required|string",
            "provider_sekarang" => "required|string",
            "kelebihan" => "nullable|string",
            "kekurangan" => "nullable|string",
            "serlok" => "nullable|string",
            "gambar_foto" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",
        ]);

        if ($request->hasFile("gambar_foto")) {
            if ($colectData->gambar_foto && File::exists(public_path("uploads/colect_data_gambar_foto/" . $colectData->gambar_foto))) {
                File::delete(public_path("uploads/colect_data_gambar_foto/" . $colectData->gambar_foto));
            }
            $file = $request->file("gambar_foto");
            $fileName = $file->hashName();
            $file->move("uploads/colect_data_gambar_foto", $fileName);
            $validated["gambar_foto"] = $fileName;
        }

       $colectData->update($validated);
        return redirect(route("admin.colect_data.index"))->with("success", "Data berhasil diupdate");
    }

    public function destroy(ColectData $colectData)
    {
        // [DIUBAH] Mengganti isRole('Siswa') dengan pengecekan group_id langsung
        if ((auth()->check() && auth()->user()->group_id == 4) && $colectData->user_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus data ini.');
        }

        if ($colectData->gambar_foto && File::exists(public_path("uploads/colect_data_gambar_foto/" . $colectData->gambar_foto))) {
            File::delete(public_path("uploads/colect_data_gambar_foto/" . $colectData->gambar_foto));
        }
        
        $colectData->delete();
       return response()->json(["success" => "Data berhasil dihapus"]);
    }
}