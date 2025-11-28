<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\PeriodePkl;
use App\Models\ProgramKeahlian;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NewUsersExport;
use App\Exports\UserCredentialsExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $periodePkl = PeriodePkl::orderBy('awal_periode', 'desc')->get();

        return view("administrator.user.index", compact('periodePkl'));
    }

    public function fetch(Request $request)
    {
        $users = User::where("group_id", "<>", 1)
                    ->with("sekolah", "group")
                    ->orderBy("group_id", "asc");

        return DataTables::of($users)
            ->addIndexColumn()
            ->editColumn('id_pkl', fn($row) => $row->id_pkl ?? '-')
            ->make(true);
    }

    public function create()
    {
        $group = Group::whereNotIn('id', [1])->get();
        $sekolah = Sekolah::all();
        $programKeahlian = ProgramKeahlian::all();
        return view('administrator.user.create', compact('group', 'sekolah', 'programKeahlian'));
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => "required",
            "username" => "required|unique:user,username",
            "email" => "required|email|unique:user,email",
            "password" => "required|min:6",
            "group_id" => "required",
            "sekolah_id" => "required_if:group_id,3,4",
            "program_keahlian_id" => "required_if:group_id,4"
        ]);

        $dataSave = $request->except(['_token', 'password']);
        $dataSave['password'] = Hash::make($request->input('password'));
        $dataSave['validasi'] = $request->has('validasi') ? 1 : 0;
        
        if (!in_array($request->input('group_id'), [3, 4])) {
            $dataSave['sekolah_id'] = null;
            $dataSave['program_keahlian_id'] = null;
        }

        User::create($dataSave);
        return redirect()->route("admin.user.index")->with("success", "Data user berhasil disimpan");
    }

    public function edit($id)
    {
        $user = User::with('periodePkl')->findOrFail($id);
        $sekolah = Sekolah::all();
        $group = Group::whereNotIn('id', [1, 6, 7])->get();
        $programKeahlian = ProgramKeahlian::all();
        $periodePkl = PeriodePkl::orderBy('awal_periode', 'desc')->get();
        return view("administrator.user.edit", compact('user', 'sekolah', 'group', 'programKeahlian', 'periodePkl'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            "name" => "required",
            "username" => "required|unique:user,username," . $id,
            "email" => "required|email|unique:user,email," . $id,
            "group_id" => "required|exists:group,id",
            "sekolah_id" => "required_if:group_id,3,4|nullable|exists:sekolah,id",
            "program_keahlian_id" => "required_if:group_id,4|nullable|exists:program_keahlian,id",
            "periode_pkl_id" => "nullable|exists:periode_pkl,id",
            'password' => 'nullable|string|min:6',
        ]);

        $dataSave = $request->only(['name', 'username', 'email', 'group_id', 'sekolah_id', 'program_keahlian_id', 'alamat']);
        $dataSave['validasi'] = $request->has('validasi') ? 1 : 0;
        
        if (!in_array($request->input('group_id'), [3, 4])) {
            $dataSave['sekolah_id'] = null;
            $dataSave['program_keahlian_id'] = null;
        }

        if ($request->filled('password')) {
            $dataSave['password'] = Hash::make($request->input('password'));
        }
        
        $user->update($dataSave);

        $isSiswaOrPembimbing = in_array($request->input('group_id'), [3, 4]);
        
        if ($isSiswaOrPembimbing && $request->filled('periode_pkl_id')) {
            $user->periodePkl()->sync([$request->periode_pkl_id]);
        } else {
            $user->periodePkl()->detach();
        }

        return redirect()->route("admin.user.index")->with("success", "Data user berhasil diupdate");
    }

    public function resetPassword($id)
    {
        try {
            $user = User::findOrFail($id);
            $newPassword = 'sandya' . Str::random(4);
            $user->password = Hash::make($newPassword);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password untuk ' . $user->name . ' berhasil direset.',
                'new_password' => $newPassword
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal reset password untuk user ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mereset password.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['success' => 'User berhasil dihapus!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghapus user.'], 500);
        }
    }

    public function batchCreate()
    {
        $sekolahs = Sekolah::orderBy('nama', 'asc')->get();
        $groups = Group::whereIn('id', [2, 3, 4, 5,])->orderBy('nama', 'asc')->get();
        $programKeahlians = ProgramKeahlian::orderBy('nama', 'asc')->get();
        $periodePkl = PeriodePkl::orderBy('awal_periode', 'desc')->get();

        return view('administrator.user.batch_create', compact('sekolahs', 'groups', 'programKeahlians', 'periodePkl'));
    }

    public function batchStore(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'periode_id' => 'nullable|string',
                'awal_periode' => 'required_if:periode_id,new|date|nullable',
                'akhir_periode' => 'required_if:periode_id,new|date|after_or_equal:awal_periode|nullable',
                'users' => 'required|array|min:1',
                'users.*.name' => 'required|string|max:255',
                'users.*.username' => 'required|string|max:255|distinct|unique:user,username',
                'users.*.email' => 'nullable|string|email|max:255|distinct|unique:user,email',
                'users.*.password' => 'nullable|string|min:6',
                'users.*.group_id' => 'required|exists:group,id',
                'users.*.sekolah_id' => 'required_if:users.*.group_id,3,4|nullable|exists:sekolah,id',
                'users.*.program_keahlian_id' => 'required_if:users.*.group_id,4|nullable|exists:program_keahlian,id',
            ], [
                'users.*.sekolah_id.required_if' => 'Kolom Sekolah wajib diisi jika role adalah Siswa/Pembimbing.',
                'users.*.program_keahlian_id.required_if' => 'Kolom Program Keahlian wajib diisi jika role adalah Siswa.',
                'users.*.username.unique' => 'Username :input sudah ada di database.',
                'users.*.email.unique' => 'Email :input sudah ada di database.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::beginTransaction();
            
            $periodePkl = null;
            if ($request->filled('periode_id')) {
                $periodeId = $request->input('periode_id');
                if ($periodeId === 'new') {
                    $periodePkl = PeriodePkl::create([
                        'nama' => 'Periode Batch ' . now()->format('d M Y H:i'),
                        'awal_periode' => $request->awal_periode,
                        'akhir_periode' => $request->akhir_periode,
                    ]);
                } else if ($periodeId) {
                    $periodePkl = PeriodePkl::findOrFail($periodeId);
                }
            }
            
            $userIdsToAttach = [];
            $newlyCreatedUsersForExport = new Collection();

            foreach ($request->users as $userData) {
                $isSiswaOrPembimbing = in_array($userData['group_id'], [3, 4]);
                $plainPassword = !empty($userData['password']) ? $userData['password'] : Str::random(8);
                $email = !empty($userData['email']) ? $userData['email'] : $userData['username'] . '@sistem.pkl';
                
                $user = User::create([
                    'name' => trim($userData['name']),
                    'username' => trim($userData['username']),
                    'email' => trim($email),
                    'password' => Hash::make($plainPassword),
                    'sekolah_id' => $isSiswaOrPembimbing ? ($userData['sekolah_id'] ?? null) : null,
                    'program_keahlian_id' => $isSiswaOrPembimbing ? ($userData['program_keahlian_id'] ?? null) : null,
                    'group_id' => $userData['group_id'],
                    'validasi' => 1,
                    'alamat' => '-'
                ]);

                if ($isSiswaOrPembimbing) {
                    $userIdsToAttach[] = $user->id;
                }

                $newlyCreatedUsersForExport->push([
                    'Nama Lengkap' => trim($userData['name']),
                    'Username' => trim($userData['username']),
                    'Password' => $plainPassword,
                ]);
            }

            if ($periodePkl && !empty($userIdsToAttach)) {
                $periodePkl->users()->attach($userIdsToAttach);
            }
            
            DB::commit();
            
            // Perubahan utama ada di sini
            $fileName = 'kredensial_user_baru_' . now()->format('d-m-Y_His') . '.xlsx';
            // 1. Simpan file ke storage/app/public/temp/
            Excel::store(new NewUsersExport($newlyCreatedUsersForExport), 'public/temp/' . $fileName);

            // 2. Redirect halaman dengan pesan sukses dan URL untuk download
            return redirect()->route('admin.user.index')
                ->with('success', 'Data user berhasil disimpan!')
                ->with('download_url', route('admin.user.batch.download', ['filename' => $fileName]));

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())
                ->withInput();
        }
    }
        public function batchDownload($filename)
    {
        $filePath = 'public/temp/' . $filename;

        // Periksa apakah file ada untuk menghindari error
        if (!Storage::exists($filePath)) {
            abort(404, 'File not found.');
        }

        // Ambil path absolut ke file
        $absolutePath = Storage::path($filePath);

        // Kembalikan response download dan hapus file setelahnya secara otomatis
        return response()->download($absolutePath)->deleteFileAfterSend(true);
    }
    public function exportCredentials($periodeId)
    {
        $siswaGroupId = Group::where('nama', 'Siswa')->value('id');
        if (!$siswaGroupId) {
            return redirect()->route('admin.user.index')->with('error', 'Grup "Siswa" tidak ditemukan.');
        }
        $siswas = User::with('sekolah')
            ->where('group_id', $siswaGroupId)
            ->whereHas('periodePkl', fn ($q) => $q->where('periode_pkl.id', $periodeId))
            ->get();
        if ($siswas->isEmpty()) {
            return redirect()->route('admin.user.index')->with('error', 'Tidak ada siswa pada periode tersebut.');
        }
        $exportData = collect();
        foreach ($siswas as $siswa) {
            $newPassword = Str::random(8);
            $siswa->password = Hash::make($newPassword);
            $siswa->save();
            $exportData->push([
                'name' => $siswa->name,
                'username' => $siswa->username,
                'new_password' => $newPassword,
                'sekolah' => $siswa->sekolah->nama ?? 'N/A',
            ]);
        }
        $periode = PeriodePkl::find($periodeId);
        $periodeName = $periode ? str_replace(' ', '_', $periode->nama) : 'periode_terpilih';
        $fileName = 'kredensial_' . $periodeName . '_' . now()->format('d-m-Y') . '.xlsx';
        return Excel::download(new UserCredentialsExport($exportData), $fileName);
    }

    public function ajaxStoreSekolah(Request $request)
    {
        $validator = Validator::make($request->all(), ['nama_sekolah' => 'required|string|max:255|unique:sekolah,nama']);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        $sekolah = new Sekolah;
        $sekolah->nama = $request->input('nama_sekolah');
        $sekolah->save();
        return response()->json(['success' => true, 'sekolah' => $sekolah]);
    }

    public function ajaxStoreProgramKeahlian(Request $request)
    {
        $validator = Validator::make($request->all(), ['nama' => 'required|string|max:255|unique:program_keahlian,nama']);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        $program = ProgramKeahlian::create(['nama' => $request->nama]);
        return response()->json(['success' => true, 'program' => $program]);
    }
}