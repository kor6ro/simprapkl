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
                "name" => "Karyawan1",
                "username" => "Karyawan1",
                "email" => "Karyawan1@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "5",
                "alamat" => "pacitan",
            ],
             [
                "name" => "Karyawan2",
                "username" => "Karyawan2",
                "email" => "Karyawan2@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "5",
                "alamat" => "pacitan",
            ],
             [
                "name" => "karyawan3",
                "username" => "Karyawan3",
                "email" => "Karyawan3@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "5",
                "alamat" => "pacitan",
            ],
             [
                "name" => "Karyawan4",
                "username" => "Karyawan4",
                "email" => "Karyawan4@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "5",
                "alamat" => "pacitan",
            ],
             [
                "name" => "Karyawan5",
                "username" => "Karyawan5",
                "email" => "Karyawan5@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "5",
                "alamat" => "pacitan",
            ],
            [
                "name" => "Karyawan6",
                "username" => "Karyawan6",
                "email" => "Karyawan@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "5",
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
