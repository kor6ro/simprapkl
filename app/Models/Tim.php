<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tim extends Model
{
    use HasFactory;

    protected $table = 'tim';
    protected $guarded = ['id'];

    /**
     * Accessor untuk mendapatkan teks status yang sudah diformat.
     */
    protected function statusText(): Attribute
    {
        return Attribute::make(
            get: fn () => ucfirst(str_replace('_', ' ', $this->status_approval ?? 'N/A')),
        );
    }

    /**
     * Accessor untuk mendapatkan kelas badge Bootstrap berdasarkan status.
     */
   protected function statusBadgeClass(): Attribute
{
    return Attribute::make(
        get: function () {
            switch (trim($this->status_approval)) {
                case 'tugas_selesai':
                    return 'bg-success'; // Hijau (Sudah Benar)
                case 'perlu_revisi':
                    return 'bg-danger text-white'; // Merah (Sudah Benar)
                case 'belum_selesai':
                    return 'bg-warning'; // Oranye (Sudah Diperbaiki)
                default:
                    return 'bg-secondary';
            }
        },
    );
}
    /**
     * Relasi ke User yang menjadi Ketua Tim.
     */
    public function ketua(): BelongsToMany
    {
         return $this->belongsToMany(User::class, 'tim_ketua', 'tim_id', 'user_id');
    }

    /**
     * Relasi ke Divisi.
     */
    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    /**
     * Relasi ke Laporan yang dimiliki oleh Tim.
     */
    public function laporan(): HasMany
    {
        return $this->hasMany(Laporan::class, 'tim_id');
    }

    public function anggota(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tim_anggota', 'tim_id', 'user_id');
    }
    //   public function approver(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'approver_id');
    // }
}