<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingTugas extends Model
{
    protected $fillable = ['divisi', 'deskripsi', 'tanggal'];

    public function ketua() {
        return $this->belongsTo(User::class, 'ketua_id');
    }
    public function anggota() {
        return $this->belongsToMany(User::class, 'setting_tugas_anggota', 'setting_tugas_id', 'user_id');
    }
}
