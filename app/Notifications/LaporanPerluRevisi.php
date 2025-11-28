<?php

namespace App\Notifications;

use App\Models\Laporan; // Gunakan model Laporan
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class LaporanPerluRevisi extends Notification
{
    use Queueable;

    protected $laporan;

    public function __construct(Laporan $laporan)
    {
        $this->laporan = $laporan;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $divisiName = $this->laporan->tim->divisi->nama_divisi;
        $approverName = Auth::user()->name;

        return [
            'title'   => 'Laporan Perlu Revisi',
            'message' => "Laporan Anda untuk tim {$divisiName} perlu direvisi. Cek catatan dari {$approverName}.",
            'url'     => route('admin.laporan.index', ['tim_id' => $this->laporan->tim_id]),
            'icon'    => 'fas fa-exclamation-triangle text-warning',
        ];
    }
}