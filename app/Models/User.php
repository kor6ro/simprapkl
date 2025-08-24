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
        "id_pkl",
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

    // hanya tampilkan id_pkl kalau group_id = 4 (siswa)
    public function getKodeSiswaAttribute($value)
    {
        if ($this->group_id == 4) {
            return $value;
        }
        return null;
    }

    // app/Models/User.php (di dalam class User)

    public function penilaian()
    {
        return $this->hasOne(Penilaian::class, 'siswa_id');
    }
    public function penilai()
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }
    public function siswaBimbingan()
    {
        return $this->hasMany(User::class, 'penilai_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if ($user->group_id == 4 && empty($user->id_pkl)) {

                $lastNumber = User::where('group_id', 4)->count();
                $nextNumber = str_pad($lastNumber + 28, 5, '0', STR_PAD_LEFT);
                $baseId = 'G1-INT' . $nextNumber;

                $formattedName = strtoupper(str_replace(' ', '_', $user->name));
                // Baris ini opsional, untuk membersihkan karakter selain huruf, angka, dan underscore
                $formattedName = preg_replace('/[^A-Z0-9_]/', '', $formattedName);

                $formattedSchool = strtoupper(str_replace(' ', '_', $user->sekolah->nama));
                $formattedSchool = preg_replace('/[^A-Z0-9_]/', '', $formattedSchool);

                $user->id_pkl = $baseId . '_' . $formattedName . '_' . $formattedSchool;
            }
        });
    }
}
