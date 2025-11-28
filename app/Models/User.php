<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Tim;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = "user";

    /**
     * PERBAIKAN: Menghapus created_at dan updated_at karena sudah otomatis.
     */
    protected $fillable = [
        "name",
        "username",
        "email",
        "password",
        "validasi",
        "photo_profile",
        "sekolah_id",
        "program_keahlian_id",
        "group_id",
        "alamat",
        "id_pkl",
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

    public function programKeahlian()
    {
        return $this->belongsTo(ProgramKeahlian::class, 'program_keahlian_id');
    }

    //relasi ke presensi siswa
    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }
    public function periodePkl()
    {
        return $this->belongsToMany(PeriodePkl::class, 'periode_pkl_user', 'user_id', 'periode_pkl_id');
    }
      public function timKetua(): BelongsToMany
    {
        return $this->belongsToMany(Tim::class, 'tim_ketua', 'user_id', 'tim_id');
    }

    public function tim()
    {
        return $this->belongsToMany(Tim::class, 'tim_anggota', 'user_id', 'tim_id');
    }

    public function penilaian()
    {
        return $this->hasOne(Penilaian::class, 'siswa_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if ($user->group_id == 4 && empty($user->id_pkl)) {

                $sekolah = Sekolah::find($user->sekolah_id);

                if ($sekolah) {
                    $lastNumber = User::where('group_id', 4)->count();
                    $nextNumber = str_pad($lastNumber + 28, 5, '0', STR_PAD_LEFT);
                    $baseId = 'G1-INT' . $nextNumber;

                    // Format nama user
                    $formattedName = strtoupper(str_replace(' ', '_', $user->name));
                    $formattedName = preg_replace('/[^A-Z0-9_]/', '', $formattedName);

                    // 3. Gunakan nama dari model sekolah yang sudah kita ambil.
                    $formattedSchool = strtoupper(str_replace(' ', '_', $sekolah->nama));
                    $formattedSchool = preg_replace('/[^A-Z0-9_]/', '', $formattedSchool);

                    $user->id_pkl = $baseId . '_' . $formattedName . '_' . $formattedSchool;
                }
            }
        });
    }
}
