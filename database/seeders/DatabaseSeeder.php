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
            JenisKegiatanSeeder::class,
            ProgramKeahlianSeeder::class,
            KriteriaPenilaianSeeder::class,
            PeriodePKlSeeder::class,
            UserSeeder::class,
            PresensiSettingSeeder::class,
            PresensiStatusSeeder::class,
            // PresensiSeeder::class,
            // ColectDataSeeder::class,
            DivisiSeeder::class,
            // TimSeeder::class,
            // LaporanSeeder::class
            // CostumPeriodSeeder::class,
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
