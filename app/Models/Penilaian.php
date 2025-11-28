<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';

    protected $fillable = [
        'siswa_id',
        'penilai_id',
        'pkl_tanggal_mulai',
        'pkl_tanggal_selesai',
        'tanggal_penilaian',
        'komentar_saran',
        'nilai_rata_rata',
    ];

    protected $casts = [
        'pkl_tanggal_mulai' => 'date',
        'pkl_tanggal_selesai' => 'date',
        'tanggal_penilaian' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }

    public function detailPenilaian()
    {
        return $this->hasMany(DetailPenilaian::class);
    }
}