<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingPresensi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = "setting_presensi";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "status_presensi",
        "tanggal_presensi",
        "user_id",
        "created_at",
        "updated_at",
    ];

    public function presensi()
    {
        return $this->hasMany(User::class, "settingpresensi_id");
    }
}
