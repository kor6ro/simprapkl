<?php

namespace App\Notifications;

use App\Models\Laporan;
// Hapus 'use Illuminate\Bus\Queueable;'
// Hapus 'use Illuminate\Contracts\Queue\ShouldQueue;'
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class LaporanDisetujui extends Notification
{
    // Hapus 'use Queueable;'

    /**
     * @var Laporan
     */
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
            'title'   => 'Laporan Disetujui!',
            'message' => "Kerja bagus! Laporan Anda untuk tim {$divisiName} telah disetujui oleh {$approverName}.",
            'url'     => route('admin.laporan.index', ['tim_id' => $this->laporan->tim_id]),
            'icon'    => 'fas fa-check-circle text-success',
        ];
    }
}