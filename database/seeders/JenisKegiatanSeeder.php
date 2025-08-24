<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JenisKegiatan;

class JenisKegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JenisKegiatan::create([
            'nama_kegiatan' => 'PSB',
            'deskripsi' => 'Pemasangan Sambungan Baru untuk klien.'
        ]);

        JenisKegiatan::create([
            'nama_kegiatan' => 'DEACT',
            'deskripsi' => 'Deaktivasi layanan klien yang berhenti berlangganan.'
        ]);

        JenisKegiatan::create([
            'nama_kegiatan' => 'Survey Lokasi',
            'deskripsi' => 'Melakukan survey ke lokasi calon klien baru.'
        ]);

        JenisKegiatan::create([
            'nama_kegiatan' => 'Maintenance',
            'deskripsi' => 'Perawatan dan perbaikan rutin pada infrastruktur jaringan.'
        ]);

        // DATA BARU DITAMBAHKAN DI SINI
        JenisKegiatan::create([
            'nama_kegiatan' => 'Collect Data',
            'deskripsi' => 'Mengumpulkan data pelanggan atau data lapangan lainnya.'
        ]);

        JenisKegiatan::create([
            'nama_kegiatan' => 'Canvassing',
            'deskripsi' => 'Melakukan penawaran produk atau layanan secara langsung ke calon pelanggan.'
        ]);
    }
}