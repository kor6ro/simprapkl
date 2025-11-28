<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Tim;
use App\Models\Laporan;
use App\Models\JenisKegiatan;
use App\Models\Presensi;
use Faker\Factory as Faker;

class CostumPeriodSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Memulai Custom Period Seeder...');

        // =================================================================
        // 1. PENGATURAN AWAL & PENGAMBILAN DATA MASTER
        // =================================================================
        $faker = Faker::create('id_ID');

        // Tentukan rentang tanggal: dari Senin minggu pertama bulan ini s/d hari ini
        $startDate = Carbon::now()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endDate = Carbon::now();

        $this->command->info("Rentang data yang akan dibuat: {$startDate->isoFormat('D MMMM Y')} s/d {$endDate->isoFormat('D MMMM Y')}");

        // Ambil data master yang diperlukan
        $jamKerja = DB::table('presensi_setting')->first();
        $statusMap = DB::table('presensi_status')->pluck('id', 'status');
        $allSiswaIds = User::where('group_id', 4)->pluck('id');
        $ketuaIds = User::where('group_id', 5)->pluck('id');
        $divisi = Divisi::all();
        $jenisKegiatanIds = JenisKegiatan::pluck('id');

        // Validasi data master
        if ($allSiswaIds->count() < 3 || $ketuaIds->isEmpty() || $divisi->isEmpty() || $jenisKegiatanIds->isEmpty() || !$jamKerja) {
            $this->command->error('Data master tidak lengkap (butuh min. 3 siswa, karyawan, divisi, jenis kegiatan, dan setting presensi). Seeder dibatalkan.');
            return;
        }

        // Pilih 3 siswa khusus untuk masuk di hari Sabtu
        $specialSiswaIds = $allSiswaIds->random(3);
        $this->command->info('3 siswa khusus untuk hari Sabtu telah dipilih.');


        // =================================================================
        // 2. PROSES PENGHAPUSAN DATA LAMA & PEMBUATAN DATA BARU
        // =================================================================
        DB::transaction(function () use ($startDate, $endDate, $faker, $jamKerja, $statusMap, $allSiswaIds, $ketuaIds, $divisi, $jenisKegiatanIds, $specialSiswaIds) {
            
            // Hapus data lama dalam rentang tanggal yang sama agar tidak duplikat
            $this->command->warn('Menghapus data lama pada rentang tanggal yang relevan...');
            $timIdsToDelete = Tim::whereBetween('tanggal', [$startDate, $endDate])->pluck('id');
            if ($timIdsToDelete->isNotEmpty()) {
                Laporan::whereIn('tim_id', $timIdsToDelete)->delete();
                DB::table('tim_anggota')->whereIn('tim_id', $timIdsToDelete)->delete();
                Tim::whereIn('id', $timIdsToDelete)->delete();
            }
            Presensi::whereBetween('presensi_at', [$startDate, $endDate])->delete();
            
            $this->command->info('Memulai perulangan untuk setiap hari...');
            $progressBar = $this->command->getOutput()->createProgressBar($startDate->diffInDays($endDate) + 1);

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                
                // --- A. LOGIKA UNTUK HARI SABTU (HANYA 3 SISWA) ---
                if ($date->isSaturday()) {
                    // Buat satu tim khusus untuk 3 siswa ini
                    $timSabtu = Tim::create([
                        'ketua_id'   => $ketuaIds->random(),
                        'divisi_id'  => $divisi->random()->id,
                        'tanggal'    => $date->toDateString(),
                        'status_approval' => 'tugas_selesai',
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                    $timSabtu->anggota()->sync($specialSiswaIds);

                    // Buat presensi & laporan untuk setiap siswa khusus
                    foreach ($specialSiswaIds as $siswaId) {
                        // Presensi Pagi & Sore (dibuat selalu hadir)
                        foreach (['pagi', 'sore'] as $sesi) {
                            $jamPresensi = Carbon::parse($date->toDateString() . ' ' . ($sesi == 'pagi' ? '08:00:00' : '13:00:00'))->addMinutes(rand(-5, 15));
                            Presensi::create([
                                'user_id' => $siswaId, 'presensi_at' => $jamPresensi, 'sesi' => $sesi,
                                'status' => 'Tepat Waktu', 'presensi_status_id' => $statusMap['Tepat Waktu'],
                                'approval_status' => 'approved', 'created_at' => $date, 'updated_at' => $date,
                            ]);
                        }
                        // Laporan
                        Laporan::create([
                            'tim_id' => $timSabtu->id, 'user_id' => $siswaId,
                            'jenis_kegiatan_id' => $jenisKegiatanIds->random(),
                            'deskripsi_kegiatan' => 'Laporan khusus pengerjaan tugas di hari Sabtu: ' . $faker->sentence(10),
                            'created_at' => $date, 'updated_at' => $date,
                        ]);
                    }
                }

                // --- B. LOGIKA UNTUK HARI KERJA NORMAL (SENIN - JUMAT) ---
                if ($date->isWeekday() && !$date->isSaturday()) {
                    // Buat 1-2 tim acak untuk semua siswa
                    $jumlahTim = rand(1, 2);
                    for ($i = 0; $i < $jumlahTim; $i++) {
                        $timNormal = Tim::create([
                            'ketua_id'   => $ketuaIds->random(),
                            'divisi_id'  => $divisi->random()->id,
                            'tanggal'    => $date->toDateString(),
                            'status_approval' => 'tugas_selesai',
                            'created_at' => $date, 'updated_at' => $date,
                        ]);
                        // Acak anggota untuk tim ini
                        $anggotaTim = $allSiswaIds->random(rand(3, $allSiswaIds->count()));
                        $timNormal->anggota()->sync($anggotaTim);

                        // Buat laporan dari salah satu anggota tim
                        Laporan::create([
                            'tim_id' => $timNormal->id, 'user_id' => $anggotaTim->random(),
                            'jenis_kegiatan_id' => $jenisKegiatanIds->random(),
                            'deskripsi_kegiatan' => $faker->realText(150),
                            'created_at' => $date, 'updated_at' => $date,
                        ]);
                    }

                    // Buat presensi acak (hadir/sakit/izin) untuk semua siswa
                    foreach ($allSiswaIds as $siswaId) {
                        $kehadiranHarian = $this->getDailyAttendanceType();
                        foreach (['pagi', 'sore'] as $sesi) {
                            $record = $this->createPresensiRecord($siswaId, $date, $sesi, $kehadiranHarian, $jamKerja, $statusMap);
                            Presensi::create($record);
                        }
                    }
                }
                
                // Hari Minggu dilewati (tidak melakukan apa-apa)
                $progressBar->advance();
            }

            $progressBar->finish();
        });

        $this->command->info("\nCustom Period Seeder berhasil dijalankan.");
    }

    // Helper methods dari PresensiSeeder (dicopy ke sini agar file mandiri)
    private function getDailyAttendanceType(): array
    {
        $rand = rand(1, 100);
        if ($rand <= 85) return ['type' => 'Hadir']; // Persentase hadir lebih tinggi
        if ($rand <= 90) return ['type' => 'Alpa'];
        if ($rand <= 95) return ['type' => 'Sakit'];
        return ['type' => rand(0, 1) ? 'Izin Mendesak' : 'Izin Terencana'];
    }

    private function createPresensiRecord($userId, $tanggal, $sesi, $kehadiran, $jamKerja, $statusMap): array
    {
        $baseTime = $tanggal->copy()->startOfDay();
        $record = [
            'user_id' => $userId, 'sesi' => $sesi, 'keterangan' => null, 'bukti_foto' => null,
            'approval_status' => null, 'requested_status' => null, 'approval_notes' => null,
            'approved_by' => null, 'approved_at' => null,
            'created_at' => $baseTime, 'updated_at' => $baseTime,
        ];

        $statusName = '';
        if ($kehadiran['type'] === 'Hadir') {
            $mulai = Carbon::parse($sesi === 'pagi' ? $jamKerja->pagi_mulai : $jamKerja->sore_mulai);
            $presensiTime = $baseTime->copy()->setTimeFrom($mulai)->addMinutes(rand(-10, 45));
            
            $statusName = 'Tepat Waktu';
            if ($presensiTime > $mulai->addMinutes($jamKerja->toleransi_telat)) $statusName = 'Terlambat';

            $record['presensi_at'] = $presensiTime;
            $record['approval_status'] = 'approved';
        } else {
            $statusName = $kehadiran['type'];
            $record['keterangan'] = 'Keterangan ' . strtolower($statusName) . ' oleh seeder.';
            $record['presensi_at'] = $baseTime;
            if ($statusName !== 'Alpa') {
                $record['requested_status'] = $statusName;
                $record['approval_status'] = 'approved'; // Langsung approve untuk data historis
            }
        }
        
        $record['status'] = $statusName;
        $record['presensi_status_id'] = $statusMap[$statusName] ?? $statusMap['Alpa'];
        return $record;
    }
}