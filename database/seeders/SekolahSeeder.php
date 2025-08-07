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
            ["nama" => "SMKN 1 Pacitan", "logo" => "logo-smk1.png"],
            ["nama" => "SMKN 2 Pacitan", "logo" => "logo-smk2.png"],
            ["nama" => "SMKN 3 Pacitan", "logo" => "logo-smk3.png"]
        ];

        Sekolah::insert($data);
    }
}
