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
    public function send(Booking $booking): void
    {
        $errors = [];

        try {
            Mail::to($booking->email)->send(new BookingRequestReceived($booking));
            $booking->forceFill(['customer_email_sent_at' => now()])->save();
        } catch (Throwable $exception) {
            report($exception);
            $errors[] = 'Customer receipt: '.$exception->getMessage();
        }

        try {
            $business = SystemSetting::businessInformation();
            Mail::to($business['email'] ?: config('app.company_email'))->send(new NewBookingRequest($booking));
            $booking->forceFill(['admin_email_sent_at' => now()])->save();
        } catch (Throwable $exception) {
            report($exception);
            $errors[] = 'Hydrox alert: '.$exception->getMessage();
        }

        $booking->forceFill(['email_error' => $errors ? implode("\n", $errors) : null])->save();
    }
}
