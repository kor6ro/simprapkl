<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tim;
use App\Models\User;
use App\Models\Divisi;
use App\Models\PeriodePkl;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Memulai TimSeeder...');
        $periode = PeriodePkl::first();
        if (!$periode) {
            $this->command->info('Periode PKL tidak ditemukan, TimSeeder dilewati.');
            return;
        }

        $divisi = Divisi::all();
        $ketuaIds = User::where('group_id', 5)->pluck('id');
        $anggotaIds = User::where('group_id', 4)->pluck('id');

        if ($divisi->isEmpty() || $ketuaIds->count() < 2 || $anggotaIds->isEmpty()) {
            $this->command->error('Data master (Divisi, Karyawan, Siswa) tidak lengkap. Butuh minimal 2 karyawan. TimSeeder dibatalkan.');
            return;
        }

        DB::transaction(function () use ($periode, $ketuaIds, $anggotaIds, $divisi) {
            Tim::query()->delete();
            DB::table('tim_anggota')->delete();
            DB::table('tim_ketua')->delete(); // [PERBAIKAN] Hapus data di tabel pivot ketua juga

            // --- 1. MEMBUAT DATA HISTORIS ---
            $startDate = $periode->awal_periode;
            $endDate = $periode->akhir_periode->isFuture() ? Carbon::now()->subDay() : $periode->akhir_periode;
            
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                if ($date->isWeekend()) continue;

                $jumlahTimPerHari = rand(1, 2);
                for ($i = 0; $i < $jumlahTimPerHari; $i++) {
                    $ketua = $ketuaIds->random();
                    $tim = Tim::create([
                        // [PERBAIKAN] Hapus 'ketua_id' dari sini
                        'divisi_id'  => $divisi->random()->id,
                        'tanggal'    => $date->toDateString(),
                        'status_approval' => 'tugas_selesai',
                        'approver_id'     => $ketua,
                        'feedback'        => 'Pekerjaan telah selesai dan disetujui.',
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                    
                    // [PERBAIKAN] Tambahkan ketua melalui relasi attach()
                    $tim->ketua()->attach($ketua);
                    $tim->anggota()->sync($anggotaIds->random(rand(1, $anggotaIds->count())));
                }
            }
            $this->command->info('Data tim historis berhasil dibuat.');

            // --- 2. MEMBUAT TUGAS UNTUK HARI INI ---
            if (!$periode->akhir_periode->isPast()) {
                $this->command->info('Membuat tugas untuk hari ini...');
                
                // Tim 1 (1 Ketua)
                $timHariIni1 = Tim::create([
                    'divisi_id'  => $divisi->random()->id,
                    'tanggal'    => Carbon::today()->toDateString(),
                    'status_approval' => 'belum_selesai',
                ]);
                $timHariIni1->ketua()->attach($ketuaIds->random());
                $timHariIni1->anggota()->sync($anggotaIds->random(rand(1, 2)));
                
                // [TAMBAHAN] Membuat 1 tim dengan DUA KETUA untuk testing
                $timMultiKetua = Tim::create([
                    'divisi_id'  => $divisi->random()->id,
                    'tanggal'    => Carbon::today()->toDateString(),
                    'status_approval' => 'belum_selesai',
                ]);
                $ketuaMulti = $ketuaIds->random(2); // Ambil 2 ID ketua secara acak
                $timMultiKetua->ketua()->attach($ketuaMulti);
                $timMultiKetua->anggota()->sync($anggotaIds->random(rand(1, 2)));

                $this->command->info('Tugas hari ini (termasuk 1 tim multi-ketua) berhasil dibuat.');
            }
        });

        $this->command->info('TimSeeder selesai.');
    }
}