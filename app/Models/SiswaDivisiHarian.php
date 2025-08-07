<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiswaDivisiHarian extends Model
{
    protected $table = 'siswa_divisi_harian';

    protected $fillable = ['siswa_id', 'divisi', 'tanggal'];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}
