<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = "laporan";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "jenis_kegiatan",
        "lokasi",
        "homepass",
        "jml_orang_ditemui",
        "detail_pekerjaan",
        "hasil_capaian",
        "user_id",
        "jenis_laporan_id",
        "laporan_gambar_id",
        "created_at",
        "updated_at",
    ];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function jenislaporan()
    {
        return $this->belongsTo(JenisLaporan::class, "jenis_laporan_id");
    }

    public function laporangambar()
    {
        return $this->belongsTo(LaporanGambar::class, "laporan_gambar_id");
    }
}
