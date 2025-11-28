<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;

    protected $table = 'laporan';

    // Izinkan kolom approver_id diisi secara massal
    protected $fillable = [
        'tim_id',
        'user_id',
        'jenis_kegiatan_id',
        'deskripsi_kegiatan',
        'bukti_foto',
        'status',
        'feedback',
        'approver_id', // <-- Tambahkan ini
    ];

    // Relasi ke Tim
    public function tim()
    {
        return $this->belongsTo(Tim::class, 'tim_id');
    }

    // Relasi ke User (Siswa)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Jenis Kegiatan
    public function jenisKegiatan()
    {
        return $this->belongsTo(JenisKegiatan::class, 'jenis_kegiatan_id');
    }

    /**
     * [UBAH] Ganti relasi rejecter() menjadi approver()
     * Relasi ke User yang menyetujui/menolak laporan.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}