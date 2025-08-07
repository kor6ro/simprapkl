<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasHarian extends Model
{
    use HasFactory;

    protected $table = 'tugas_harian';

    protected $fillable = [
        'user_id',
        'tanggal',
        'mulai',
        'selesai',
        'laporan',
    ];

    protected $dates = [
        'tanggal',
        'mulai',
        'selesai',
    ];

    // Relasi ke user (siswa)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Durasi dalam menit (akses di view/controller)
    public function getDurasiMenitAttribute()
    {
        if ($this->mulai && $this->selesai) {
            return $this->mulai->diffInMinutes($this->selesai);
        }

        return null;
    }
}
