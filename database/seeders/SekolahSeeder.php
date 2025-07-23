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
            ["nama" => "SMKN 1 Pacitan"],
            ["nama" => "SMKN 2 Pacitan"],
            ["nama" => "SMKN 3 Pacitan"]
        ];

        Sekolah::insert($data);
    }
}
