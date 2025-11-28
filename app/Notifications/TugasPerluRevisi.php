<?php

namespace App\Notifications;

use App\Models\Tim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class TugasPerluRevisi extends Notification
{
    use Queueable;

    protected $tim;

    public function __construct(Tim $tim)
    {
        $this->tim = $tim;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Menyimpan notifikasi di database untuk ikon lonceng
    }

    public function toArray(object $notifiable): array
    {
        $divisiName = $this->tim->divisi->nama_divisi;
        $approverName = Auth::user()->name;

        return [
            'title'   => 'Tugas Perlu Revisi',
            'message' => "Tugas tim {$divisiName} dikembalikan untuk revisi oleh {$approverName}.",
            'url'     => route('admin.laporan.index', ['tim_id' => $this->tim->id]),
            'icon'    => 'fas fa-exclamation-triangle text-warning',
        ];
    }
}