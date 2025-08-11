<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PresensiStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Truncate table
        DB::table('presensi_status')->truncate();

        // 2. Insert default data
        DB::table('presensi_status')->insert([
            ['kode' => 'TEPAT', 'status' => 'Tepat Waktu', 'color' => 'success'],
            ['kode' => 'TELAT', 'status' => 'Terlambat', 'color' => 'warning'],
            ['kode' => 'IZIN', 'status' => 'Izin', 'color' => 'info'],
            ['kode' => 'SAKIT', 'status' => 'Sakit', 'color' => 'secondary'],
            ['kode' => 'ALPA', 'status' => 'Alpa', 'color' => 'danger'],
        ]);
    }
}
