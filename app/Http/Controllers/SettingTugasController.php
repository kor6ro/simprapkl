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

        // Ambil user admin sebagai calon ketua tim (kecuali admin yang sedang login)
        $availableAdmins = User::where('group_id', 2)
            ->where('id', '!=', auth()->id())
            ->get();

        // Tandai admin yang sudah terdaftar
        $availableAdmins = $availableAdmins->map(function ($admin) use ($adminYangSudahTerdaftar) {
            $admin->sudah_terdaftar = in_array($admin->id, $adminYangSudahTerdaftar);
            return $admin;
        });

        // Tim Sales hari ini dengan anggota (diurutkan berdasarkan created_at)
        $timSales = SettingTugas::with(['ketua', 'anggota'])
            ->where('divisi', 'sales')
            ->whereDate('tanggal', today())
            ->orderBy('created_at', 'asc')
            ->get();

        // Tim Teknisi hari ini dengan anggota (diurutkan berdasarkan created_at)
        $timTeknisi = SettingTugas::with(['ketua', 'anggota'])
            ->where('divisi', 'teknisi')
            ->whereDate('tanggal', today())
            ->orderBy('created_at', 'asc')
            ->get();

        // Ambil semua siswa yang sudah terdaftar di tim hari ini
        $siswaYangSudahTerdaftar = DB::table('setting_tugas_anggota')
            ->join('setting_tugas', 'setting_tugas_anggota.setting_tugas_id', '=', 'setting_tugas.id')
            ->whereDate('setting_tugas.tanggal', today())
            ->pluck('setting_tugas_anggota.user_id')
            ->toArray();

        // Ambil semua user siswa
        $availableSiswa = User::where('group_id', 4)
            ->orderBy('name', 'asc')
            ->get();
        
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
            'timTeknisi'
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

            // Validasi tambahan: Pastikan ketua adalah admin (group_id = 2)
            $ketua = User::find($validated['ketua_id']);
            if (!$ketua || $ketua->group_id != 2) {
                Log::warning('Ketua bukan admin:', ['ketua_id' => $validated['ketua_id']]);
                
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Ketua tim harus dari grup admin!']);
                }
                return back()->with('error', 'Ketua tim harus dari grup admin!');
            }

            // Validasi tambahan: Pastikan anggota adalah siswa (group_id = 4)
            $anggotaUsers = User::whereIn('id', $validated['anggota'])->get();
            $nonSiswa = $anggotaUsers->where('group_id', '!=', 4);
            if ($nonSiswa->count() > 0) {
                $namaNonSiswa = $nonSiswa->pluck('name')->join(', ');
                Log::warning('Anggota bukan siswa:', ['non_siswa' => $nonSiswa->pluck('id')->toArray()]);
                
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => "Anggota berikut bukan siswa: {$namaNonSiswa}"]);
                }
                return back()->with('error', "Anggota berikut bukan siswa: {$namaNonSiswa}");
            }

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
            
            $message = $isUpdate ? 'Tim berhasil diperbarui!' : 'Tim berhasil dibuat!';
            Log::info('SettingTugas berhasil disimpan:', [
                'id' => $settingTugas->id,
                'action' => $isUpdate ? 'update' : 'create'
            ]);
            
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

    // public function destroy($id)
    // {
    //     // Validasi ID harus numeric
    //     if (!is_numeric($id)) {
    //         if (request()->ajax()) {
    //             return response()->json([
    //                 'success' => false, 
    //                 'message' => 'ID tim tidak valid'
    //             ]);
    //         }
    //         return back()->with('error', 'ID tim tidak valid');
    //     }
    //     DB::beginTransaction();
    //     try {
    //         $settingTugas = SettingTugas::findOrFail($id);
            
    //         // Log untuk audit
    //         Log::info('Menghapus tim:', [
    //             'id' => $settingTugas->id,
    //             'ketua' => $settingTugas->ketua->name ?? 'Unknown',
    //             'divisi' => $settingTugas->divisi,
    //             'anggota_count' => $settingTugas->anggota->count()
    //         ]);
            
    //         // Hapus anggota tim (many-to-many relationship)
    //         $settingTugas->anggota()->detach();
            
    //         // Hapus tim
    //         $settingTugas->delete();

    //         DB::commit();
            
    //         $message = 'Tim berhasil dihapus!';
            
    //         if (request()->ajax()) {
    //             return response()->json(['success' => true, 'message' => $message]);
    //         }
            
    //         return back()->with('success', $message);

    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         Log::error('SettingTugas Delete Error:', [
    //             'message' => $e->getMessage(),
    //             'id' => $id
    //         ]);
            
    //         $message = 'Terjadi kesalahan saat menghapus tim: ' . $e->getMessage();
            
    //         if (request()->ajax()) {
    //             return response()->json(['success' => false, 'message' => $message]);
    //         }
            
    //         return back()->with('error', $message);
    //     }
    // }

    /**
     * Delete all teams for today (batch delete)
     */
public function destroy($id)
{
    // PERBAIKAN: Handle khusus untuk destroy-all
    if ($id === 'destroy-all' || $id === 'all') {
        return $this->destroyAll();
    }
    
    // Validasi ID harus numeric
    if (!is_numeric($id)) {
        if (request()->ajax()) {
            return response()->json([
                'success' => false, 
                'message' => 'ID tim tidak valid'
            ]);
        }
        return back()->with('error', 'ID tim tidak valid');
    }
    
    DB::beginTransaction();
    try {
        $settingTugas = SettingTugas::findOrFail($id);
        
        // Log untuk audit
        Log::info('Menghapus tim:', [
            'id' => $settingTugas->id,
            'ketua' => $settingTugas->ketua->name ?? 'Unknown',
            'divisi' => $settingTugas->divisi,
            'anggota_count' => $settingTugas->anggota->count()
        ]);
        
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

/**
 * Delete all teams for today - METHOD YANG SUDAH DIPERBAIKI
 */
public function destroyAll()
{
    DB::beginTransaction();
    try {
        // Ambil semua tim hari ini
        $teams = SettingTugas::whereDate('tanggal', today())->get();
        
        if ($teams->isEmpty()) {
            return response()->json([
                'success' => false, 
                'message' => 'Tidak ada tim untuk dihapus hari ini!'
            ]);
        }
        
        $deletedCount = 0;
        
        foreach ($teams as $team) {
            // Log untuk audit
            Log::info('Menghapus tim (batch delete):', [
                'id' => $team->id,
                'ketua' => optional($team->ketua)->name ?? 'Unknown',
                'divisi' => $team->divisi,
                'anggota_count' => $team->anggota()->count()
            ]);
            
            // Hapus anggota tim terlebih dahulu (many-to-many relationship)
            $team->anggota()->detach();
            
            // Hapus tim
            $team->delete();
            $deletedCount++;
        }

        DB::commit();
        
        $message = "Berhasil menghapus semua {$deletedCount} tim hari ini!";
        
        Log::info('Batch Delete Success:', [
            'deleted_count' => $deletedCount,
            'date' => today()->toDateString()
        ]);
        
        return response()->json([
            'success' => true, 
            'message' => $message,
            'deleted_count' => $deletedCount
        ]);

    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Batch Delete Error:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false, 
            'message' => 'Terjadi kesalahan saat menghapus tim: ' . $e->getMessage()
        ]);
    }
}
    /**
     * Update multiple teams at once (bulk update)
     */
    public function updateBulk(Request $request)
    {
        Log::info('Bulk Update Request:', $request->all());

        try {
            $request->validate([
                'updates' => 'required|array|min:1',
                'updates.*.team_id' => 'required|exists:setting_tugas,id',
                'updates.*.ketua_id' => 'required|exists:user,id',
                'updates.*.divisi' => 'required|in:sales,teknisi',
                'updates.*.anggota' => 'required|array|min:1',
                'updates.*.anggota.*' => 'exists:user,id'
            ]);

            $updates = $request->updates;

            // Validasi duplikasi dalam request
            $ketuaIds = collect($updates)->pluck('ketua_id')->toArray();
            if (count($ketuaIds) !== count(array_unique($ketuaIds))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ada ketua yang sama! Setiap ketua hanya boleh memimpin satu tim.'
                ]);
            }

            $allAnggota = collect($updates)->pluck('anggota')->flatten()->toArray();
            if (count($allAnggota) !== count(array_unique($allAnggota))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ada anggota yang terdaftar di beberapa tim! Setiap siswa hanya boleh ikut satu tim.'
                ]);
            }

            // Validasi: Pastikan ketua adalah admin
            $ketuaUsers = User::whereIn('id', $ketuaIds)->get();
            $nonAdmin = $ketuaUsers->where('group_id', '!=', 2);
            if ($nonAdmin->count() > 0) {
                $namaNonAdmin = $nonAdmin->pluck('name')->join(', ');
                return response()->json([
                    'success' => false,
                    'message' => "Ketua berikut bukan admin: {$namaNonAdmin}"
                ]);
            }

            // Validasi: Pastikan anggota adalah siswa
            $anggotaUsers = User::whereIn('id', $allAnggota)->get();
            $nonSiswa = $anggotaUsers->where('group_id', '!=', 4);
            if ($nonSiswa->count() > 0) {
                $namaNonSiswa = $nonSiswa->pluck('name')->join(', ');
                return response()->json([
                    'success' => false,
                    'message' => "Anggota berikut bukan siswa: {$namaNonSiswa}"
                ]);
            }

            // Validasi: Cek konflik ketua dengan tim lain hari ini (yang tidak sedang diedit)
            $teamIdsBeingUpdated = collect($updates)->pluck('team_id')->toArray();
            $existingKetua = SettingTugas::whereDate('tanggal', today())
                ->whereIn('ketua_id', $ketuaIds)
                ->whereNotIn('id', $teamIdsBeingUpdated)
                ->with('ketua')
                ->get();

            if ($existingKetua->count() > 0) {
                $namaKetua = $existingKetua->pluck('ketua.name')->join(', ');
                return response()->json([
                    'success' => false,
                    'message' => "Admin berikut sudah menjadi ketua tim lain hari ini: {$namaKetua}"
                ]);
            }

            // Validasi: Cek konflik anggota dengan tim lain hari ini (yang tidak sedang diedit)
            $existingAnggota = DB::table('setting_tugas_anggota')
                ->join('setting_tugas', 'setting_tugas_anggota.setting_tugas_id', '=', 'setting_tugas.id')
                ->join('user', 'setting_tugas_anggota.user_id', '=', 'user.id')
                ->whereDate('setting_tugas.tanggal', today())
                ->whereIn('setting_tugas_anggota.user_id', $allAnggota)
                ->whereNotIn('setting_tugas.id', $teamIdsBeingUpdated)
                ->select('user.name')
                ->get();

            if ($existingAnggota->count() > 0) {
                $namaAnggota = $existingAnggota->pluck('name')->join(', ');
                return response()->json([
                    'success' => false,
                    'message' => "Siswa berikut sudah terdaftar di tim lain hari ini: {$namaAnggota}"
                ]);
            }

            DB::beginTransaction();

            $updatedCount = 0;
            
            foreach ($updates as $updateData) {
                $settingTugas = SettingTugas::findOrFail($updateData['team_id']);
                
                // Update data tim
                $settingTugas->update([
                    'ketua_id' => $updateData['ketua_id'],
                    'divisi' => $updateData['divisi']
                ]);

                // Sync anggota tim
                $settingTugas->anggota()->sync($updateData['anggota']);
                
                $updatedCount++;
                
                Log::info('Tim berhasil diupdate (bulk):', [
                    'id' => $settingTugas->id,
                    'ketua_id' => $updateData['ketua_id'],
                    'divisi' => $updateData['divisi'],
                    'anggota_count' => count($updateData['anggota'])
                ]);
            }

            DB::commit();

            Log::info('Bulk Update Success:', [
                'updated_count' => $updatedCount,
                'total_teams' => count($updates)
            ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil memperbarui {$updatedCount} tim!"
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            Log::error('Bulk Update Validation Error:', $e->errors());
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', array_flatten($e->errors())),
                'errors' => $e->errors()
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk Update Error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui tim: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store multiple teams at once (bulk insert)
     */
    public function storeBulk(Request $request)
    {
        Log::info('Bulk Store Request:', $request->all());

        try {
            // Validasi input dasar
            $request->validate([
                'teams' => 'required|array|min:1',
                'teams.*.ketua_id' => 'required|exists:user,id',
                'teams.*.divisi' => 'required|in:sales,teknisi',
                'teams.*.anggota' => 'required|array|min:1',
                'teams.*.anggota.*' => 'exists:user,id'
            ]);

            $teams = $request->teams;

            // Validasi duplikasi dalam request
            $ketuaIds = collect($teams)->pluck('ketua_id')->toArray();
            if (count($ketuaIds) !== count(array_unique($ketuaIds))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ada ketua yang sama dalam form! Setiap ketua hanya boleh memimpin satu tim.'
                ]);
            }

            $allAnggota = collect($teams)->pluck('anggota')->flatten()->toArray();
            if (count($allAnggota) !== count(array_unique($allAnggota))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ada anggota yang terdaftar di beberapa tim! Setiap siswa hanya boleh ikut satu tim.'
                ]);
            }

            // Validasi: Pastikan ketua adalah admin
            $ketuaUsers = User::whereIn('id', $ketuaIds)->get();
            $nonAdmin = $ketuaUsers->where('group_id', '!=', 2);
            if ($nonAdmin->count() > 0) {
                $namaNonAdmin = $nonAdmin->pluck('name')->join(', ');
                return response()->json([
                    'success' => false,
                    'message' => "Ketua berikut bukan admin: {$namaNonAdmin}"
                ]);
            }

            // Validasi: Pastikan anggota adalah siswa
            $anggotaUsers = User::whereIn('id', $allAnggota)->get();
            $nonSiswa = $anggotaUsers->where('group_id', '!=', 4);
            if ($nonSiswa->count() > 0) {
                $namaNonSiswa = $nonSiswa->pluck('name')->join(', ');
                return response()->json([
                    'success' => false,
                    'message' => "Anggota berikut bukan siswa: {$namaNonSiswa}"
                ]);
            }

            // Validasi: Cek apakah ada ketua yang sudah digunakan hari ini
            $existingKetua = SettingTugas::whereDate('tanggal', today())
                ->whereIn('ketua_id', $ketuaIds)
                ->with('ketua')
                ->get();

            if ($existingKetua->count() > 0) {
                $namaKetua = $existingKetua->pluck('ketua.name')->join(', ');
                return response()->json([
                    'success' => false,
                    'message' => "Admin berikut sudah menjadi ketua tim lain hari ini: {$namaKetua}"
                ]);
            }

            // Validasi: Cek apakah ada anggota yang sudah terdaftar hari ini
            $existingAnggota = DB::table('setting_tugas_anggota')
                ->join('setting_tugas', 'setting_tugas_anggota.setting_tugas_id', '=', 'setting_tugas.id')
                ->join('user', 'setting_tugas_anggota.user_id', '=', 'user.id')
                ->whereDate('setting_tugas.tanggal', today())
                ->whereIn('setting_tugas_anggota.user_id', $allAnggota)
                ->select('user.name')
                ->get();

            if ($existingAnggota->count() > 0) {
                $namaAnggota = $existingAnggota->pluck('name')->join(', ');
                return response()->json([
                    'success' => false,
                    'message' => "Siswa berikut sudah terdaftar di tim lain hari ini: {$namaAnggota}"
                ]);
            }

            DB::beginTransaction();

            $createdTeams = [];
            
            foreach ($teams as $teamData) {
                // Buat tim baru
                $settingTugas = SettingTugas::create([
                    'ketua_id' => $teamData['ketua_id'],
                    'divisi' => $teamData['divisi'],
                    'tanggal' => today()
                ]);

                // Tambahkan anggota tim
                $settingTugas->anggota()->attach($teamData['anggota']);
                
                $createdTeams[] = [
                    'id' => $settingTugas->id,
                    'ketua' => $settingTugas->ketua->name,
                    'divisi' => $settingTugas->divisi,
                    'anggota_count' => count($teamData['anggota'])
                ];
            }

            DB::commit();

            Log::info('Bulk Store Success:', [
                'teams_created' => count($createdTeams),
                'teams' => $createdTeams
            ]);

            return response()->json([
                'success' => true,
                'message' => sprintf('Berhasil membuat %d tim! (%d Sales, %d Teknisi)', 
                    count($createdTeams),
                    collect($teams)->where('divisi', 'sales')->count(),
                    collect($teams)->where('divisi', 'teknisi')->count()
                ),
                'data' => $createdTeams
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            Log::error('Bulk Store Validation Error:', $e->errors());
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', array_flatten($e->errors())),
                'errors' => $e->errors()
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk Store Error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan tim: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get all teams data for editing (untuk future implementation)
     */
    public function getAllTeamsForEdit()
    {
        try {
            // Ambil semua tim hari ini dengan relasi
            $teams = SettingTugas::with(['ketua', 'anggota'])
                ->whereDate('tanggal', today())
                ->orderBy('created_at', 'asc')
                ->get();
                
            $teamsData = $teams->map(function($team) {
                return [
                    'id' => $team->id,
                    'ketua_id' => $team->ketua_id,
                    'ketua_name' => $team->ketua->name ?? 'Unknown',
                    'divisi' => $team->divisi,
                    'anggota' => $team->anggota->map(function($anggota) {
                        return [
                            'id' => $anggota->id,
                            'name' => $anggota->name
                        ];
                    })
                ];
            });
            
            return response()->json([
                'success' => true, 
                'data' => $teamsData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get All Teams For Edit Error:', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Gagal mengambil data tim untuk edit'
            ]);
        }
    }

    /**
     * Get tim statistics for today
     */
    public function getStatistics()
    {
        try {
            $today = today();
            
            $stats = [
                'total_tim' => SettingTugas::whereDate('tanggal', $today)->count(),
                'tim_sales' => SettingTugas::where('divisi', 'sales')->whereDate('tanggal', $today)->count(),
                'tim_teknisi' => SettingTugas::where('divisi', 'teknisi')->whereDate('tanggal', $today)->count(),
                'total_anggota' => DB::table('setting_tugas_anggota')
                    ->join('setting_tugas', 'setting_tugas_anggota.setting_tugas_id', '=', 'setting_tugas.id')
                    ->whereDate('setting_tugas.tanggal', $today)
                    ->count(),
                'admin_tersedia' => User::where('group_id', 2)
                    ->where('id', '!=', auth()->id())
                    ->whereNotIn('id', SettingTugas::whereDate('tanggal', $today)->pluck('ketua_id'))
                    ->count(),
                'siswa_tersedia' => User::where('group_id', 4)
                    ->whereNotIn('id', DB::table('setting_tugas_anggota')
                        ->join('setting_tugas', 'setting_tugas_anggota.setting_tugas_id', '=', 'setting_tugas.id')
                        ->whereDate('setting_tugas.tanggal', $today)
                        ->pluck('setting_tugas_anggota.user_id'))
                    ->count()
            ];
            
            return response()->json(['success' => true, 'data' => $stats]);
            
        } catch (\Exception $e) {
            Log::error('Get Statistics Error:', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal mengambil statistik']);
        }
    }

    public function swapDivisi()
    {
        DB::beginTransaction();
        try {
            // Ambil semua tim hari ini dengan anggota
            $timSales = SettingTugas::with('anggota')
                ->where('divisi', 'sales')
                ->whereDate('tanggal', today())
                ->get();
            
            $timTeknisi = SettingTugas::with('anggota')
                ->where('divisi', 'teknisi')
                ->whereDate('tanggal', today())
                ->get();

            // Validasi: pastikan ada tim dari kedua divisi
            if ($timSales->isEmpty() || $timTeknisi->isEmpty()) {
                DB::rollback();
                return back()->with('error', 'Tidak bisa menukar anggota karena salah satu divisi tidak memiliki tim!');
            }

            // Kumpulkan semua anggota dari tim sales
            $anggotaSales = collect();
            foreach ($timSales as $tim) {
                foreach ($tim->anggota as $anggota) {
                    $anggotaSales->push($anggota->id);
                }
            }

            // Kumpulkan semua anggota dari tim teknisi
            $anggotaTeknisi = collect();
            foreach ($timTeknisi as $tim) {
                foreach ($tim->anggota as $anggota) {
                    $anggotaTeknisi->push($anggota->id);
                }
            }

            // Log untuk debugging
            Log::info('Swap Debug:', [
                'anggota_sales' => $anggotaSales->toArray(),
                'anggota_teknisi' => $anggotaTeknisi->toArray(),
                'tim_sales_count' => $timSales->count(),
                'tim_teknisi_count' => $timTeknisi->count()
            ]);

            // Hapus semua relasi anggota yang ada
            foreach ($timSales as $tim) {
                $tim->anggota()->detach();
            }
            foreach ($timTeknisi as $tim) {
                $tim->anggota()->detach();
            }

            // Fungsi untuk mendistribusikan anggota secara merata
            $distributeMembers = function($members, $teams) {
                $memberArray = $members->toArray();
                $memberCount = count($memberArray);
                $teamCount = $teams->count();
                
                if ($memberCount == 0 || $teamCount == 0) {
                    Log::warning('Tidak ada anggota atau tim untuk didistribusi', [
                        'member_count' => $memberCount,
                        'team_count' => $teamCount
                    ]);
                    return;
                }
                
                // Shuffle anggota untuk distribusi yang lebih acak
                shuffle($memberArray);
                
                // Hitung jumlah anggota per tim
                $baseCount = intval($memberCount / $teamCount);
                $remainder = $memberCount % $teamCount;
                
                $memberIndex = 0;
                
                foreach ($teams as $teamIndex => $team) {
                    // Tentukan jumlah anggota untuk tim ini
                    $membersForThisTeam = $baseCount + ($teamIndex < $remainder ? 1 : 0);
                    
                    // Ambil anggota untuk tim ini
                    $teamMembers = array_slice($memberArray, $memberIndex, $membersForThisTeam);
                    $memberIndex += $membersForThisTeam;
                    
                    // Attach anggota ke tim
                    if (!empty($teamMembers)) {
                        $team->anggota()->attach($teamMembers);
                        Log::info("Tim {$team->id} ({$team->divisi}) mendapat anggota:", $teamMembers);
                    }
                }
            };

            // Distribusi anggota yang tadinya di sales ke tim teknisi
            $distributeMembers($anggotaSales, $timTeknisi);
            
            // Distribusi anggota yang tadinya di teknisi ke tim sales
            $distributeMembers($anggotaTeknisi, $timSales);

            DB::commit();
            
            // Hitung statistik untuk pesan
            $totalAnggotaSales = $anggotaSales->count();
            $totalAnggotaTeknisi = $anggotaTeknisi->count();
            $totalTimSales = $timSales->count();
            $totalTimTeknisi = $timTeknisi->count();
            
            $message = sprintf(
                'Berhasil menukar anggota tim! %d anggota sales didistribusikan ke %d tim teknisi, %d anggota teknisi didistribusikan ke %d tim sales.',
                $totalAnggotaSales,
                $totalTimTeknisi,
                $totalAnggotaTeknisi,
                $totalTimSales
            );
            
            Log::info('SwapDivisi Success:', [
                'anggota_sales_moved' => $totalAnggotaSales,
                'anggota_teknisi_moved' => $totalAnggotaTeknisi,
                'tim_sales_count' => $totalTimSales,
                'tim_teknisi_count' => $totalTimTeknisi
            ]);
            
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('SwapDivisi Error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat menukar anggota: ' . $e->getMessage());
        }
    }
}