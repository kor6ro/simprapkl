<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PresensiStatus extends Model
{
    use HasFactory;

    protected $table = 'presensi_status';

    protected $fillable = [
        'status',
    ];

    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }
}
