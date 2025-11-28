<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property \Carbon\Carbon $awal_periode
 * @property \Carbon\Carbon $akhir_periode
 */
class PeriodePkl extends Model
{
    use HasFactory;

    protected $table = 'periode_pkl';

    protected $fillable = ['awal_periode', 'akhir_periode'];

    protected $casts = [
        'awal_periode' => 'date',
        'akhir_periode' => 'date',
    ];

    /**
     * NAMA METHOD INI HARUS "users" (JAMAK)
     * agar bisa dipanggil oleh controller dengan ->load('users')
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'periode_pkl_user');
    }
}