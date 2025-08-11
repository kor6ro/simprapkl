<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiStatus extends Model
{
    use HasFactory;

    protected $table = 'presensi_status';

    protected $fillable = [
        'kode',
        'status',
        'color',
        'deskripsi',
    ];

    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }

    public static function getByKode($kode)
    {
        return self::where('kode', $kode)->first();
    }
}
