<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PresensiJenis;

class JenisPresensiSeeder extends Seeder
{
    public function run()
    {
        PresensiJenis::insert([
            ['nama' => 'hadir', 'butuh_bukti' => false, 'otomatis' => false, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'telat', 'butuh_bukti' => false, 'otomatis' => true,  'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'izin',  'butuh_bukti' => true,  'otomatis' => false, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'sakit', 'butuh_bukti' => true,  'otomatis' => false, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'bolos', 'butuh_bukti' => false, 'otomatis' => true,  'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
