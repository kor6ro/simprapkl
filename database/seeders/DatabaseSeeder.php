<?php

namespace Database\Seeders;

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
            UserSeeder::class,
            GroupSeeder::class,
            SekolahSeeder::class,
            PresensiStatusSeeder::class,
            PresensiSettingSeeder::class,
            PresensiSeeder::class
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
