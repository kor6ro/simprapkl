<?php

namespace Database\Seeders;

use App\Models\Sekolah;
use Illuminate\Database\Seeder;

class SekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ["nama" => "SMK NEGERI 1 Pacitan", "logo" => "logo-smk1.png"],
            ["nama" => "SMK NEGERI 2 Pacitan", "logo" => "logo-smk2.png"],
            ["nama" => "SMK NEGERI 3 Pacitan", "logo" => "logo-smk3.png"],
            ["nama" => "SMK NEGERI Pringkuku", "logo" => "logo-smk4.png"],
            ["nama" => "SMK NEGERI Ngadirojo", "logo" => "logo-smk5.png"],
            ["nama" => "SMK NEGERI Donorojo", "logo" => "logo-smk6.png"],
        ];

        Sekolah::insert($data);
    }
}
