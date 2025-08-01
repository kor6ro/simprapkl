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
            ->with('presensiStatus')
            ->first();

        $sore = Presensi::where('user_id', $userId)
            ->where('sesi', 'sore')
            ->whereDate('tanggal_presensi', $tanggal)
            ->with('presensiStatus')
            ->first();

        $pagiStatus = $pagi?->presensiStatus?->kode;
        $soreStatus = $sore?->presensiStatus?->kode;

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
}
