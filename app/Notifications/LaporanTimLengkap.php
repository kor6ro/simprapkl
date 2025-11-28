<?php

namespace App\Notifications;

use App\Models\Tim; // <-- Tambahkan ini
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LaporanTimLengkap extends Notification
{
    use Queueable;

    protected $tim;

    /**
     * Buat instance notifikasi baru.
     */
    public function __construct(Tim $tim)
    {
        $this->tim = $tim;
    }

    /**
     * Tentukan channel pengiriman notifikasi.
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Simpan di database untuk ikon lonceng
    }

    /**
     * Definisikan data yang akan disimpan di database.
     */
    public function toArray(object $notifiable): array
{
    $divisiName = $this->tim->divisi->nama_divisi;

    // [PERBAIKAN] Ubah isi pesan notifikasi
    $newMessage = "Semua anggota tim anda hari ini telah mengirimkan laporan. Saatnya untuk ditinjau!";

    return [
        'message' => $newMessage,
        'url' => route('admin.laporan.index', ['tim_id' => $this->tim->id]),
        'icon' => 'fas fa-check-double text-success',
    ];
}
}