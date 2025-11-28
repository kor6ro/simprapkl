<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User; // Import User model

class SiswaMengajukanIzin extends Notification
{
    use Queueable;

    protected $siswa;
    protected $presensi;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $siswa, $presensi)
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
        return ['database']; // Kita akan simpan notifikasi ini di database
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        // Data yang akan disimpan sebagai JSON di database
        return [
            'siswa_name' => $this->siswa->name,
            'message' => $this->siswa->name . ' mengajukan ' . $this->presensi->status . '.',
            'url' => route('presensi.index', ['filter_approval' => 'pending_all']), // URL tujuan saat notif diklik
            'icon' => 'far fa-envelope-open text-warning'
        ];
    }
}