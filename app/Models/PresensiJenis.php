<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PresensiJenis extends Model
{
    use HasFactory;

    protected $table = 'presensi_jenis';

    protected $fillable = [
        'nama',
        'butuh_bukti',
        'otomatis',
    ];

    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }
}
