<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JenisKegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('jenis_kegiatan')->truncate();

        $now = Carbon::now();

        $kegiatan = [
            [
                'nama_kegiatan' => 'PSB',
                'deskripsi' => 'Pemasangan Sambungan Baru untuk klien.',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'nama_kegiatan' => 'DEACT',
                'deskripsi' => 'Deaktivasi layanan klien yang berhenti berlangganan.',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'nama_kegiatan' => 'Survey Lokasi',
                'deskripsi' => 'Melakukan survey ke lokasi calon klien baru.',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'nama_kegiatan' => 'Maintenance',
                'deskripsi' => 'Perawatan dan perbaikan rutin pada infrastruktur jaringan.',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'nama_kegiatan' => 'Collect Data',
                'deskripsi' => 'Mengumpulkan data pelanggan atau data lapangan lainnya.',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'nama_kegiatan' => 'Canvassing',
                'deskripsi' => 'Melakukan penawaran produk atau layanan secara langsung ke calon pelanggan.',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        DB::table('jenis_kegiatan')->insert($kegiatan);
    }
}
