<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = "user";

    protected $fillable = [
        "name",
        "username",
        "email",
        "password",
        "validasi",
        "sekolah_id",
        "group_id",
        "alamat",
        "created_at",
        "updated_at",
    ];

    //Relasi ke sekolah
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, "sekolah_id");
    }

    //Relasi ke group
    public function group()
    {
        return $this->belongsTo(Group::class, "group_id");
    }

    //relasi ke presensi siswa
    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }

    public function divisiHarianToday()
    {
        return $this->hasOne(SiswaDivisiHarian::class, 'siswa_id')->whereDate('tanggal', today());
    }
}
