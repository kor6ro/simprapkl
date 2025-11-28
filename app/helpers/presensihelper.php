<?php

namespace App\Helpers;

use App\Models\Presensi;
use App\Models\PresensiSetting;
use App\Models\PresensiStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class PresensiHelper
{

    public static function getCurrentSession(PresensiSetting $setting): ?string
    {
        $now = now();
        $pagiMulai = Carbon::parse($setting->pagi_mulai);
        $soreMulai = Carbon::parse($setting->sore_mulai);
        $soreSelesai = Carbon::parse($setting->sore_selesai);

        if ($now->isBetween($pagiMulai, $soreMulai->copy()->subSecond())) {
            return 'pagi';
        }
        
        if ($now->isBetween($soreMulai, $soreSelesai)) {
            return 'sore';
        }

        return null;
    }

    /**
     * Menyimpan gambar base64 ke storage.
     * (Tidak ada perubahan di sini)
     */
    public static function storeBase64Image(string $imageData, int $userId): string
    {
        if (str_contains($imageData, ',')) {
            $imageData = explode(',', $imageData, 2)[1];
        }
        $imageFile = base64_decode($imageData);
        if ($imageFile === false) {
            throw new \Exception('Data base64 tidak valid');
        }
        $fileName = 'camera_' . date('Y-m-d_H-i-s') . '_' . $userId . '_' . uniqid() . '.jpg';
        $path = 'uploads/presensi/' . $fileName;
        Storage::disk('public')->put($path, $imageFile);
        return $path;
    }
    /**
     * Menghitung status harian dari koleksi data.
     * Logika ini menjadi jauh lebih sederhana berkat kolom 'kategori' di tabel status.
     */
    public static function hitungStatusHarianFromCollection($presensiHarian)
    {
        if ($presensiHarian->isEmpty()) {
            return 'alpa';
        }

        // Load relasi presensiStatus dengan kategori untuk efisiensi
        $presensiHarian->load('presensiStatus:id,kategori');

        // Cek apakah ada sesi dengan kategori izin atau sakit, itu menjadi prioritas.
        foreach ($presensiHarian as $presensi) {
            if (in_array($presensi->presensiStatus?->kategori, ['izin', 'sakit'])) {
                return $presensi->presensiStatus->kategori;
            }
        }

        // Jika tidak ada izin/sakit, cek kehadiran pagi dan sore
        $pagi = $presensiHarian->where('sesi', 'pagi')->first();
        $sore = $presensiHarian->where('sesi', 'sore')->first();

        // Jika salah satu sesi tidak ada (dan bukan karena izin/sakit), maka alpa.
        if (!$pagi || !$sore) {
            return 'alpa';
        }
        
        // Jika kedua sesi kategori-nya bukan 'hadir' (misal: Alpa), maka alpa.
        if ($pagi->presensiStatus?->kategori !== 'hadir' || $sore->presensiStatus?->kategori !== 'hadir') {
            return 'alpa';
        }

        // Jika salah satu sesi adalah terlambat, maka status hari itu terlambat.
        if (in_array($pagi->status, ['Terlambat', 'Sangat Terlambat']) || in_array($sore->status, ['Terlambat', 'Sangat Terlambat'])) {
            return 'telat';
        }

        // Jika semua kondisi terpenuhi, maka hadir.
        return 'hadir';
    }

    /**
     * Mendapatkan status presensi berdasarkan waktu (Tepat Waktu, Terlambat, dll).
     * Tidak ada perubahan signifikan.
     */
    public static function getStatusByTime($jamPresensi, $sesi, $setting)
    {
        if (!$setting) return 'TEPAT_WAKTU';
        try {
            $waktuPresensi = Carbon::parse($jamPresensi);
            $waktuMulai = Carbon::parse($sesi === 'pagi' ? $setting->pagi_mulai : $setting->sore_mulai);
            $waktuSelesai = Carbon::parse($sesi === 'pagi' ? $setting->pagi_selesai : $setting->sore_selesai);
            $toleransi = $setting->toleransi_telat ?? 15;

            if ($waktuPresensi->lt($waktuMulai)) return 'TERLALU_AWAL';
            if ($waktuPresensi->lte($waktuSelesai)) return 'TEPAT_WAKTU';
            if ($waktuPresensi->lte($waktuSelesai->copy()->addMinutes($toleransi))) return 'TERLAMBAT';
            
            return 'SANGAT_TERLAMBAT';
        } catch (\Exception $e) {
            \Log::error('Error in getStatusByTime: ' . $e->getMessage());
            return 'TEPAT_WAKTU';
        }
    }

    /**
     * Membuat HTML badge untuk status presensi.
     * Sedikit disederhanakan.
     */
    public static function renderStatusBadge($row): string
    {
        if ($row->approval_status === 'pending') {
            return '<span class="badge bg-warning">' . e($row->requested_status) . ' (Menunggu)</span>';
        }
        $status = $row->status ?? '-';
        $kategori = $row->presensiStatus?->kategori ?? 'alpa';

        $map = [
            'hadir' => 'success',
            'izin' => 'info',
            'sakit' => 'primary',
            'alpa' => 'danger',
        ];
        $class = $map[$kategori] ?? 'light';

        if ($status === 'Terlambat' || $status === 'Sangat Terlambat') {
            $class = 'warning';
        }
        
        return '<span class="badge bg-' . $class . '">' . e($status) . '</span>';
    }

    /**
     * Membuat HTML badge untuk status approval.
     * Tidak ada perubahan, sudah bagus.
     */
    public static function renderApprovalBadge($row): string
    {
        if (!$row->approval_status) return '<span class="badge bg-light text-muted">-</span>';
        $map = [
            'pending' => ['class' => 'warning', 'text' => 'Menunggu'],
            'approved' => ['class' => 'success', 'text' => 'Disetujui'],
            'rejected' => ['class' => 'danger', 'text' => 'Ditolak']
        ];
        $config = $map[$row->approval_status] ?? ['class' => 'secondary', 'text' => ucfirst($row->approval_status)];
        return '<span class="badge bg-' . $config['class'] . '">' . e($config['text']) . '</span>';
    }
}