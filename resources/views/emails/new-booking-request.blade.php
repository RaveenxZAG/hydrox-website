<!doctype html>
<html lang="en">
<body style="margin:0;background:#eef4f7;font-family:Arial,sans-serif;color:#06162f">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:30px 15px;background:#eef4f7">
    <tr><td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;border-radius:20px;background:#fff">
            <tr><td align="center" style="padding:30px;background:#06162f;color:#fff;text-align:center">
                <div style="margin:0 auto 22px;text-align:center">
                    <img src="{{ asset('images/hydrox-email-logo.png') }}"
                         width="210"
                         alt="Hydrox Facility Management"
                         style="display:block;width:210px;max-width:70%;height:auto;border:0;margin:0 auto">
                </div>
                <div style="font-size:12px;font-weight:700;letter-spacing:2px;color:#72efd0">NEW WEBSITE REQUEST</div>
                <h1 style="margin:10px 0 4px">{{ $booking->reference }}</h1>
                <p style="margin:0;color:#d9e9f2">{{ $booking->customer_name }} · {{ $booking->phone }}</p>
            </td></tr>
            <tr><td style="padding:30px">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="9">
                    <tr><td style="width:140px;color:#64748b">Customer</td><td><strong>{{ $booking->customer_name }}</strong></td></tr>
                    <tr><td style="color:#64748b">Email</td><td>{{ $booking->email }}</td></tr>
                    <tr><td style="color:#64748b">Phone</td><td>{{ $booking->phone }}</td></tr>
                    <tr><td style="color:#64748b">Services</td><td>{{ collect($booking->services)->join(', ') }}</td></tr>
                    <tr><td style="color:#64748b">Extras</td><td>{{ collect($booking->extras)->join(', ') ?: 'None' }}</td></tr>
                    <tr><td style="color:#64748b">Frequency</td><td>{{ str($booking->frequency)->replace('-', ' ')->title() }}</td></tr>
                    <tr><td style="color:#64748b">Timing</td><td>{{ $booking->schedule_flexible ? 'Flexible' : collect([$booking->preferred_date?->format('d M Y'), $booking->preferred_time])->filter()->join(' · ') }}</td></tr>
                    <tr><td style="color:#64748b">Address</td><td>{{ collect([$booking->address, $booking->suburb, $booking->postcode])->filter()->join(', ') }}</td></tr>
                    <tr><td style="color:#64748b">Photos</td><td>{{ $booking->photos->count() }}</td></tr>
                    <tr><td style="color:#64748b">Notes</td><td>{!! nl2br(e($booking->notes ?: 'None')) !!}</td></tr>
                </table>
                <p style="margin:24px 0 0">
                    <a href="{{ route('bookings.show', $booking) }}" style="display:inline-block;padding:14px 22px;border-radius:999px;background:#0082c9;color:#fff;text-decoration:none;font-weight:700">Review in Hydrox Portal</a>
                </p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
