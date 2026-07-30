<!doctype html>
<html lang="en">
<body style="margin:0;background:#eef4f7;font-family:Arial,sans-serif;color:#06162f">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:30px 15px;background:#eef4f7">
    <tr><td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;overflow:hidden;border-radius:22px;background:#fff">
            <tr><td style="padding:34px;background:linear-gradient(120deg,#06162f,#07527d);color:#fff">
                <div style="margin:0 0 24px">
                    <img src="{{ asset('images/hydrox-email-logo.png') }}"
                         width="220"
                         alt="Hydrox Facility Management"
                         style="display:block;width:220px;max-width:70%;height:auto;border:0;background:#ffffff;border-radius:12px;padding:10px 14px">
                </div>
                <div style="font-size:12px;font-weight:700;letter-spacing:2px;color:#72efd0">HYDROX FACILITY MANAGEMENT</div>
                <h1 style="margin:12px 0 8px;font-size:30px">Your request is being reviewed</h1>
                <p style="margin:0;color:#d9e9f2;line-height:1.6">Thanks {{ $booking->customer_name }}. We have safely received your booking request.</p>
            </td></tr>
            <tr><td style="padding:32px">
                <div style="padding:20px;border:1px solid #b9eadd;border-radius:16px;background:#ecfff9;text-align:center">
                    <div style="font-size:11px;font-weight:700;letter-spacing:2px;color:#08745a">BOOKING REQUEST CODE</div>
                    <div style="margin-top:7px;font-size:25px;font-weight:800;color:#06162f">{{ $booking->reference }}</div>
                </div>
                <p style="margin:24px 0 10px;line-height:1.7"><strong>This is a booking request only. It is not yet confirmed.</strong></p>
                <p style="margin:0 0 24px;color:#526477;line-height:1.7">Our team will review the services, location and preferred timing, then contact you to confirm availability and pricing. Please wait for our confirmation before treating the requested date as booked.</p>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="8" style="border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0">
                    <tr><td style="width:130px;color:#64748b">Services</td><td><strong>{{ collect($booking->services)->join(', ') }}</strong></td></tr>
                    @if($booking->extras)<tr><td style="color:#64748b">Extras</td><td>{{ collect($booking->extras)->join(', ') }}</td></tr>@endif
                    <tr><td style="color:#64748b">Frequency</td><td>{{ str($booking->frequency)->replace('-', ' ')->title() }}</td></tr>
                    <tr><td style="color:#64748b">Preferred time</td><td>{{ $booking->schedule_flexible ? 'Flexible' : collect([$booking->preferred_date?->format('d M Y'), $booking->preferred_time])->filter()->join(' · ') }}</td></tr>
                    <tr><td style="color:#64748b">Location</td><td>{{ collect([$booking->address, $booking->suburb, $booking->postcode])->filter()->join(', ') }}</td></tr>
                </table>
                <p style="margin:26px 0 0;color:#526477">Need to add something? Call Hydrox on <strong>0418 222 477</strong> and quote {{ $booking->reference }}.</p>
            </td></tr>
            <tr><td style="padding:20px 32px;background:#06162f;color:#b9c9d7;font-size:12px">Hydrox Facility Management · Secure booking request</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
