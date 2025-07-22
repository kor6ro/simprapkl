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
        ];

        User::insert($data);
    }
}
