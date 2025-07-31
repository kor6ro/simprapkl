<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PresensiSetting;

class PresensiSettingSeeder extends Seeder
{
    public function run()
    {
        PresensiSetting::create([
            'pagi_mulai' => '07:00:00',
            'pagi_selesai' => '08:15:00',
            'sore_mulai' => '16:00:00',
            'sore_selesai' => '20:00:00',
        ]);
    }
}
