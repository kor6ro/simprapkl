<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon; // Import Carbon

class SiswaDinyatakanAlpa extends Notification
{
    use Queueable;

    protected $tanggal;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Carbon $tanggal)
    {
        $this->tanggal = $tanggal;
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
            'message' => 'Anda ditandai Alpa pada tanggal ' . $this->tanggal->translatedFormat('d F Y') . ' oleh admin.',
            'url' => route('presensi.index'),
            'icon' => 'far fa-calendar-times text-danger'
        ];
    }
}