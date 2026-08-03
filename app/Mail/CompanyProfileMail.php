<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyProfileMail extends Mailable
{
    use Queueable, SerializesModels;

    public const SUBJECT = 'Hydrox Facility Management Company Profile';

    public const PROFILE_URL = 'https://profile.hydrox.au';

    public function __construct(public array $business) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: self::SUBJECT);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.company-profile');
    }
}
