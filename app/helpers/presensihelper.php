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

        // Debug log
        \Log::info('Status Debug', [
            'user_id' => $userId,
            'tanggal' => $tanggal,
            'pagi_raw' => $pagi?->status,
            'sore_raw' => $sore?->status,
            'pagi_normalized' => $pagiStatus,
            'sore_normalized' => $soreStatus,
        ]);

        // 1. Jika ada izin/sakit di salah satu sesi, prioritaskan itu
        if (in_array($pagiStatus, ['IZIN', 'SAKIT'])) return strtolower($pagiStatus);
        if (in_array($soreStatus, ['IZIN', 'SAKIT'])) return strtolower($soreStatus);

        // 2. Jika keduanya tidak presensi sama sekali
        if (!$pagiStatus && !$soreStatus) return 'absen';

        // 3. Jika keduanya hadir (tepat waktu atau telat)
        if ($pagiStatus && $soreStatus) {
            $hadirStatuses = ['TEPAT', 'TELAT', 'SANGAT_TELAT', 'TERLALU_AWAL'];

            if (in_array($pagiStatus, $hadirStatuses) && in_array($soreStatus, $hadirStatuses)) {
                // Jika ada yang telat, status keseluruhan = telat
                if (
                    $pagiStatus === 'TELAT' || $soreStatus === 'TELAT' ||
                    $pagiStatus === 'SANGAT_TELAT' || $soreStatus === 'SANGAT_TELAT'
                ) {
                    return 'telat';
                }
                // Jika keduanya tepat waktu
                return 'hadir';
            }
        }

        // 4. Jika hanya hadir salah satu sesi = bolos
        if (($pagiStatus && !$soreStatus) || (!$pagiStatus && $soreStatus)) {
            $hadirStatuses = ['TEPAT', 'TELAT', 'SANGAT_TELAT', 'TERLALU_AWAL'];

            if (in_array($pagiStatus, $hadirStatuses) || in_array($soreStatus, $hadirStatuses)) {
                return 'Tidak Lengkap';
            }
        }

        // 5. Default fallback
        return 'alpa';
    }

    // Konversi berbagai format status ke format standar
    private static function normalizeStatus($status)
    {
        if (!$status) return null;

        // Mapping status dari database ke format standar
        $statusMap = [
            // Format dari database
            'Tepat Waktu'      => 'TEPAT',
            'Terlambat'        => 'TELAT',
            'Sangat Terlambat' => 'SANGAT_TELAT',
            'Terlalu Awal'     => 'TERLALU_AWAL',
            'Izin'             => 'IZIN',
            'Sakit'            => 'SAKIT',
            'alpa'             => 'ALPA',

            // Format lama (jika ada)
            'Tepat'            => 'TEPAT',
            'Telat'            => 'TELAT',
            'Hadir'            => 'TEPAT',
        ];

        // Cek exact match dulu
        if (isset($statusMap[$status])) {
            return $statusMap[$status];
        }

        // Cek case insensitive
        foreach ($statusMap as $key => $value) {
            if (strtolower($key) === strtolower($status)) {
                return $value;
            }
        }

        // Jika tidak ditemukan, return uppercase
        return strtoupper($status);
    }

    public static function getStatusColor($status)
    {
        $colors = [
            'hadir'          => 'success',
            'telat'          => 'warning',
            'izin'           => 'info',
            'sakit'          => 'secondary',
            'absen'          => 'danger',
            'bolos'          => 'dark',
            'alpa'           => 'danger',
        ];

        return $colors[strtolower($status)] ?? 'light';
    }

    public static function getStatusLabel($status)
    {
        $labels = [
            'hadir'          => 'Hadir',
            'telat'          => 'Terlambat',
            'izin'           => 'Izin',
            'sakit'          => 'Sakit',
            'absen'          => 'Tidak Hadir',
            'bolos'          => 'Bolos',
            'alpa'           => 'Alpa',
        ];

        return $labels[strtolower($status)] ?? ucfirst($status);
    }

    public static function hitungStatistikHarian($tanggal = null)
    {
        $tanggal = $tanggal ?? now()->toDateString();
        $users = \App\Models\User::where('group_id', 4)->get();

        $statistik = [
            'total'          => $users->count(),
            'hadir'          => 0,
            'telat'          => 0,
            'izin'           => 0,
            'sakit'          => 0,
            'absen'          => 0,
            'bolos'          => 0,
            'alpa'           => 0,
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
        $waktuMulai = $sesi === 'pagi' ? $setting->pagi_mulai : $setting->sore_mulai;

        if (!$batasWaktu || !$waktuMulai) return 'Tepat Waktu';

        $toleransi = $setting->toleransi_telat ?? 15; // Default 15 menit

        try {
            $waktuPresensi       = Carbon::createFromFormat('H:i:s', $jamPresensi);
            $waktuMulaiCarbon    = Carbon::createFromFormat('H:i:s', $waktuMulai);
            $waktuBatasCarbon    = Carbon::createFromFormat('H:i:s', $batasWaktu);
            $waktuBatasToleransi = Carbon::createFromFormat('H:i:s', $batasWaktu)->addMinutes($toleransi);

            // Terlalu awal (sebelum waktu mulai)
            if ($waktuPresensi->lt($waktuMulaiCarbon)) {
                return 'Terlalu Awal';
            }

            // Tepat waktu (dalam rentang normal)
            if ($waktuPresensi->between($waktuMulaiCarbon, $waktuBatasCarbon)) {
                return 'Tepat Waktu';
            }

            // Terlambat (dalam toleransi)
            if ($waktuPresensi->between($waktuBatasCarbon->copy()->addSecond(), $waktuBatasToleransi)) {
                return 'Terlambat';
            }

            // Sangat terlambat (melebihi toleransi)
            if ($waktuPresensi->gt($waktuBatasToleransi)) {
                return 'Sangat Terlambat';
            }

            return 'Tepat Waktu';
        } catch (\Exception $e) {
            \Log::error('Error in getStatusByTime: ' . $e->getMessage());
            return 'Tepat Waktu';
        }
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
        $color = self::getStatusColor($status);
        $label = self::getStatusLabel($status);

        return "<span class=\"badge bg-{$color}\">{$label}</span>";
    }

    // Method untuk debug - bisa dihapus nanti
    public static function debugStatusHarian($userId, $tanggal)
    {
        $pagi = Presensi::where('user_id', $userId)
            ->where('sesi', 'pagi')
            ->whereDate('tanggal_presensi', $tanggal)
            ->first();

        $sore = Presensi::where('user_id', $userId)
            ->where('sesi', 'sore')
            ->whereDate('tanggal_presensi', $tanggal)
            ->first();

        return [
            'pagi' => [
                'ada' => !!$pagi,
                'status_raw' => $pagi?->status,
                'status_normalized' => $pagi ? self::normalizeStatus($pagi->status) : null,
                'jam' => $pagi?->jam_presensi,
            ],
            'sore' => [
                'ada' => !!$sore,
                'status_raw' => $sore?->status,
                'status_normalized' => $sore ? self::normalizeStatus($sore->status) : null,
                'jam' => $sore?->jam_presensi,
            ],
            'status_final' => self::hitungStatusHarian($userId, $tanggal),
        ];
    }
}
