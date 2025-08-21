<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Properti publik ini akan otomatis tersedia di dalam view.
     */
    public $resetUrl;

    /**
     * Buat instance pesan baru.
     *
     * @return void
     */
    public function __construct($url) // <-- Kita akan mengirim URL ke sini
    {
        $this->resetUrl = $url;
    }

    /**
     * Dapatkan amplop pesan (subjek, pengirim, penerima).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Link Reset Password Anda', // <-- Atur subjek email di sini
        );
    }

    /**
     * Dapatkan konten pesan (tampilan/template email).
     */
    public function content(): Content
    {
        // <-- Arahkan ke file Blade yang akan kita buat selanjutnya
        return new Content(
            view: 'emails.reset_password',
        );
    }

    /**
     * Dapatkan lampiran untuk pesan.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
