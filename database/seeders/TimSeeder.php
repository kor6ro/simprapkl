<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tim;
use App\Models\User;
use App\Models\Divisi; // 1. TAMBAHKAN MODEL DIVISI
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 2. AMBIL SEMUA ID DARI TABEL DIVISI
        $divisiIds = Divisi::pluck('id');
        // Hentikan seeder jika tidak ada divisi
        if ($divisiIds->isEmpty()) {
            $this->command->info('Tidak ada data di tabel Divisi, TimSeeder dilewati.');
            return;
        }

        $ketuaIds = User::where('group_id', 5)->pluck('id');
        $anggotaIds = User::where('group_id', 4)->pluck('id');

        // Hentikan jika tidak ada ketua atau anggota
        if ($ketuaIds->isEmpty() || $anggotaIds->isEmpty()) {
            $this->command->info('Tidak ada Karyawan (group 5) atau Siswa (group 4), TimSeeder dilewati.');
            return;
        }
    
        $startDate = Carbon::create(2025, 5, 1);
        $endDate = Carbon::create(2025, 7, 31);
        
        DB::transaction(function () use ($startDate, $endDate, $ketuaIds, $anggotaIds, $divisiIds) {
            
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                
                $jumlahTimPerHari = rand(2, 5);

                for ($i = 0; $i < $jumlahTimPerHari; $i++) {
                    $randomKetuaId = $ketuaIds->random();
                    
                    // 3. AMBIL ID DIVISI SECARA ACAK
                    $randomDivisiId = $divisiIds->random();
                    // Ambil nama divisi untuk deskripsi
                    $divisiInfo = Divisi::find($randomDivisiId);

                    $jumlahAnggota = rand(2, 4);
                    if ($anggotaIds->count() < $jumlahAnggota) {
                        $jumlahAnggota = $anggotaIds->count();
                    }
                    $randomAnggotaIds = $anggotaIds->random($jumlahAnggota)->all();

                    $jamAcak = rand(8, 16);
                    $menitAcak = rand(0, 59);
                    $detikAcak = rand(0, 59);
                    $timestampPalsu = $date->copy()->setTime($jamAcak, $menitAcak, $detikAcak);

                    // 4. GUNAKAN 'divisi_id' SAAT MEMBUAT TIM
                    $tim = Tim::create([
                        'ketua_id'   => $randomKetuaId,
                        'divisi_id'  => $randomDivisiId, // <-- PERUBAHAN UTAMA
                        'tanggal'    => $date->toDateString(),
                        'created_at' => $timestampPalsu,
                        'updated_at' => $timestampPalsu,
                    ]);

                    $tim->anggota()->sync($randomAnggotaIds);
                }
            }
        });
    }
}