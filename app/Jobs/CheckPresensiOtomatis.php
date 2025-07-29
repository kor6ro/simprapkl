<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Presensi;
use App\Models\PresensiSetting;
use App\Models\PresensiJenis;
use App\Models\User;
use Carbon\Carbon;

class CheckPresensiOtomatis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $today = Carbon::today();
        $activeSetting = PresensiSetting::where('is_active', true)->first();

        if (!$activeSetting) {
            return; // Tidak ada setting aktif
        }

        // Get jenis presensi untuk telat dan bolos
        $jenisTelat = PresensiJenis::where('nama', 'telat')->first();
        $jenisBolos = PresensiJenis::where('nama', 'bolos')->first();

        if (!$jenisTelat || !$jenisBolos) {
            return; // Jenis presensi tidak ditemukan
        }

        // Get hanya siswa (group_id = 4)
        $students = User::where('group_id', 4)->get();

        foreach ($students as $student) {
            $this->checkUserPresensi($student, $today, $activeSetting, $jenisTelat, $jenisBolos);
        }
    }

    private function checkUserPresensi($user, $today, $activeSetting, $jenisTelat, $jenisBolos)
    {
        // Cek presensi pagi
        $presensiPagi = Presensi::where('user_id', $user->id)
            ->where('tanggal_presensi', $today)
            ->where('sesi', 'pagi')
            ->first();

        // Cek presensi sore
        $presensiSore = Presensi::where('user_id', $user->id)
            ->where('tanggal_presensi', $today)
            ->where('sesi', 'sore')
            ->first();

        // Jika tidak ada presensi pagi, buat bolos pagi
        if (!$presensiPagi) {
            $this->createBolosPresensi($user, $today, 'pagi', $jenisBolos, $activeSetting);
        }

        // Jika tidak ada presensi sore, buat bolos sore
        if (!$presensiSore) {
            $this->createBolosPresensi($user, $today, 'sore', $jenisBolos, $activeSetting);
        }

        // Cek apakah presensi pagi telat
        if ($presensiPagi && $presensiPagi->presensi_jenis_id != $jenisTelat->id) {
            $this->checkTelatPresensi($presensiPagi, $activeSetting, $jenisTelat, 'pagi');
        }

        // Cek apakah presensi sore telat
        if ($presensiSore && $presensiSore->presensi_jenis_id != $jenisTelat->id) {
            $this->checkTelatPresensi($presensiSore, $activeSetting, $jenisTelat, 'sore');
        }
    }

    private function createBolosPresensi($user, $today, $sesi, $jenisBolos, $activeSetting)
    {
        // Cek apakah sudah ada presensi bolos untuk sesi ini
        $existingBolos = Presensi::where('user_id', $user->id)
            ->where('tanggal_presensi', $today)
            ->where('sesi', $sesi)
            ->where('presensi_jenis_id', $jenisBolos->id)
            ->first();

        if (!$existingBolos) {
            Presensi::create([
                'user_id' => $user->id,
                'presensi_jenis_id' => $jenisBolos->id,
                'tanggal_presensi' => $today,
                'sesi' => $sesi,
                'jam_presensi' => $sesi === 'pagi' ? $activeSetting->pagi_selesai : $activeSetting->sore_selesai,
                'keterangan' => 'Otomatis - Tidak ada presensi',
                'status_verifikasi' => 'valid',
                'catatan_verifikasi' => 'Sistem otomatis - Bolos'
            ]);
        }
    }

    private function checkTelatPresensi($presensi, $activeSetting, $jenisTelat, $sesi)
    {
        $jamPresensi = Carbon::createFromFormat('H:i:s', $presensi->jam_presensi);

        if ($sesi === 'pagi') {
            $jamSelesai = Carbon::createFromFormat('H:i:s', $activeSetting->pagi_selesai);
        } else {
            $jamSelesai = Carbon::createFromFormat('H:i:s', $activeSetting->sore_selesai);
        }

        // Jika presensi dilakukan setelah jam selesai, ubah menjadi telat
        if ($jamPresensi->gt($jamSelesai)) {
            $presensi->update([
                'presensi_jenis_id' => $jenisTelat->id,
                'keterangan' => $presensi->keterangan . ' (Diubah otomatis menjadi telat)',
                'catatan_verifikasi' => 'Sistem otomatis - Telat'
            ]);
        }
    }
}
