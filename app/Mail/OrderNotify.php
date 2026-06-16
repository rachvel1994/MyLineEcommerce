<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class OrderNotify extends Mailable
{
    use Queueable, SerializesModels;

    public Product $product;
    protected ?string $pdfPath;

    public function __construct(Product $product, ?string $pdfPath = null)
    {
        $this->product = $product;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'შეკვეთის დეტალები #' . $this->product->order_id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-notify',
            with: [
                'product' => $this->product,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->pdfPath && Storage::disk('local')->exists($this->pdfPath)) {
            $attachments[] = Attachment::fromPath(Storage::disk('local')->path($this->pdfPath))
                ->as('invoice-' . $this->product->id . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
