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
}
