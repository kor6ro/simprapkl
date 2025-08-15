<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PresensiSeeder extends Seeder
{
    public function run()
    {
        // Ambil setting jam kerja dari tabel presensi_setting
        $jamKerja = DB::table('presensi_setting')->first();

        $pagiMulai   = Carbon::createFromFormat('H:i:s', $jamKerja->pagi_mulai);
        $pagiSelesai = Carbon::createFromFormat('H:i:s', $jamKerja->pagi_selesai);
        $soreMulai   = Carbon::createFromFormat('H:i:s', $jamKerja->sore_mulai);
        $soreSelesai = Carbon::createFromFormat('H:i:s', $jamKerja->sore_selesai);

        $toleransiTelat = $jamKerja->toleransi_telat;

        // Ambil ID status dari tabel presensi_status
        $statusData = DB::table('presensi_status')->get()->keyBy('kode');

        // Ambil semua siswa PKL
        $userIds = DB::table('user')
            ->where('group_id', 4)
            ->pluck('id');

        // Range tanggal: mulai 1 Juli 2025 sampai hari ini
        $startDate = Carbon::create(2025, 7, 1);
        $endDate   = now()->startOfDay();

        foreach ($userIds as $userId) {
            $date = $startDate->copy();

            while ($date->lte($endDate)) {
                // Skip Sabtu & Minggu
                if ($date->isSaturday() || $date->isSunday()) {
                    $date->addDay();
                    continue;
                }

                // Tentukan status kehadiran untuk hari ini
                $kehadiranHarian = $this->tentukanKehadiranHarian();

                if ($kehadiranHarian['type'] === 'HADIR') {
                    $this->insertPresensiHadir(
                        $userId,
                        $date,
                        'pagi',
                        $pagiMulai,
                        $pagiSelesai,
                        $toleransiTelat,
                        $statusData
                    );

                    $this->insertPresensiHadir(
                        $userId,
                        $date,
                        'sore',
                        $soreMulai,
                        $soreSelesai,
                        $toleransiTelat,
                        $statusData
                    );
                } else {
                    $this->insertPresensiTidakHadir(
                        $userId,
                        $date,
                        'pagi',
                        $kehadiranHarian,
                        $statusData
                    );

                    $this->insertPresensiTidakHadir(
                        $userId,
                        $date,
                        'sore',
                        $kehadiranHarian,
                        $statusData
                    );
                }

                $date->addDay();
            }
        }
    }

    private function tentukanKehadiranHarian()
    {
        $random = rand(1, 100);

        if ($random <= 65) {
            return ['type' => 'HADIR', 'keterangan' => 'Hadir'];
        } elseif ($random <= 75) {
            return ['type' => 'ALPA', 'keterangan' => 'Tidak hadir tanpa keterangan'];
        } elseif ($random <= 88) {
            return ['type' => 'IZIN', 'keterangan' => $this->getRandomKeteranganIzin()];
        } else {
            return ['type' => 'SAKIT', 'keterangan' => $this->getRandomKeteranganSakit()];
        }
    }

    private function insertPresensiHadir($userId, $tanggal, $sesi, $mulai, $selesai, $toleransiTelat, $statusData)
    {
        $jamMulaiVariasi = $mulai->copy()->subMinutes(rand(0, 20));
        $jamSelesaiVariasi = $selesai->copy()->addMinutes(rand(0, 45));

        $jamRandom = $jamMulaiVariasi->copy()->addMinutes(
            rand(0, $jamSelesaiVariasi->diffInMinutes($jamMulaiVariasi))
        );

        $jamPresensi = $jamRandom->format('H:i:s');
        $batasTepat = $mulai->copy();
        $batasToleransi = $mulai->copy()->addMinutes($toleransiTelat);

        if ($jamRandom->lt($batasTepat)) {
            $statusId = $statusData['TEPAT']->id;
            $status = $statusData['TEPAT']->status;
            $keterangan = $sesi === 'pagi' ? 'Datang pagi lebih awal' : 'Datang sore lebih awal';
        } elseif ($jamRandom->lte($batasToleransi)) {
            $statusId = $statusData['TEPAT']->id;
            $status = $statusData['TEPAT']->status;
            $keterangan = $sesi === 'pagi' ? 'Datang pagi tepat waktu' : 'Datang sore tepat waktu';
        } else {
            $statusId = $statusData['TELAT']->id;
            $status = $statusData['TELAT']->status;
            $telat = $jamRandom->diffInMinutes($batasToleransi);
            $keterangan = $sesi === 'pagi'
                ? "Datang pagi terlambat {$telat} menit"
                : "Datang sore terlambat {$telat} menit";
        }

        DB::table('presensi')->insert([
            'user_id' => $userId,
            'tanggal_presensi' => $tanggal->format('Y-m-d'),
            'bukti_foto' => $this->getRandomBuktiPhoto(),
            'sesi' => $sesi,
            'jam_presensi' => $jamPresensi,
            'keterangan' => $keterangan,
            'status' => $status,
            'presensi_status_id' => $statusId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertPresensiTidakHadir($userId, $tanggal, $sesi, $kehadiranHarian, $statusData)
    {
        $type = $kehadiranHarian['type'];
        $keteranganBase = $kehadiranHarian['keterangan'];

        $statusId = $statusData[$type]->id;
        $status = $statusData[$type]->status;

        $keterangan = $sesi === 'pagi'
            ? $keteranganBase . ' (sesi pagi)'
            : $keteranganBase . ' (sesi sore)';

        DB::table('presensi')->insert([
            'user_id' => $userId,
            'tanggal_presensi' => $tanggal->format('Y-m-d'),
            'bukti_foto' => $type === 'IZIN' || $type === 'SAKIT' ? $this->getRandomBuktiPhoto() : 'default.jpg',
            'sesi' => $sesi,
            'jam_presensi' => null,
            'keterangan' => $keterangan,
            'status' => $status,
            'presensi_status_id' => $statusId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getRandomKeteranganIzin()
    {
        $keterangan = [
            'Izin keperluan keluarga',
            'Izin urusan pribadi',
            'Izin mengurus dokumen penting',
            'Izin menghadiri acara keluarga',
            'Izin ke dokter untuk kontrol rutin',
            'Izin menghadiri undangan resmi',
            'Izin karena transportasi bermasalah',
            'Izin mengikuti lomba sekolah',
            'Izin menghadiri seminar pendidikan',
        ];
        return $keterangan[array_rand($keterangan)];
    }

    private function getRandomKeteranganSakit()
    {
        $keterangan = [
            'Sakit demam tinggi',
            'Sakit flu dan batuk',
            'Sakit kepala berat',
            'Sakit perut mendadak',
            'Sakit gigi parah',
            'Tidak enak badan',
            'Sakit maag kambuh',
            'Sakit tenggorokan',
            'Sakit dan perlu istirahat total'
        ];
        return $keterangan[array_rand($keterangan)];
    }

    private function getRandomBuktiPhoto()
    {
        $photos = [
            'default.jpg',
            'bukti_' . rand(1000, 9999) . '.jpg',
            'photo_' . date('Ymd') . '_' . rand(100, 999) . '.jpg',
            'presensi_' . rand(10, 99) . '.jpg'
        ];
        return $photos[array_rand($photos)];
    }
}
