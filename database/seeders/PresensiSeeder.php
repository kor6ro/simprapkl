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

        $toleransiTelat = $jamKerja->toleransi_telat; // menit

        // Ambil ID status dari tabel presensi_status
        $statusMap = DB::table('presensi_status')->pluck('id', 'kode');
        // contoh: ['TEPAT' => 1, 'TELAT' => 2, 'IZIN' => 3, 'SAKIT' => 4, 'ALPA' => 5]

        // Ambil semua siswa PKL
        $userIds = DB::table('user')
            ->where('group_id', 4)
            ->pluck('id');

        // Range tanggal
        $startDate = Carbon::create(2025, 8, 4);
        $endDate   = Carbon::create(2025, 8, 9);

        foreach ($userIds as $userId) {
            $date = $startDate->copy();

            while ($date->lte($endDate)) {
                // Sesi pagi
                $this->insertPresensi(
                    $userId,
                    $date,
                    'pagi',
                    $pagiMulai,
                    $pagiSelesai,
                    $toleransiTelat,
                    $statusMap
                );

                // Sesi sore
                $this->insertPresensi(
                    $userId,
                    $date,
                    'sore',
                    $soreMulai,
                    $soreSelesai,
                    $toleransiTelat,
                    $statusMap
                );

                $date->addDay();
            }
        }
    }

    private function insertPresensi($userId, $tanggal, $sesi, $mulai, $selesai, $toleransiTelat, $statusMap)
    {
        // Status acak dengan bobot lebih besar untuk hadir
        $kehadiranType = collect(['HADIR', 'HADIR', 'HADIR', 'ALPA', 'IZIN', 'SAKIT'])->random();

        $jamPresensi = null;
        $keterangan = null;
        $statusId = null;

        if ($kehadiranType === 'HADIR') {
            $jamRandom = $mulai->copy()->addMinutes(rand(0, $selesai->diffInMinutes($mulai)));
            $jamPresensi = $jamRandom->format('H:i:s');

            $batasTelat = $mulai->copy()->addMinutes($toleransiTelat);

            if ($jamRandom->lte($batasTelat)) {
                $statusId = $statusMap['TEPAT'];
                $keterangan = 'Tepat waktu';
            } else {
                $statusId = $statusMap['TELAT'];
                $keterangan = 'Datang telat';
            }
        } elseif ($kehadiranType === 'ALPA') {
            $statusId = $statusMap['ALPA'];
            $keterangan = 'Tidak hadir';
        } elseif ($kehadiranType === 'IZIN') {
            $statusId = $statusMap['IZIN'];
            $keterangan = 'Izin karena urusan pribadi';
        } elseif ($kehadiranType === 'SAKIT') {
            $statusId = $statusMap['SAKIT'];
            $keterangan = 'Sakit';
        }

        DB::table('presensi')->insert([
            'user_id' => $userId,
            'tanggal_presensi' => $tanggal->format('Y-m-d'),
            'bukti_foto' => 'default.jpg',
            'sesi' => $sesi,
            'jam_presensi' => $jamPresensi,
            'keterangan' => $keterangan,
            'status' => null, // kolom lama, opsional
            'presensi_status_id' => $statusId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
