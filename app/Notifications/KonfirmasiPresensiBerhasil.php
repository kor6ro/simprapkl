<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Presensi;

class KonfirmasiPresensiBerhasil extends Notification
{
    use Queueable;

    protected $presensi;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Presensi $presensi)
    {
        $this->presensi = $presensi;
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
        $sesi = ucfirst($this->presensi->sesi);
        $jam = $this->presensi->presensi_at->format('H:i');

        return [
            'message' => "Presensi {$sesi} Anda berhasil dicatat pada pukul {$jam} dengan status: {$this->presensi->status}.",
            'url'     => route('presensi.index'),
            'icon'    => 'fas fa-camera-retro text-info'
        ];
    }
}