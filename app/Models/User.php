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
        "kode_siswa",
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
        return $this->hasOne(SiswaDivisiHarian::class, 'siswa_id')
            ->whereDate('tanggal', today());
    }

    // hanya tampilkan kode_siswa kalau group_id = 4 (siswa)
    public function getKodeSiswaAttribute($value)
    {
        if ($this->group_id == 4) {
            return $value;
        }
        return null;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if ($user->group_id == 4 && empty($user->kode_siswa)) {
                // hitung jumlah siswa yang sudah ada
                $lastNumber = User::where('group_id', 4)->count();
                $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

                // contoh format: Sid0001, Sid0002, dst.
                $user->kode_siswa = 'Sid' . $nextNumber;
            }
        });
    }
}
