<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ColectData;
use App\Models\PeriodePkl; // <-- 1. Import model PeriodePkl
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Faker\Factory as Faker;

class ColectDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- PERUBAHAN 2: Mengambil rentang tanggal dari Periode PKL ---
        $periode = PeriodePkl::first();
        if (!$periode) {
            $this->command->info('Tidak ada data Periode PKL, ColectDataSeeder dilewati.');
            return;
        }

        $faker = Faker::create('id_ID');
        $students = User::where('group_id', 4)->get();

        if ($students->isEmpty()) {
            $this->command->info('Tidak ada user siswa, ColectDataSeeder dilewati.');
            return;
        }

        // --- PERUBAHAN 3: Atur tanggal mulai dan selesai secara dinamis ---
        $startDate = $periode->awal_periode;
        // Batasi tanggal akhir hingga hari ini
        $endDate = $periode->akhir_periode->isFuture() ? Carbon::now() : $periode->akhir_periode;
        
        // Hapus data lama
        ColectData::query()->delete();

        $providers = ['Indihome', 'Iconnet', 'Biznet', 'Telkom', 'MyRepublic', 'First Media'];
        $kelebihan = ['Jaringan stabil', 'Harga terjangkau', 'Upload dan download simetris', 'Layanan pelanggan cepat', 'Tidak ada FUP'];
        $kekurangan = ['Sering RTO malam hari', 'Mahal', 'Jangkauan terbatas', 'Kecepatan tidak sesuai paket', 'Tagihan sering naik'];

        // Buat 50 data collect random dalam rentang periode
        for ($i = 0; $i < 50; $i++) {
            $randomStudent = $students->random();

            ColectData::create([
                'user_id'           => $randomStudent->id,
                'tanggal'           => $faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d'),
                'nama_cus'          => $faker->name(),
                'no_telp'           => $faker->phoneNumber(),
                'alamat_cus'        => $faker->address(),
                'provider_sekarang' => $faker->randomElement($providers),
                'kelebihan'         => $faker->randomElement($kelebihan),
                'kekurangan'        => $faker->randomElement($kekurangan),
                'serlok'            => 'https://maps.google.com/?q=' . $faker->latitude(-8.10, -8.20) . ',' . $faker->longitude(111.10, 111.20),
            ]);
        }
        $this->command->info('ColectDataSeeder selesai dijalankan sesuai periode PKL.');
    }
}