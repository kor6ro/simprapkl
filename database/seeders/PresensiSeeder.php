<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\PeriodePkl; // <-- 1. Import model PeriodePkl

class PresensiSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Memulai proses seeding data presensi (dinamis sesuai periode)...');

        // --- PERUBAHAN 2: Mengambil rentang tanggal dari Periode PKL ---
        $periode = PeriodePkl::first();
        if (!$periode) {
            $this->command->info('Tidak ada data Periode PKL, PresensiSeeder dilewati.');
            return;
        }

        $adminId = DB::table('user')->where('group_id', 1)->value('id');
        $jamKerja = DB::table('presensi_setting')->first();
        $statusMap = DB::table('presensi_status')->pluck('id', 'status');
        $userIds = DB::table('user')->where('group_id', 4)->pluck('id');

        if (!$jamKerja || !$adminId || $statusMap->isEmpty() || $userIds->isEmpty()) {
            $this->command->error('Data awal tidak lengkap. Seeding presensi dibatalkan.');
            return;
        }
        
        // --- PERUBAHAN 3: Atur tanggal mulai dan selesai secara dinamis ---
        $startDate = $periode->awal_periode;
        // Batasi tanggal akhir hingga hari ini
        $endDate = $periode->akhir_periode->isFuture() ? Carbon::now() : $periode->akhir_periode;

        $presensiToInsert = [];

        $totalOperations = $userIds->count() * $startDate->diffInDaysFiltered(fn(Carbon $date) => !$date->isWeekend(), $endDate);
        if ($totalOperations == 0) {
            $this->command->info('Tidak ada hari kerja dalam rentang periode PKL yang perlu di-seed. PresensiSeeder selesai.');
            return;
        }
        $progressBar = $this->command->getOutput()->createProgressBar($totalOperations);

        foreach ($userIds as $userId) {
            // ... (sisa logika looping tidak perlu diubah)
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                if ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }

                $kehadiranHarian = $this->getDailyAttendanceType();

                foreach (['pagi', 'sore'] as $sesi) {
                    $presensiToInsert[] = $this->createPresensiRecord(
                        $userId,
                        $currentDate,
                        $sesi,
                        $kehadiranHarian,
                        $jamKerja,
                        $statusMap,
                        $adminId
                    );
                }

                $progressBar->advance();
                $currentDate->addDay();
            }
        }
        
        DB::table('presensi')->delete();

        foreach (array_chunk($presensiToInsert, 500) as $chunk) {
            DB::table('presensi')->insert($chunk);
        }

        $progressBar->finish();
        $this->command->info("\nSeeding data presensi berhasil diselesaikan.");
    }

    // ... (sisa method lain tidak perlu diubah)
    private function getDailyAttendanceType(): array
    {
        $rand = rand(1, 100);
        if ($rand <= 80) return ['type' => 'Hadir'];
        if ($rand <= 85) return ['type' => 'Alpa', 'keterangan' => 'Tidak hadir tanpa keterangan.'];
        if ($rand <= 93) return ['type' => 'Sakit', 'keterangan' => $this->getRandomKeterangan('sakit')];
        
        $jenisIzin = rand(0, 1) ? 'Izin Mendesak' : 'Izin Terencana';
        return ['type' => $jenisIzin, 'keterangan' => $this->getRandomKeterangan('izin')];
    }
    
    private function createPresensiRecord($userId, $tanggal, $sesi, $kehadiran, $jamKerja, $statusMap, $adminId): array
    {
        $now = now();
        $record = [
            'user_id'          => $userId,
            'tanggal_presensi' => $tanggal->toDateString(),
            'sesi'             => $sesi,
            'jam_presensi'     => null,
            'status'           => null,
            'presensi_status_id' => null,
            'keterangan'       => null,
            'bukti_foto'       => null,
            'approval_status'  => null,
            'requested_status' => null,
            'approval_notes'   => null,
            'approved_by'      => null,
            'approved_at'      => null,
            'created_at'       => $tanggal->copy()->setTime(rand(6, 18), rand(0, 59)),
            'updated_at'       => $tanggal->copy()->setTime(rand(6, 18), rand(0, 59)),
        ];

        $statusName = '';

        if ($kehadiran['type'] === 'Hadir') {
            $mulai = Carbon::createFromTimeString($sesi === 'pagi' ? $jamKerja->pagi_mulai : $jamKerja->sore_mulai);
            $selesai = Carbon::createFromTimeString($sesi === 'pagi' ? $jamKerja->pagi_selesai : $jamKerja->sore_selesai);
            
            $jamPresensi = $mulai->copy()->subMinutes(10)->addSeconds(rand(0, $selesai->diffInSeconds($selesai) + (45 * 60)));
            $record['jam_presensi'] = $jamPresensi->toTimeString();

            $toleransi = (int)$jamKerja->toleransi_telat;
            if ($jamPresensi->lt($mulai)) $statusName = 'Terlalu Awal';
            elseif ($jamPresensi->lte($selesai)) $statusName = 'Tepat Waktu';
            elseif ($jamPresensi->lte($selesai->copy()->addMinutes($toleransi))) $statusName = 'Terlambat';
            else $statusName = 'Sangat Terlambat';
            
            $record['approval_status'] = 'approved';
            $record['approved_by'] = $adminId;
            $record['approved_at'] = $jamPresensi;
            $record['keterangan'] = "Presensi $sesi otomatis oleh sistem.";

        } else {
            $statusName = $kehadiran['type'];
            $record['keterangan'] = $kehadiran['keterangan'];
            
            if ($statusName !== 'Alpa') {
                $record['requested_status'] = $statusName;
                
                if ($tanggal->isBefore(Carbon::today())) {
                    $record['approval_status'] = 'approved';
                    $record['approved_by'] = $adminId;
                    $record['approved_at'] = $tanggal->copy()->setTime(rand(17, 18), rand(0, 59));
                    $record['approval_notes'] = 'Disetujui sistem secara otomatis untuk data lama.';
                } else {
                    $record['approval_status'] = 'pending';
                    $statusName = 'Alpa';
                }
            }
        }
        
        $record['status'] = $statusName;
        $record['presensi_status_id'] = $statusMap[$statusName] ?? $statusMap['Alpa'];

        return $record;
    }

    private function getRandomKeterangan(string $jenis): string
    {
        $keterangan = [
            'izin' => ['Ada acara keluarga mendadak', 'Mengurus administrasi di kelurahan', 'Menghadiri pernikahan saudara'],
            'sakit' => ['Demam tinggi dan flu, disarankan istirahat oleh dokter', 'Sakit kepala berat dan tidak bisa fokus', 'Mengalami gangguan pencernaan']
        ];
        return $keterangan[$jenis][array_rand($keterangan[$jenis])];
    }
}