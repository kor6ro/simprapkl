<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PresensiStatus;

class PresensiStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = ['tepat', 'telat', 'izin', 'sakit'];

        foreach ($statuses as $status) {
            PresensiStatus::updateOrCreate(
                ['status' => $status],
                ['status' => $status]
            );
        }
    }
}
