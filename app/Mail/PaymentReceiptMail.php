<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public Payment $payment;
    public string $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment, string $pdfContent)
    {
        $this->payment = $payment;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Ricevuta Pagamento #{$this->payment->receipt_number} - {$this->payment->school->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-receipt',
            with: [
                'payment' => $this->payment,
                'school' => $this->payment->school,
                'student' => $this->payment->user,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, "Ricevuta-{$this->payment->receipt_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
