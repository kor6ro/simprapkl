<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensi';

    protected $fillable = [
        'user_id',
        'sesi',
        'jam_presensi',
        'tanggal_presensi',
        'bukti_foto',
        'keterangan',
        'status',
        'presensi_status_id',
    ];

    protected $casts = [
        'tanggal_presensi' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function presensiStatus()
    {
        return $this->belongsTo(PresensiStatus::class, 'presensi_status_id');
    }

    // Accessor untuk status yang konsisten
    public function getStatusDisplayAttribute()
    {
        return $this->presensiStatus?->status ?? $this->status;
    }

    // Accessor untuk warna badge
    public function getStatusColorAttribute()
    {
        $colors = [
            'Tepat Waktu' => 'success',
            'Terlambat' => 'warning',
            'Izin' => 'info',
            'Sakit' => 'secondary',
            'Alpa' => 'danger',
        ];

        $status = $this->status_display;
        return $colors[$status] ?? 'light';
    }
}
