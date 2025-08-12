<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensi';

    protected $fillable = [
        'user_id',
        'sesi',
        'jam_presensi',
        'tanggal_presensi',
        'bukti_foto',
        'keterangan',
        'status',
        'presensi_status_id',
        'approval_status',
        'requested_status',
        'approval_notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_presensi' => 'date',
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

    // Relasi dengan admin yang approve
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessor untuk status yang konsisten
    public function getStatusDisplayAttribute()
    {
        // Jika ada approval pending, tampilkan status yang diminta
        if ($this->approval_status === 'pending') {
            return $this->requested_status . ' (Menunggu Persetujuan)';
        }

        return $this->presensiStatus?->status ?? $this->status;
    }

    // Accessor untuk warna badge
    public function getStatusColorAttribute()
    {
        // Jika status pending approval, gunakan warning
        if ($this->approval_status === 'pending') {
            return 'warning';
        }

        $colors = [
            'Tepat Waktu' => 'success',
            'Terlambat' => 'warning',
            'Sangat Terlambat' => 'danger',
            'Terlalu Awal' => 'info',
            'Izin' => 'info',
            'Sakit' => 'secondary',
            'Alpa' => 'danger',
        ];

        $status = $this->presensiStatus?->status ?? $this->status;
        return $colors[$status] ?? 'light';
    }

    // Scope untuk data pending approval
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending');
    }

    // Scope untuk data hari ini
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal_presensi', today());
    }

    // Scope untuk user tertentu
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Method untuk check apakah bisa request edit
    public function canRequestEdit()
    {
        return $this->status === 'Alpa' &&
            in_array($this->approval_status, [null, 'rejected']);
    }

    // Method untuk check apakah membutuhkan approval admin
    public function needsApproval()
    {
        return $this->approval_status === 'pending';
    }
}
