<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ProgramKeahlian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Developer & Admin
            [
                "name" => "Developer",
                "username" => "developer",
                "email" => "developer@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "group_id" => 1,
                "alamat" => "Pacitan",
            ],
            [
                "name" => "Admin",
                "username" => "admin",
                "email" => "admin@gmail.com",
                "password" => Hash::make("sandya88"),
                "validasi" => "1",
                "group_id" => 2,
                "alamat" => "Pacitan",
            ],

            // 2 Karyawan
            [
                "name" => "RIFFAN BAYU P",
                "username" => "riffan",
                "email" => "karyawan1@gmail.com",
                "password" => Hash::make("sandya88"),
                "validasi" => "1",
                "group_id" => 5,
                "alamat" => "Pacitan",
            ],
            [
                "name" => "FADHILAH FAUZIA F",
                "username" => "fadhilah",
                "email" => "karyawan2@gmail.com",
                "password" => Hash::make("sandya88"),
                "validasi" => "1",
                "group_id" => 5,
                "alamat" => "Pacitan",
            ],
            [
                "name" => "VIKKI K",
                "username" => "vikki",
                "email" => "karyawan3@gmail.com",
                "password" => Hash::make("sandya88"),
                "validasi" => "1",
                "group_id" => 5,
                "alamat" => "Pacitan",
            ],
            [
                "name" => "TAUFIK HIDAYAT",
                "username" => "taufik",
                "email" => "karyawan4@gmail.com",
                "password" => Hash::make("sandya88"),
                "validasi" => "1",
                "group_id" => 5,
                "alamat" => "Pacitan",
            ],
            [
                "name" => "SAJAK PRANOTO",
                "username" => "sajak",
                "email" => "karyawan5@gmail.com",
                "password" => Hash::make("sandya88"),
                "validasi" => "1",
                "group_id" => 5,
                "alamat" => "Pacitan",
            ],
            [
                "name" => "MUHAMAD FAJAR",
                "username" => "fajar",
                "email" => "karyawan6@gmail.com",
                "password" => Hash::make("sandya88"),
                "validasi" => "1",
                "group_id" => 5,
                "alamat" => "Pacitan",
            ],

            // Pembimbing Sekolah 1 (punya 2 program: RPL & TKJ)
            [
                "name" => "Budi Santoso",
                "username" => "pembimbing",
                "email" => "budi.santoso.rpl@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => 1,
                "program_keahlian_id" => 2,
                "group_id" => 3,
                "alamat" => "Jakarta",
            ],

            [
                "name" => "Salsabilla Salwa Indra Wijaya",
                "username" => "kepalaklp",
                "email" => "kepalaklp@gmail.com",
                "password" => Hash::make("sandya88"),
                "validasi" => "1",
                "group_id" => 6,
                "alamat" => "Pacitan",
            ],
            [
                "name" => "Ferry",
                "username" => "wakilkepalaklp",
                "email" => "wakilkepalaklp@gmail.com",
                "password" => Hash::make("sandya88"),
                "validasi" => "1",
                "group_id" => 7,
                "alamat" => "Pacitan",
            ],
        ];

        foreach ($data as $user) {
            User::create($user);
        }
    }
}
