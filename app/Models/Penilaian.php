<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penilaian extends Model
{
    use HasFactory;

    /**
     * TAMBAHKAN BARIS INI
     * Beritahu Laravel untuk menggunakan tabel 'penilaian' (tanpa s).
     */
    protected $table = 'penilaian';

    protected $fillable = ['siswa_id', 'penilai_id', 'tanggal_penilaian', 'komentar'];

    // ... (sisa relasi biarkan seperti semula)

    public function detailNilai(): HasMany
    {
        // Pastikan nama tabel di DetailNilai juga benar
        return $this->hasMany(DetailNilai::class);
    }

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }
}
