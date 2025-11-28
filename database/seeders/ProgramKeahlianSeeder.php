<?php

namespace Database\Seeders;

use App\Models\ProgramKeahlian;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramKeahlianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProgramKeahlian::create(['nama' => 'Rekayasa Perangkat Lunak']);
        ProgramKeahlian::create(['nama' => 'Teknik Komputer dan Jaringan']);
    }
}
