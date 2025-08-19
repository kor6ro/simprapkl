<?php

namespace App\Helpers;

use App\Models\Presensi;
use Illuminate\Support\Carbon;

class PresensiHelper
{
    /**
     * Menghitung status harian dengan melakukan query ke database.
     * Method ini tetap dipertahankan untuk penggunaan di luar proses rekap massal.
     */
    public static function hitungStatusHarian($userId, $tanggal)
    {
        $presensiHarian = Presensi::where('user_id', $userId)
            ->whereDate('tanggal_presensi', $tanggal)
            ->get();

        // Memanggil method baru yang bekerja dengan koleksi data
        return self::hitungStatusHarianFromCollection($presensiHarian);
    }

    /**
     * METHOD BARU: Menghitung status harian dari koleksi data yang sudah di-query.
     * Ini adalah kunci untuk performa ekspor yang cepat, karena tidak ada query baru di sini.
     */
    public static function hitungStatusHarianFromCollection($presensiHarian)
    {
        if ($presensiHarian->isEmpty()) {
            return 'alpa';
        }

        $pagi = $presensiHarian->where('sesi', 'pagi')->first();
        $sore = $presensiHarian->where('sesi', 'sore')->first();

        $pagiStatus = $pagi ? self::normalizeStatus($pagi->status) : null;
        $soreStatus = $sore ? self::normalizeStatus($sore->status) : null;

        // 1. Prioritaskan Izin atau Sakit jika ada
        if (in_array($pagiStatus, ['IZIN', 'SAKIT'])) return strtolower($pagiStatus);
        if (in_array($soreStatus, ['IZIN', 'SAKIT'])) return strtolower($soreStatus);

        // 2. Keduanya hadir
        $hadirStatuses = ['TEPAT', 'TELAT', 'SANGAT_TELAT', 'TERLALU_AWAL'];
        if ($pagiStatus && $soreStatus && in_array($pagiStatus, $hadirStatuses) && in_array($soreStatus, $hadirStatuses)) {
            // Jika salah satu atau keduanya telat, maka statusnya telat
            if (in_array($pagiStatus, ['TELAT', 'SANGAT_TELAT']) || in_array($soreStatus, ['TELAT', 'SANGAT_TELAT'])) {
                return 'telat';
            }
            // Jika keduanya hadir tanpa telat
            return 'hadir';
        }

        // 3. Jika salah satu sesi tidak ada (dianggap Alpa) atau statusnya Alpa
        if (!$pagiStatus || !$soreStatus || $pagiStatus === 'ALPA' || $soreStatus === 'ALPA') {
            return 'alpa';
        }

        // Fallback default
        return 'alpa';
    }

    /**
     * Konversi berbagai format status ke format standar yang konsisten.
     * Private method, tidak ada perubahan.
     */
    private static function normalizeStatus($status)
    {
        if (!$status) return null;
        $statusMap = [
            'Tepat Waktu'      => 'TEPAT',
            'Terlambat' => 'TELAT',
            'Sangat Terlambat' => 'SANGAT_TELAT',
            'Terlalu Awal'     => 'TERLALU_AWAL',
            'Izin' => 'IZIN',
            'Sakit' => 'SAKIT',
            'Alpa' => 'ALPA', // 'alpa' dari DB jadi 'ALPA'
            'Tepat' => 'TEPAT',
            'Telat' => 'TELAT',
            'Hadir' => 'TEPAT',
        ];
        foreach ($statusMap as $key => $value) {
            if (strcasecmp($key, $status) == 0) return $value;
        }
        return strtoupper($status);
    }

    /**
     * Menentukan sesi saat ini berdasarkan waktu.
     */
    public static function getCurrentSession($setting, $currentTime)
    {
        if (!$setting) return null;
        if ($currentTime >= $setting->pagi_mulai && $currentTime < $setting->sore_mulai) {
            return 'pagi';
        }
        if ($currentTime >= $setting->sore_mulai && $currentTime <= $setting->sore_selesai) {
            return 'sore';
        }
        return null;
    }

