<?php

namespace Database\Seeders;

use App\Models\JenisKegiatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->call([
            SekolahSeeder::class,
            GroupSeeder::class,
            UserSeeder::class,
            PresensiSettingSeeder::class,
            PresensiStatusSeeder::class,
            PresensiSeeder::class,
            DivisiSeeder::class,
            TimSeeder::class,
            JenisKegiatanSeeder::class
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
