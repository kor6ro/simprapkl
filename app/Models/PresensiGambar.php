<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiGambar extends Model
{
    use HasFactory;

    protected $table = 'presensi_gambar';

    protected $fillable = [
        'presensi_id',
        'bukti',
    ];


    public function presensi()
    {
        return $this->belongsTo(Presensi::class);
    }
}
