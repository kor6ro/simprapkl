<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = "presensi";

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
}
