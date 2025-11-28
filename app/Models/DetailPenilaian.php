<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenilaian extends Model
{
    use HasFactory;

    protected $table = 'detail_penilaian';

    protected $fillable = [
        'penilaian_id',
        'variabel',
        'nilai',
    ];

    public function penilaian()
    {
        return $this->belongsTo(Penilaian::class);
    }
}