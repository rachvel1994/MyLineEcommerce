<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DailyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $pdfPath) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'დღიური გაყიდვების რეპორტი',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-report',
        );
    }

    public function attachments(): array
    {
        if (!Storage::disk('local')->exists($this->pdfPath)) {
            return [];
        }

        return [
            Attachment::fromPath(Storage::disk('local')->path($this->pdfPath))
                ->as('daily-report.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
