<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Presensi;

class HasilApprovalIzin extends Notification
{
    use Queueable;

    protected $presensi;
    protected $isApproved;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Presensi $presensi, bool $isApproved)
    {
        $this->presensi = $presensi;
        $this->isApproved = $isApproved;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $statusTeks = $this->isApproved ? 'disetujui' : 'ditolak';
        $tanggal = $this->presensi->presensi_at->translatedFormat('d F Y');
        $icon = $this->isApproved ? 'far fa-calendar-check text-success' : 'far fa-calendar-times text-danger';

        return [
            'message' => "Pengajuan {$this->presensi->status} Anda pada tanggal {$tanggal} telah {$statusTeks}.",
            'url'     => route('presensi.index'),
            'icon'    => $icon
        ];
    }
}