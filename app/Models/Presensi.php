<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Presensi extends Model
{
    use HasFactory;
    protected $table = 'presensi';
    protected $guarded = ['id'];

    protected $casts = [
        'presensi_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function presensiStatus()
    {
        return $this->belongsTo(PresensiStatus::class, 'presensi_status_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function getCanRequestEditAttribute(): bool
    {
        return $this->status === 'Alpa' && !in_array($this->approval_status, ['pending', 'pending_update']);
    }
}