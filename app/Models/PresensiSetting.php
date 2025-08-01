<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiSetting extends Model
{
    use HasFactory;

    protected $table = "presensi_setting";

    protected $fillable = [
        "pagi_mulai",
        "pagi_selesai",
        "sore_mulai",
        "sore_selesai",
    ];

    protected $casts = [
        'pagi_mulai' => 'datetime:H:i:s',
        'pagi_selesai' => 'datetime:H:i:s',
        'sore_mulai' => 'datetime:H:i:s',
        'sore_selesai' => 'datetime:H:i:s',
    ];


    public static function getSetting()
    {
        return self::first();
    }
}
