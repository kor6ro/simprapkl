<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Presensi;

class SiswaMengajukanUlangIzin extends Notification
{
    use Queueable;

    protected $siswa;
    protected $presensi;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $siswa, Presensi $presensi)
    {
        $this->siswa = $siswa;
        $this->presensi = $presensi;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => "{$this->siswa->name} telah mengajukan ulang permohonan izinnya.",
            'url'     => route('presensi.index', ['filter_approval' => 'pending_all']),
            'icon'    => 'fas fa-redo-alt text-primary',
        ];
    }
}