<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingTugas extends Model
{
    use HasFactory;

    protected $table = 'setting_tugas';
    
    protected $fillable = [
        // 'nama_tim', // <-- HAPUS BARIS INI
        'ketua_id',
        'divisi', 
        'tanggal',
        'deskripsi'
    ];

    // ... sisa kode model biarkan sama ...
    protected $casts = [
        'tanggal' => 'date'
    ];

    public function ketua()
    {
        return $this->belongsTo(User::class, 'ketua_id');
    }

    public function anggota()
    {
        return $this->belongsToMany(
            User::class,
            'setting_tugas_anggota',
            'setting_tugas_id',
            'user_id'
        )->withTimestamps();
    }
}
