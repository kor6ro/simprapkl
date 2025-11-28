<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ["nama" => "Developer"],
            ["nama" => "Admin"],
            ["nama" => "Pembimbing"],
            ["nama" => "Siswa"],
            ["nama" => "Karyawan"],
            ["nama" => "Kepala Kantor Layanan Pacitan"],
            ["nama" => "Wakil Kepala Kantor Layanan Pacitan"],
        ];

        Group::insert($data);
    }
}
