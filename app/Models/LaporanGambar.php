<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanGambar extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = "laporan_gambar";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ["gambar", "laporan_id", "created_at", "updated_at"];
}
