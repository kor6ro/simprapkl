<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiSetting extends Model
{
    use HasFactory;

    protected $table = "presensi_setting";

    protected $fillable = [
        "pagi_mulai",
        "pagi_selesai",
        "sore_mulai",
        "sore_selesai",
        "toleransi_telat",  // Tambah ini
    ];

    protected $casts = [
        'toleransi_telat' => 'integer',
    ];

    public static function getSetting()
    {
        return self::first();
    }

    // Helper method untuk mendapatkan batas waktu dengan toleransi
    public function getBatasToleransiPagi()
    {
        if (!$this->pagi_selesai) return null;

        try {
            // Try different time formats
            $time = $this->pagi_selesai;

            // If it's already a Carbon instance, convert to string first
            if ($time instanceof \Carbon\Carbon) {
                $time = $time->format('H:i:s');
            }

            // Try to create Carbon from various formats
            $carbon = null;

            // Format 1: H:i:s (08:15:00)
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
                $carbon = \Carbon\Carbon::createFromFormat('H:i:s', $time);
            }
            // Format 2: H:i (08:15)
            elseif (preg_match('/^\d{2}:\d{2}$/', $time)) {
                $carbon = \Carbon\Carbon::createFromFormat('H:i', $time);
            }
            // Format 3: Try to parse as is
            else {
                $carbon = \Carbon\Carbon::parse($time);
            }

            if ($carbon) {
                return $carbon->addMinutes($this->toleransi_telat ?? 15)->format('H:i:s');
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Error parsing pagi_selesai time: ' . $e->getMessage(), [
                'pagi_selesai' => $this->pagi_selesai,
                'type' => gettype($this->pagi_selesai)
            ]);
            return null;
        }
    }

    public function getBatasToleransiSore()
    {
        if (!$this->sore_selesai) return null;

        try {
            // Try different time formats
            $time = $this->sore_selesai;

            // If it's already a Carbon instance, convert to string first
            if ($time instanceof \Carbon\Carbon) {
                $time = $time->format('H:i:s');
            }

            // Try to create Carbon from various formats
            $carbon = null;

            // Format 1: H:i:s (17:00:00)
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
                $carbon = \Carbon\Carbon::createFromFormat('H:i:s', $time);
            }
            // Format 2: H:i (17:00)
            elseif (preg_match('/^\d{2}:\d{2}$/', $time)) {
                $carbon = \Carbon\Carbon::createFromFormat('H:i', $time);
            }
            // Format 3: Try to parse as is
            else {
                $carbon = \Carbon\Carbon::parse($time);
            }

            if ($carbon) {
                return $carbon->addMinutes($this->toleransi_telat ?? 15)->format('H:i:s');
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Error parsing sore_selesai time: ' . $e->getMessage(), [
                'sore_selesai' => $this->sore_selesai,
                'type' => gettype($this->sore_selesai)
            ]);
            return null;
        }
    }
}
