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
        'presensi_jenis_id',
        'sesi',
        'jam_presensi',
        'tanggal_presensi',
        'bukti',
        'keterangan',
        'status_verifikasi',
        'catatan_verifikasi',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jenisPresensi()
    {
        return $this->belongsTo(PresensiJenis::class);
    }

    public function gambar()
    {
        return $this->hasMany(PresensiGambar::class);
    }
}