    /**
     * Mendapatkan status presensi berdasarkan waktu (Tepat Waktu, Terlambat, dll).
     * Tidak ada perubahan.
     */
    public static function getStatusByTime($jamPresensi, $sesi, $setting)
    {
        if (!$setting) return 'Tepat Waktu';
        $batasWaktu = $sesi === 'pagi' ? $setting->pagi_selesai : $setting->sore_selesai;
        $waktuMulai = $sesi === 'pagi' ? $setting->pagi_mulai : $setting->sore_mulai;
        if (!$batasWaktu || !$waktuMulai) return 'Tepat Waktu';
        $toleransi = $setting->toleransi_telat ?? 15;
        try {
            $waktuPresensi = Carbon::createFromFormat('H:i:s', $jamPresensi);
            $waktuMulaiCarbon = Carbon::createFromFormat('H:i:s', $waktuMulai);
            $waktuBatasCarbon = Carbon::createFromFormat('H:i:s', $batasWaktu);
            $waktuBatasToleransi = $waktuBatasCarbon->copy()->addMinutes($toleransi);
            if ($waktuPresensi->lt($waktuMulaiCarbon)) return 'Terlalu Awal';
            if ($waktuPresensi->betweenIncluded($waktuMulaiCarbon, $waktuBatasCarbon)) return 'Tepat Waktu';
            if ($waktuPresensi->between($waktuBatasCarbon, $waktuBatasToleransi)) return 'Terlambat';
            if ($waktuPresensi->gt($waktuBatasToleransi)) return 'Sangat Terlambat';
            return 'Tepat Waktu';
        } catch (\Exception $e) {
            Log::error('Error in getStatusByTime: ' . $e->getMessage());
            return 'Tepat Waktu';
        }
    }

    /**
     * METHOD BARU: Membuat HTML badge untuk status presensi.
     * Dipanggil oleh Controller.
     */
    public static function renderStatusBadge($row): string
    {
        if ($row->approval_status === 'pending') {
            return '<span class="badge bg-warning">' . e($row->requested_status) . ' (Menunggu)</span>';
        }
        $status = $row->status ?? '-';
        $map = [
            'Tepat Waktu' => 'success',
            'Terlambat' => 'warning',
            'Sangat Terlambat' => 'danger',
            'Terlalu Awal' => 'secondary',
            'Izin' => 'info',
            'Sakit' => 'primary',
            'Alpa' => 'danger',
        ];
        $class = $map[$status] ?? 'light';
        return '<span class="badge bg-' . $class . '">' . e($status) . '</span>';
    }

    /**
     * METHOD BARU: Membuat HTML badge untuk status approval.
     * Dipanggil oleh Controller.
     */
    public static function renderApprovalBadge($row): string
    {
        if (!$row->approval_status) {
            return '<span class="badge bg-light text-muted">-</span>';
        }
        $map = [
            'pending' => ['class' => 'warning', 'text' => 'Menunggu'],
            'approved' => ['class' => 'success', 'text' => 'Disetujui'],
            'rejected' => ['class' => 'danger', 'text' => 'Ditolak'],
        ];
        $config = $map[$row->approval_status] ?? ['class' => 'secondary', 'text' => ucfirst($row->approval_status)];
        return '<span class="badge bg-' . $config['class'] . '">' . e($config['text']) . '</span>';
    }

    /**
     * Menyimpan gambar base64 ke storage.
     * METHOD BARU untuk merapikan Controller.
     */
    public static function storeBase64Image(string $imageData, $userId): string
    {
        if (str_contains($imageData, ',')) {
            $imageData = explode(',', $imageData, 2)[1];
        }
        $imageFile = base64_decode($imageData);
        if ($imageFile === false) {
            throw new \Exception('Invalid base64 data');
        }
        $fileName = 'camera_' . date('Y-m-d_H-i-s') . '_' . $userId . '_' . uniqid() . '.jpg';
        $path = 'uploads/presensi/' . $fileName;
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageFile);
        return $path;
    }

    // ===================================================================
    // Method original Anda yang tidak disentuh (tetap ada untuk jaga-jaga)
    // ===================================================================
    public static function getStatusColor($status)
    {
        $colors = ['hadir' => 'success', 'telat' => 'warning', 'izin' => 'info', 'sakit' => 'secondary', 'alpa' => 'danger'];
        return $colors[strtolower($status)] ?? 'light';
    }
    public static function getStatusLabel($status)
    {
        $labels = ['hadir' => 'Hadir', 'telat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'];
        return $labels[strtolower($status)] ?? ucfirst($status);
    }
    public static function hitungStatistikHarian($tanggal = null)
    {
        $tanggal = $tanggal ?? now()->toDateString();
        $users = \App\Models\User::where('group_id', 4)->get();
        $statistik = ['total' => $users->count(), 'hadir' => 0, 'telat' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0];
        foreach ($users as $user) {
            $status = self::hitungStatusHarian($user->id, $tanggal);
            if (isset($statistik[$status])) $statistik[$status]++;
        }
        return $statistik;
    }
}
