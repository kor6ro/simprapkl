<?php

namespace App\Notifications;

use App\Models\Tim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SiswaDitambahkanKeTim extends Notification
{
    use Queueable;

    protected $tim;

    public function __construct(Tim $tim)
    {
        $this->tim = $tim;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Simpan di database untuk ikon lonceng
    }

    public function toArray(object $notifiable): array
    {
        $divisiName = $this->tim->divisi->nama_divisi;
        $ketuaNames = $this->tim->ketua->pluck('name')->implode(', ');

        return [
            'title'   => 'Tugas Baru!',
            'message' => "Anda telah ditambahkan ke tim {$divisiName} (PIC: {$ketuaNames}) untuk tugas hari ini.",
            'url'     => route('admin.tim.index'),
            'icon'    => 'fas fa-users text-info', // Ikon tim
        ];
    }
}