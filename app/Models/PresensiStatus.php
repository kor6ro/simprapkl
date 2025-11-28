<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiStatus extends Model
{
    use HasFactory;
    protected $table = 'presensi_status';
    protected $fillable = ['kode', 'status', 'kategori'];

    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }
}