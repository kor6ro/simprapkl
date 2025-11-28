<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PeriodePkl;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PeriodePklSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PeriodePkl::query()->delete();
        User::whereIn('group_id', [3, 4])->delete();

        $periode = PeriodePkl::create([
            'awal_periode'  => Carbon::create(2025, 7, 7),
            'akhir_periode' => Carbon::create(2025, 12, 31),
        ]);

        $this->command->info('Periode PKL berhasil dibuat.');

        // Data Pembimbing dan Siswa untuk sekolah pertama (contoh)
        $pembimbing1 = User::create([
            "name" => "Hendri Winarto",
            "username" => "Hendri",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 1,
            "program_keahlian_id" => 1,
            "group_id" => 3,
            "alamat" => "Pacitan",
        ]);

        $siswa1 = User::create([
            "name" => "Adya Rizqy Riantama",
            "username" => "rizqy",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 1,
            "program_keahlian_id" => 1,
            "group_id" => 4,
            "alamat" => "Pacitan",
        ]);

        $siswa2 = User::create([
            "name" => "Ahmad Nurrosyid",
            "username" => "ahmad",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 1,
            "program_keahlian_id" => 1,
            "group_id" => 4,
            "alamat" => "Pacitan",
        ]);
        
        // Data Pembimbing dan Siswa untuk sekolah kedua (TKJ)
        $pembimbing2 = User::create([
            "name" => "Pembimbing TKJ",
            "username" => "pembimbing.tkj",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 1,
            "program_keahlian_id" => 2,
            "group_id" => 3,
            "alamat" => "Pacitan",
        ]);

        $siswa3 = User::create([
            "name" => "Antyka Sekar Kinasih",
            "username" => "antyka",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 1,
            "program_keahlian_id" => 2,
            "group_id" => 4,
            "alamat" => "Pacitan",
        ]);

        $siswa4 = User::create([
            "name" => "Daffa Nazril Putra Cipto",
            "username" => "daffa",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 1,
            "program_keahlian_id" => 2,
            "group_id" => 4,
            "alamat" => "Pacitan",
        ]);

        $siswa5 = User::create([
            "name" => "Kelvin Yandika Pratama",
            "username" => "kelvin",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 1,
            "program_keahlian_id" => 2,
            "group_id" => 4,
            "alamat" => "Pacitan",
        ]);

        // Data Pembimbing dan Siswa untuk sekolah ketiga (jurusan TKJ, sekolah kosong)
        $pembimbing3 = User::create([
            "name" => "Pembimbing TKJ 2",
            "username" => "pembimbing.tkj2",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 6,
            "program_keahlian_id" => 2,
            "group_id" => 3,
            "alamat" => "Pacitan",
        ]);

        $siswa7 = User::create([
            "name" => "Rasya Putra Yassin",
            "username" => "rasya.yassin",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 6,
            "program_keahlian_id" => 2,
            "group_id" => 4,
            "alamat" => "Pacitan",
        ]);

        $siswa8 = User::create([
            "name" => "Zaky Jihad Akbar",
            "username" => "zaky",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 6,
            "program_keahlian_id" => 2,
            "group_id" => 4,
            "alamat" => "Pacitan",
        ]);

        $siswa9 = User::create([
            "name" => "Raditya Bimo Sasongko",
            "username" => "raditya",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 6,
            "program_keahlian_id" => 2,
            "group_id" => 4,
            "alamat" => "Pacitan",
        ]);

        // Data Pembimbing dan Siswa untuk sekolah keempat (jurusan TKJ, sekolah beda)
        $pembimbing4 = User::create([
            "name" => "Pembimbing TKJ 3",
            "username" => "pembimbing.tkj3",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 5,
            "program_keahlian_id" => 2,
            "group_id" => 3,
            "alamat" => "Pacitan",
        ]);

        $siswa10 = User::create([
            "name" => "Andhika Gusti Pratama",
            "username" => "andhika",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 5,
            "program_keahlian_id" => 2,
            "group_id" => 4,
            "alamat" => "Pacitan",
        ]);

        $siswa11 = User::create([
            "name" => "Muhammad Saiful",
            "username" => "saiful",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 5,
            "program_keahlian_id" => 2,
            "group_id" => 4,
            "alamat" => "Pacitan",
        ]);
        
        // Data Pembimbing dan Siswa untuk sekolah kelima (jurusan TKJ, sekolah kosong)
        $pembimbing5 = User::create([
            "name" => "Pembimbing TKJ 4",
            "username" => "pembimbing.tkj4",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 4,
            "program_keahlian_id" => 2,
            "group_id" => 3,
            "alamat" => "Pacitan",
        ]);

        $siswa12 = User::create([
            "name" => "Thariq Arief Setyono",
            "username" => "thariq",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 4,
            "program_keahlian_id" => 2,
            "group_id" => 4,
            "alamat" => "Pacitan",
        ]);

        $siswa13 = User::create([
            "name" => "Rasya Adi Pratama",
            "username" => "rasya.adi",
            "email" => "youremail@gmail.com",
            "password" => Hash::make("password"),
            "validasi" => "1",
            "sekolah_id" => 4,
            "program_keahlian_id" => 2,
            "group_id" => 4,
            "alamat" => "Pacitan",
        ]);

        $this->command->info('User Pembimbing & Siswa berhasil dibuat.');

        $periode->users()->attach([
            $pembimbing1->id,
            $siswa1->id,
            $siswa2->id,
            $pembimbing2->id,
            $siswa3->id,
            $siswa4->id,
            $siswa5->id,
            $pembimbing3->id,
            $siswa7->id,
            $siswa8->id,
            $siswa9->id,
            $pembimbing4->id,
            $siswa10->id,
            $siswa11->id,
            $pembimbing5->id,
            $siswa12->id,
            $siswa13->id,
        ]);
        
        $this->command->info('Semua anggota berhasil didaftarkan ke periode PKL.');
    }
}
