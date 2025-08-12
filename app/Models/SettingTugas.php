<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingTugas extends Model
{
    use HasFactory;

    protected $table = 'setting_tugas';
    
    protected $fillable = [
        'ketua_id',
        'divisi', 
        'tanggal',
        'deskripsi'
    ];

    protected $casts = [
        'tanggal' => 'date'
    ];

    // Relationship dengan User (Ketua)
    public function ketua()
    {
        return $this->belongsTo(User::class, 'ketua_id');
    }

    // Relationship Many-to-Many dengan User (Anggota)
    public function anggota()
    {
        return $this->belongsToMany(
            User::class,
            'setting_tugas_anggota', // nama tabel pivot
            'setting_tugas_id',      // foreign key untuk setting_tugas
            'user_id'                // foreign key untuk user
        )->withTimestamps();
    }
}