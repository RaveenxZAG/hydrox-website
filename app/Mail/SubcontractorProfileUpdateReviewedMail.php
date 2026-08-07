<?php

namespace App\Mail;

use App\Models\StaffMember;
use App\Models\StaffProfileChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubcontractorProfileUpdateReviewedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public StaffMember $staff, public StaffProfileChangeRequest $profileChange) {}

    public function envelope(): Envelope
    {
        $statusLabel = ucfirst($this->profileChange->status);

        return new Envelope(
            subject: "Profile Update {$statusLabel} - Hydrox Subcontractor Portal ({$this->staff->fullName()})"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subcontractor-profile-update-reviewed'
        );
    }
}
