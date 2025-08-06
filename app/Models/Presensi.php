<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Presensi extends Model
{
    use HasFactory;
    protected $table = 'presensi';
    protected $fillable = [
        'user_id',
        'sesi',
        'jam_presensi',
        'tanggal_presensi',
        'bukti_foto',
        'keterangan',
    ];
    protected $casts = [
        'tanggal_presensi' => 'date',
        'jam_presensi' => 'datetime:H:i:s',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function presensiStatus()
    {
    }
}
