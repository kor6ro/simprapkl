<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskBreakdown extends Model
{
    use HasFactory;

    // Tentukan nama tabel secara eksplisit
    protected $table = 'task_breakdown';

    // Izinkan kolom-kolom ini untuk diisi secara massal
    protected $fillable = [
        'applicable_date',
        'tipe',
        'deskripsi_tugas',
        'task_breakdown',
    ];
}