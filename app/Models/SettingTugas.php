<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingTugas extends Model
{
    protected $fillable = ['divisi', 'deskripsi', 'tanggal'];
}
