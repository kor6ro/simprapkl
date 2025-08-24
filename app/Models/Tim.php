<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tim extends Model
{
    use HasFactory;

    protected $table = 'tim';

    protected $fillable = [
        'ketua_id',
        'divisi_id',
        'tanggal',
    ];

    /**
     * Relasi ke User sebagai Ketua Tim.
     */
    public function ketua()
    {
        return $this->belongsTo(User::class, 'ketua_id');
    }

    /**
     * Relasi ke Divisi.
     */
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    /**
     * Relasi ke User sebagai Anggota Tim.
     */
    public function anggota()
    {
        return $this->belongsToMany(User::class, 'tim_anggota', 'tim_id', 'user_id');
    }
}