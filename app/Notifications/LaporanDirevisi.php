<?php

namespace App\Notifications;

use App\Models\Laporan;
use Illuminate\Notifications\Notification;

// Tidak ada 'use Queueable' atau 'implements ShouldQueue'

class LaporanDirevisi extends Notification
{
    /**
     * @var Laporan
     */
    protected $laporan;

    /**
     * Buat instance notifikasi baru.
     *
     * @param  Laporan  $laporan
     * @return void
     */
    public function __construct(Laporan $laporan)
    {
        $this->laporan = $laporan;
    }

    /**
     * Tentukan channel pengiriman notifikasi.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Simpan notifikasi di database
    }

    /**
     * Definisikan data yang akan disimpan di database.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray(object $notifiable): array
    {
        // Mengambil data yang relevan untuk pesan notifikasi
        $namaSiswa = $this->laporan->user->name;
        $divisiName = $this->laporan->tim->divisi->nama_divisi;

        return [
            'title'   => 'Laporan Telah Direvisi',
            'message' => "{$namaSiswa} telah mengirimkan revisi untuk laporannya di tim {$divisiName}. Mohon ditinjau kembali.",
            'url'     => route('admin.laporan.index', ['tim_id' => $this->laporan->tim_id]),
            'icon'    => 'fas fa-sync-alt text-primary', // Ikon untuk revisi/pembaruan
        ];
    }
}