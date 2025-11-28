<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PresensiSetting;

class PresensiSettingSeeder extends Seeder
{
    public function run()
    {
        PresensiSetting::updateOrCreate(['id' => 1], [
            'pagi_mulai' => '07:00',
            'pagi_selesai' => '08:15',
            'sore_mulai' => '16:00',
            'sore_selesai' => '21:00',
            'toleransi_telat' => 15,
        ]);
    }
}