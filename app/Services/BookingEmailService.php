<?php

namespace App\Services;

use App\Mail\BookingRequestReceived;
use App\Mail\NewBookingRequest;
use App\Models\Booking;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BookingEmailService
{
    public function __construct(private readonly MicrosoftGraphMailService $graph) {}

    public function send(Booking $booking, bool $sendCustomer = true, bool $sendAdmin = true): void
    {
        $errors = [];

        if ($sendCustomer) {
            try {
                $mail = new BookingRequestReceived($booking);
                $this->deliver(
                    $booking->email,
                    "We received your Hydrox request: {$booking->reference}",
                    $mail->render(),
                    $mail
                );
                $booking->forceFill(['customer_email_sent_at' => now()])->save();
            } catch (Throwable $exception) {
                report($exception);
                $errors[] = 'Customer receipt: '.$exception->getMessage();
            }
        }

        if ($sendAdmin) {
            try {
                $business = SystemSetting::businessInformation();
                $recipient = $business['email'] ?: config('app.company_email');
                $mail = new NewBookingRequest($booking);
                $this->deliver(
                    $recipient,
                    "New Hydrox booking request: {$booking->reference}",
                    $mail->render(),
                    $mail
                );
                $booking->forceFill(['admin_email_sent_at' => now()])->save();
            } catch (Throwable $exception) {
                report($exception);
                $errors[] = 'Hydrox alert: '.$exception->getMessage();
            }
        }

        $booking->forceFill(['email_error' => $errors ? implode("\n", $errors) : null])->save();
    }

    private function deliver(string $recipient, string $subject, string $html, object $fallbackMail): void
    {
        if ($this->graph->configured()) {
            $this->graph->send($recipient, $subject, $html);

            return;
        }

        Mail::to($recipient)->send($fallbackMail);
    }
}
