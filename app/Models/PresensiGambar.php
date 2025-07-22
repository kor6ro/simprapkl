<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiGambar extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = "presensi_gambar";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "gmbr_presensi_pagi",
        "gmbr_presensi_sore",
        "presensi_id",
        "created_at",
        "updated_at",
    ];
}
