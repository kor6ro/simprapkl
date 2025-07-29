<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\CheckPresensiOtomatis;

class CheckPresensiOtomatisCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presensi:check-otomatis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and create automatic presensi (telat/bolos)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan presensi otomatis...');

        try {
            CheckPresensiOtomatis::dispatch();
            $this->info('Job pengecekan presensi otomatis berhasil dijalankan!');
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
