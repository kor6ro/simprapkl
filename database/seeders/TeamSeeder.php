<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SettingTugas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       

        $ketuaIds = User::where('group_id', 5)->pluck('id');
        $anggotaIds = User::where('group_id', 4)->pluck('id');

    
        $startDate = Carbon::create(2025, 5, 1);
        $endDate = Carbon::create(2025, 7, 31);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        DB::transaction(function () use ($startDate, $endDate, $ketuaIds, $anggotaIds) {
            
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                
                $jumlahTimPerHari = rand(2, 5);

                for ($i = 0; $i < $jumlahTimPerHari; $i++) {
                    $randomKetuaId = $ketuaIds->random();
                    $randomDivisi = ['sales', 'teknisi'][array_rand(['sales', 'teknisi'])];
                    $jumlahAnggota = rand(2, 4);
                    
                    if ($anggotaIds->count() < $jumlahAnggota) {
                        $jumlahAnggota = $anggotaIds->count();
                    }

                    $randomAnggotaIds = $anggotaIds->random($jumlahAnggota)->all();

                    // === PERUBAHAN DI SINI ===
                    // Buat timestamp acak antara jam 08:00 - 16:59 pada tanggal yang sedang di-loop
                    $jamAcak = rand(8, 16);
                    $menitAcak = rand(0, 59);
                    $detikAcak = rand(0, 59);
                    $timestampPalsu = $date->copy()->setTime($jamAcak, $menitAcak, $detikAcak);

                    // Buat record tim dengan timestamp palsu
                    $tim = SettingTugas::create([
                        'ketua_id' => $randomKetuaId,
                        'divisi' => $randomDivisi,
                        'tanggal' => $date->toDateString(),
                        'deskripsi' => 'Tim ' . $randomDivisi . ' otomatis #' . ($i + 1),
                        'created_at' => $timestampPalsu,
                        'updated_at' => $timestampPalsu,
                    ]);

                    $tim->anggota()->sync($randomAnggotaIds);
                }
            }
        });

    }
}