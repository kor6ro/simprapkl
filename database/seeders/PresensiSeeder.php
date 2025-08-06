<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PresensiSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        // Ambil ID user yang ingin diisi presensinya
        $userIds = DB::table('user')
            ->where('group_id', 4) // Siswa PKL
            ->pluck('id');

        foreach ($userIds as $userId) {
            DB::table('presensi')->insert([
                [
                    'user_id' => $userId,
                    'tanggal_presensi' => $now->format('Y-m-d'),
                    'bukti_foto' => 'default.jpg',
                    'sesi' => 'pagi',
                    'jam_presensi' => '07:15:00',
                    'keterangan' => 'Tepat waktu',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'user_id' => $userId,
                    'tanggal_presensi' => $now->subDay()->format('Y-m-d'),
                    'bukti_foto' => 'default.jpg',
                    'sesi' => 'pagi',
                    'jam_presensi' => '08:10:00',
                    'keterangan' => 'Datang telat',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }
}
