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

    /**
     * The table associated with the model.
     */
    protected $table = "user";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, "sekolah_id");
    }

    public function group()
    {
        return $this->belongsTo(Group::class, "group_id");
    }
}
