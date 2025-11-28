<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PresensiStatusSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('presensi_status')->delete();

        $statuses = [
            ['status' => 'Tepat Waktu',       'kode' => 'TEPAT_WAKTU',       'kategori' => 'hadir'],
            ['status' => 'Terlambat',         'kode' => 'TERLAMBAT',         'kategori' => 'hadir'],
            ['status' => 'Sangat Terlambat',  'kode' => 'SANGAT_TERLAMBAT',  'kategori' => 'hadir'],
            ['status' => 'Terlalu Awal',      'kode' => 'TERLALU_AWAL',      'kategori' => 'hadir'],
            ['status' => 'Hadir (Hari Libur)','kode' => 'HADIR_LIBUR',       'kategori' => 'hadir'],
            ['status' => 'Sakit',             'kode' => 'SAKIT',             'kategori' => 'sakit'],
            ['status' => 'Izin',              'kode' => 'IZIN',              'kategori' => 'izin'],
            ['status' => 'Izin Terencana',    'kode' => 'IZIN_TERENCANA',    'kategori' => 'izin'],
            ['status' => 'Izin Mendesak',     'kode' => 'IZIN_MENDESAK',     'kategori' => 'izin'],
            ['status' => 'Alpa',              'kode' => 'ALPA',              'kategori' => 'alpa'],
        ];

        DB::table('presensi_status')->insert($statuses);
        $this->command->info('Tabel Presensi Status berhasil di-seed.');
    }
}