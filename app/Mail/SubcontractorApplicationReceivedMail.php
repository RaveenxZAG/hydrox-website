<?php

namespace App\Mail;

use App\Models\SubcontractorOnboarding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubcontractorApplicationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SubcontractorOnboarding $onboarding) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Application Received - Hydrox Subcontractor Onboarding ({$this->onboarding->full_name})"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subcontractor-application-received'
        );
    }
}
