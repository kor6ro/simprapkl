<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisKegiatan extends Model
{
    use HasFactory;

    protected $table = 'jenis_kegiatan'; // Memberitahu Laravel nama tabelnya

    protected $fillable = [
        'nama_kegiatan',
        'deskripsi',
    ];
}