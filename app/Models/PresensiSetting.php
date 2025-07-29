<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiSetting extends Model
{
    use HasFactory;

    protected $table = "presensi_setting";

    protected $fillable = [
        "jam_masuk",
        "jam_pulang",
        "is_active",
    ];
}
