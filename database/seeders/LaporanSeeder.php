<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tim;
use App\Models\Laporan;
use App\Models\JenisKegiatan;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class LaporanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Memulai LaporanSeeder (logika disesuaikan dengan create.blade.php)...');
        // Kosongkan tabel laporan sebelum diisi
        Laporan::query()->delete();

        $faker = Faker::create('id_ID');
        
        // Ambil semua tim yang statusnya sudah selesai
        $selesaiTeams = Tim::where('status_approval', 'tugas_selesai')->with('anggota')->get();
        
        // Ambil semua jenis kegiatan yang ada
        $jenisKegiatanIds = JenisKegiatan::pluck('id');

        if ($selesaiTeams->isEmpty() || $jenisKegiatanIds->isEmpty()) {
            $this->command->error('Tidak ada Tim yang selesai atau tidak ada Jenis Kegiatan. LaporanSeeder dilewati.');
            return;
        }

        $laporanToInsert = [];

        // Loop melalui setiap tim yang sudah selesai
        foreach ($selesaiTeams as $tim) {
            // Lewati jika tim tidak punya anggota
            if ($tim->anggota->isEmpty()) continue;

            // Ambil satu anggota acak dari tim tersebut untuk membuat laporan
            $pelapor = $tim->anggota->random();
            
            $waktuLaporan = $tim->created_at->addHours(rand(1, 5));
            $laporanToInsert[] = [
                'tim_id' => $tim->id,
                'user_id' => $pelapor->id,
                'jenis_kegiatan_id' => $jenisKegiatanIds->random(), // Pilih jenis kegiatan apa saja secara acak
                'deskripsi_kegiatan' => $faker->realText(150),
                'bukti_foto' => null,
                'created_at' => $waktuLaporan,
                'updated_at' => $waktuLaporan,
            ];
        }
        
        // Masukkan semua data laporan sekaligus ke database
        DB::table('laporan')->insert($laporanToInsert);

        $this->command->info('SUKSES! Seeding Laporan selesai. ' . count($laporanToInsert) . ' baris data berhasil dimasukkan.');
    }
}