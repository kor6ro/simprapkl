<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PresensiStatus;

class PresensiStatusSeeder extends Seeder
{
    public function run()
    {
        PresensiStatus::insert([
            ['status' => 'hadir'],              // Pagi tepat + sore hadir
            ['status' => 'telat'],              // Pagi telat + sore hadir
            ['status' => 'izin'],               // Diisi otomatis
            ['status' => 'sakit'],              // Diisi otomatis
            ['status' => 'bolos'],              // Pagi hadir, sore tidak
            ['status' => 'tanpa keterangan'],   // Tidak hadir dua-duanya
            ['status' => 'hadir pagi saja'],    // Presensi hanya pagi
            ['status' => 'hadir sore saja'],    // Presensi hanya sore
        ]);
    }
}
