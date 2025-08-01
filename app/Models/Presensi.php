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
        'presensi_status_id',
        'sesi',
        'jam_presensi',
        'tanggal_presensi',
        'bukti_foto',
        'keterangan',
        'status_verifikasi',
        'catatan_verifikasi',
    ];

    protected $casts = [
        'tanggal_presensi' => 'date',
        'jam_presensi' => 'datetime:H:i:s',
        'status_verifikasi' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function presensiStatus()
    {
        return $this->belongsTo(PresensiStatus::class, 'presensi_status_id');
    }
}
