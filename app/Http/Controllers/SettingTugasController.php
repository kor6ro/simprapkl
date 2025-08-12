<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SettingTugas;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingTugasController extends Controller
{
    public function index()
    {
        // Ambil admin yang sudah menjadi ketua tim hari ini
        $adminYangSudahTerdaftar = SettingTugas::whereDate('tanggal', today())
            ->pluck('ketua_id')
            ->toArray();

        // Ambil user admin sebagai calon ketua tim (kecuali admin yang sedang login dan yang sudah terdaftar)
        $availableAdmins = User::where('group_id', 2)
            ->where('id', '!=', auth()->id())
            ->get();

        // Tandai admin yang sudah terdaftar
        $availableAdmins = $availableAdmins->map(function ($admin) use ($adminYangSudahTerdaftar) {
            $admin->sudah_terdaftar = in_array($admin->id, $adminYangSudahTerdaftar);
            return $admin;
        });

        // Tim Sales hari ini dengan anggota
        $timSales = SettingTugas::with(['ketua', 'anggota'])
            ->where('divisi', 'sales')
            ->whereDate('tanggal', today())
            ->get();

        // Tim Teknisi hari ini dengan anggota
        $timTeknisi = SettingTugas::with(['ketua', 'anggota'])
            ->where('divisi', 'teknisi')
            ->whereDate('tanggal', today())
            ->get();

        // Ambil semua siswa yang sudah terdaftar di tim hari ini
        $siswaYangSudahTerdaftar = DB::table('setting_tugas_anggota')
            ->join('setting_tugas', 'setting_tugas_anggota.setting_tugas_id', '=', 'setting_tugas.id')
            ->whereDate('setting_tugas.tanggal', today())
            ->pluck('setting_tugas_anggota.user_id')
            ->toArray();

        // Ambil semua user siswa
        $availableSiswa = User::where('group_id', 4)->get();
        
        // Tandai siswa yang sudah terdaftar
        $availableSiswa = $availableSiswa->map(function ($siswa) use ($siswaYangSudahTerdaftar) {
            $siswa->sudah_terdaftar = in_array($siswa->id, $siswaYangSudahTerdaftar);
            return $siswa;
        });

        // Debug log
        Log::info('Admin yang sudah terdaftar hari ini:', $adminYangSudahTerdaftar);
        Log::info('Siswa yang sudah terdaftar hari ini:', $siswaYangSudahTerdaftar);
        Log::info('Total tim Sales: ' . $timSales->count());
        Log::info('Total tim Teknisi: ' . $timTeknisi->count());

        return view('administrator.setting_tugas.index', compact(
            'availableAdmins', 
            'availableSiswa', 
            'timSales', 
            'timTeknisi',
            'siswaYangSudahTerdaftar',
            'adminYangSudahTerdaftar'
        ));
    }

    public function store(Request $request)
    {
        // Log request untuk debugging
        Log::info('SettingTugas Store Request:', $request->all());

        try {
            // Validasi input dasar
            $validated = $request->validate([
                'ketua_id' => 'required|exists:user,id',
                'divisi' => 'required|in:sales,teknisi',
                'anggota' => 'required|array|min:1',
                'anggota.*' => 'exists:user,id'
            ]);

            // Validasi khusus: Cek apakah ada siswa yang sudah terdaftar di tim lain hari ini
            $isUpdate = $request->has('team_id') && !str_starts_with($request->team_id, 'new_');
            $currentTeamId = $isUpdate ? $request->team_id : null;

            // Query untuk cek siswa yang sudah terdaftar (exclude tim yang sedang diedit)
            $siswaYangSudahTerdaftarQuery = DB::table('setting_tugas_anggota')
                ->join('setting_tugas', 'setting_tugas_anggota.setting_tugas_id', '=', 'setting_tugas.id')
                ->whereDate('setting_tugas.tanggal', today())
                ->whereIn('setting_tugas_anggota.user_id', $validated['anggota']);
            
            if ($currentTeamId) {
                $siswaYangSudahTerdaftarQuery->where('setting_tugas.id', '!=', $currentTeamId);
            }
            
            $siswaYangSudahTerdaftar = $siswaYangSudahTerdaftarQuery
                ->join('user', 'setting_tugas_anggota.user_id', '=', 'user.id')
                ->select('user.name', 'user.id')
                ->get();

            if ($siswaYangSudahTerdaftar->count() > 0) {
                $namaYangSudahTerdaftar = $siswaYangSudahTerdaftar->pluck('name')->join(', ');
                $message = "Siswa berikut sudah terdaftar di tim lain hari ini: {$namaYangSudahTerdaftar}";
                
                Log::warning('Siswa sudah terdaftar:', [
                    'siswa' => $siswaYangSudahTerdaftar->toArray(),
                    'current_team_id' => $currentTeamId
                ]);
                
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $message]);
                }
                return back()->with('error', $message);
            }

            DB::beginTransaction();

            if ($isUpdate) {
                // Update tim yang sudah ada
                $settingTugas = SettingTugas::findOrFail($request->team_id);
                
                // Cek apakah ketua baru sudah digunakan hari ini (kecuali tim ini sendiri)
                $existingTim = SettingTugas::where('ketua_id', $validated['ketua_id'])
                    ->whereDate('tanggal', today())
                    ->where('id', '!=', $request->team_id)
                    ->first();

                if ($existingTim) {
                    DB::rollback();
                    $message = 'Admin tersebut sudah menjadi ketua tim lain hari ini!';
                    
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => $message]);
                    }
                    return back()->with('error', $message);
                }

                // Update data tim
                $settingTugas->update([
                    'ketua_id' => $validated['ketua_id'],
                    'divisi' => $validated['divisi']
                ]);

                // Sync anggota tim (akan menghapus yang lama dan menambah yang baru)
                $settingTugas->anggota()->sync($validated['anggota']);

            } else {
                // Cek apakah ketua sudah digunakan hari ini
                $existingTim = SettingTugas::where('ketua_id', $validated['ketua_id'])
                    ->whereDate('tanggal', today())
                    ->first();

                if ($existingTim) {
                    DB::rollback();
                    $message = 'Admin tersebut sudah menjadi ketua tim lain hari ini!';
                    
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => $message]);
                    }
                    return back()->with('error', $message);
                }

                // Buat tim baru
                $settingTugas = SettingTugas::create([
                    'ketua_id' => $validated['ketua_id'],
                    'divisi' => $validated['divisi'],
                    'tanggal' => today()
                ]);

                // Tambahkan anggota tim
                $settingTugas->anggota()->attach($validated['anggota']);
            }

            DB::commit();
            
            $message = 'Tim berhasil disimpan!';
            Log::info('SettingTugas berhasil disimpan:', ['id' => $settingTugas->id]);
            
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            
            return back()->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            Log::error('Validation Error:', $e->errors());
            
            $message = 'Data tidak valid: ' . implode(', ', array_flatten($e->errors()));
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => $message,
                    'errors' => $e->errors()
                ]);
            }
            
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('SettingTugas Store Error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $message = 'Terjadi kesalahan saat menyimpan tim: ' . $e->getMessage();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => $message,
                    'debug' => config('app.debug') ? $e->getMessage() : null
                ]);
            }
            
            return back()->with('error', $message);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $settingTugas = SettingTugas::findOrFail($id);
            
            // Hapus anggota tim (many-to-many relationship)
            $settingTugas->anggota()->detach();
            
            // Hapus tim
            $settingTugas->delete();

            DB::commit();
            
            $message = 'Tim berhasil dihapus!';
            
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('SettingTugas Delete Error:', [
                'message' => $e->getMessage(),
                'id' => $id
            ]);
            
            $message = 'Terjadi kesalahan saat menghapus tim: ' . $e->getMessage();
            
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            
            return back()->with('error', $message);
        }
    }

    public function swapDivisi()
    {
        DB::beginTransaction();
        try {
            $timHariIni = SettingTugas::whereDate('tanggal', today())->get();

            foreach ($timHariIni as $tim) {
                $tim->divisi = $tim->divisi === 'sales' ? 'teknisi' : 'sales';
                $tim->save();
            }

            DB::commit();
            return back()->with('success', 'Semua divisi tim berhasil ditukar!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('SwapDivisi Error:', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menukar divisi: ' . $e->getMessage());
        }
    }
}