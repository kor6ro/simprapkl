<?php

namespace App\Helpers;

use App\Models\Presensi;
use App\Models\PresensiStatus;
use Illuminate\Support\Carbon;

class PresensiHelper
{
    public static function hitungStatusHarian($userId, $tanggal)
    {
        $pagi = Presensi::where('user_id', $userId)
            ->where('sesi', 'pagi')
            ->whereDate('tanggal_presensi', $tanggal)
            ->first();

        $sore = Presensi::where('user_id', $userId)
            ->where('sesi', 'sore')
            ->whereDate('tanggal_presensi', $tanggal)
            ->first();

        // Konversi status ke format yang konsisten
        $pagiStatus = $pagi ? self::normalizeStatus($pagi->status) : null;
        $soreStatus = $sore ? self::normalizeStatus($sore->status) : null;

        // Jika ada izin/sakit di salah satu sesi
        if (in_array($pagiStatus, ['IZIN', 'SAKIT'])) return strtolower($pagiStatus);
        if (in_array($soreStatus, ['IZIN', 'SAKIT'])) return strtolower($soreStatus);

        // Jika keduanya hadir
        if ($pagiStatus && $soreStatus) {
            // Jika ada yang telat
            if ($pagiStatus === 'TELAT' || $soreStatus === 'TELAT') return 'telat';
            // Jika keduanya tepat waktu
            if ($pagiStatus === 'TEPAT' && $soreStatus === 'TEPAT') return 'hadir';
        }

        // Jika hanya hadir pagi tapi tidak sore
        if ($pagiStatus && !$soreStatus) {
            if (in_array($pagiStatus, ['TEPAT', 'TELAT'])) return 'bolos';
        }

        // Jika hanya hadir sore tapi tidak pagi
        if (!$pagiStatus && $soreStatus) {
            if (in_array($soreStatus, ['TEPAT', 'TELAT'])) return 'bolos';
        }

        // Jika tidak ada presensi sama sekali
        if (!$pagiStatus && !$soreStatus) return 'absen';

        return 'tidak lengkap'; // fallback
    }

    // Konversi berbagai format status ke format standar
    private static function normalizeStatus($status)
    {
        $statusMap = [
            'Tepat Waktu' => 'TEPAT',
            'Terlambat' => 'TELAT',
            'Izin' => 'IZIN',
            'Sakit' => 'SAKIT',
            'Alpa' => 'ALPA',
        ];

        return $statusMap[$status] ?? strtoupper($status);
    }

    public static function getStatusColor($status)
    {
        $colors = [
            'hadir' => 'success',
            'telat' => 'warning',
            'izin' => 'info',
            'sakit' => 'secondary',
            'absen' => 'danger',
            'bolos' => 'dark',
            'tidak lengkap' => 'warning'
        ];

        return $colors[strtolower($status)] ?? 'light';
    }

    public static function hitungStatistikHarian($tanggal = null)
    {
        $tanggal = $tanggal ?? now()->toDateString();
        $users = \App\Models\User::where('group_id', 4)->get();

        $statistik = [
            'total' => $users->count(),
            'hadir' => 0,
            'telat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'absen' => 0,
            'bolos' => 0
        ];

        foreach ($users as $user) {
            $status = self::hitungStatusHarian($user->id, $tanggal);
            if (isset($statistik[$status])) {
                $statistik[$status]++;
            }
        }

        return $statistik;
    }

    // Helper untuk mendapatkan status presensi berdasarkan waktu
    public static function getStatusByTime($jamPresensi, $sesi, $setting)
    {
        if (!$setting) return 'Tepat Waktu';

        $batasWaktu = $sesi === 'pagi' ? $setting->pagi_selesai : $setting->sore_selesai;

        if (!$batasWaktu) return 'Tepat Waktu';

        $toleransi = $setting->toleransi_telat ?? 10; // Default 10 menit

        $waktuPresensi = Carbon::createFromFormat('H:i:s', $jamPresensi);
        $waktuBatas = Carbon::createFromFormat('H:i:s', $batasWaktu)->addMinutes($toleransi);

        return $waktuPresensi->gt($waktuBatas) ? 'Terlambat' : 'Tepat Waktu';
    }

    // Helper untuk format tampilan waktu
    public static function formatWaktu($waktu)
    {
        return $waktu ? Carbon::parse($waktu)->format('H:i') : '-';
    }

    // Helper untuk cek apakah hari ini weekend
    public static function isWeekend($tanggal = null)
    {
        $tanggal = $tanggal ? Carbon::parse($tanggal) : now();
        return $tanggal->isWeekend();
    }

    // Helper untuk mendapatkan status warna CSS
    public static function getStatusBadge($status)
    {
        $badges = [
            'Tepat Waktu' => 'success',
            'Terlambat' => 'warning',
            'Izin' => 'info',
            'Sakit' => 'secondary',
            'Alpa' => 'danger',
        ];

        $color = $badges[$status] ?? 'light';
        return "<span class=\"badge bg-{$color}\">{$status}</span>";
    }
}
