<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColectData extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = "colect_data";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "tanggal",
        "nama_cus",
        "no_telp",
        "alamat_cus",
        "provider_sekarang",
        "kelebihan",
        "kekurangan",
        "serlok",
        "gambar_foto",
        "user_id",
        "created_at",
        "updated_at",
    ];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
