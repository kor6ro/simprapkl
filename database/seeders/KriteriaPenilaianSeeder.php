<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KriteriaPenilaian; // <-- Tambahkan ini

class KriteriaPenilaianSeeder extends Seeder
{
    public function run(): void
    {
        $kriteria = [
            ['nama_variabel' => 'Performance (Unjuk Kerja)', 'kode_variabel' => 'performance'],
            ['nama_variabel' => 'Attitude/Sikap (Sopan, Santun, Kepatuhan)', 'kode_variabel' => 'attitude'],
            ['nama_variabel' => 'Kerjasama dalam Tim', 'kode_variabel' => 'kerjasama'],
            ['nama_variabel' => 'Kedisiplinan', 'kode_variabel' => 'kedisiplinan'],
            ['nama_variabel' => 'Kemampuan dalam Berkomunikasi', 'kode_variabel' => 'komunikasi'],
            ['nama_variabel' => 'Pelaksanaan dan Tanggung Jawab atas pekerjaan yang dilakukan', 'kode_variabel' => 'tanggung_jawab'],
            ['nama_variabel' => 'Pengetahuan dan Kemampuan teknis di bidangnya', 'kode_variabel' => 'kemampuan_teknis'],
        ];

        // Looping untuk memasukkan data ke database
        foreach ($kriteria as $item) {
            KriteriaPenilaian::create($item);
        }
    }
}
