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
                "name" => "SiswaPKL",
                "username" => "siswapkl",
                "email" => "siswapkl@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "pacitan",
            ],
            [
                "name" => "John Doe",
                "username" => "john",
                "email" => "johndoe01@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "pacitan",
            ],
            [
                "name" => "Jane Smith",
                "username" => "jane",
                "email" => "janesmith02@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "pacitan",
            ],
            [
                "name" => "Alice Johnson",
                "username" => "alice",
                "email" => "alicejohnson03@gmail.com",
                "password" => Hash::make("password"),
                "validasi" => "1",
                "sekolah_id" => "1",
                "group_id" => "4",
                "alamat" => "pacitan",
            ],
        ];

        User::insert($data);
    }
}
