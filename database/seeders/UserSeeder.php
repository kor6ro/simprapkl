<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                "name" => "Developer",
                "username" => "developer",
                "email" => "developer@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "1",
                "alamat" => "pacitan",
            ],
            [
                "name" => "Admin",
                "username" => "admin",
                "email" => "admin@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "2",
                "alamat" => "pacitan",
            ],
            [
                "name" => "Admin1",
                "username" => "admin1",
                "email" => "admin@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "2",
                "alamat" => "pacitan",
            ],
             [
                "name" => "Admin2",
                "username" => "admin2",
                "email" => "admin@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "2",
                "alamat" => "pacitan",
            ],
             [
                "name" => "Admin3",
                "username" => "admin3",
                "email" => "admin@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "2",
                "alamat" => "pacitan",
            ],
             [
                "name" => "Admin4",
                "username" => "admin4",
                "email" => "admin@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "2",
                "alamat" => "pacitan",
            ],
             [
                "name" => "Admin5",
                "username" => "admin5",
                "email" => "admin@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "2",
                "alamat" => "pacitan",
            ],
            [
                "name" => "Admin6",
                "username" => "admin`6",
                "email" => "admin@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "2",
                "alamat" => "pacitan",
            ],
            [
                "name" => "Pembimbing",
                "username" => "pembimbing",
                "email" => "pembimbing@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "3",
                "alamat" => "pacitan",
            ],
            [
                "name" => "Adit Prakoso",
                "username" => "adit",
                "email" => "adit.prakoso01@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "Jakarta",
            ],
            [
                "name" => "Raka Putra",
                "username" => "raka",
                "email" => "raka.putra02@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "Bandung",
            ],
            [
                "name" => "Zahra Ayu",
                "username" => "zahra",
                "email" => "zahra.ayu03@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "Surabaya",
            ],
            [
                "name" => "Naufal Rizky",
                "username" => "naufal",
                "email" => "naufal.rizky04@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "Yogyakarta",
            ],
            [
                "name" => "Alif Ramadhan",
                "username" => "alif",
                "email" => "alif.ramadhan05@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "Semarang",
            ],
            [
                "name" => "Keyla Putri",
                "username" => "keyla",
                "email" => "keyla.putri06@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "Makassar",
            ],
            [
                "name" => "Daffa Pratama",
                "username" => "daffa",
                "email" => "daffa.pratama07@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "Medan",
            ],
            [
                "name" => "Celine Oktavia",
                "username" => "celine",
                "email" => "celine.oktavia08@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "Palembang",
            ],
            [
                "name" => "Rafif Adrian",
                "username" => "rafif",
                "email" => "rafif.adrian09@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "Balikpapan",
            ],
            [
                "name" => "Aurora Salma",
                "username" => "aurora",
                "email" => "aurora.salma10@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "Malang",
            ],
            [
                "name" => "Iqbal Mahendra",
                "username" => "iqbal",
                "email" => "iqbal.mahendra11@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "Solo",
            ]

        ];

        User::insert($data);
    }
}
