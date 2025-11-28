<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PresensiSetting extends Model
{
    use HasFactory;

    protected $table = "presensi_setting";

    protected $fillable = [
        "pagi_mulai",
        "pagi_selesai",
        "sore_mulai",
        "sore_selesai",
        "toleransi_telat",
    ];

    protected $casts = [
        'pagi_mulai'      => 'datetime:H:i',
        'pagi_selesai'    => 'datetime:H:i',
        'sore_mulai'      => 'datetime:H:i',
        'sore_selesai'    => 'datetime:H:i',
        'toleransi_telat' => 'integer',
    ];

    public function getBatasToleransiPagiAttribute(): ?string
    {

        if (!$this->pagi_selesai) return null;
        
        return Carbon::parse($this->pagi_selesai)
            ->addMinutes($this->toleransi_telat)
            ->format('H:i');
    }

    public function getBatasToleransiSoreAttribute(): ?string
    {
        if (!$this->sore_selesai) return null;

        return Carbon::parse($this->sore_selesai)
            ->addMinutes($this->toleransi_telat)
            ->format('H:i');
    }
}