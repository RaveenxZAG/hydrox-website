<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ \App\Mail\CompanyProfileMail::SUBJECT }}</title>
</head>
<body style="margin:0;padding:0;background:#eef4f7;font-family:Arial,Helvetica,sans-serif;color:#07142e;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:28px 14px;background:#eef4f7;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;overflow:hidden;border-radius:20px;background:#ffffff;box-shadow:0 16px 40px rgba(15,35,55,.10);">
                <tr>
                    <td align="center" style="padding:30px 24px 26px;">
                        <img src="{{ asset('images/hydrox-email-logo-transparent.png') }}" width="220" alt="Hydrox Facility Management" style="display:block;width:220px;max-width:70%;height:auto;border:0;margin:0 auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:38px 34px;background:#062446;color:#ffffff;text-align:center;">
                        <div style="font-size:12px;font-weight:700;letter-spacing:2px;color:#72efd0;">PROFESSIONAL CLEANING SERVICES</div>
                        <h1 style="margin:12px 0 10px;font-size:30px;line-height:1.2;color:#ffffff;">Discover Hydrox Facility Management</h1>
                        <p style="margin:0 auto;max-width:520px;font-size:16px;line-height:1.7;color:#d9e9f2;">Learn more about our company, capabilities, and commitment to dependable facility management and cleaning services.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:34px;">
                        <p style="margin:0 0 22px;font-size:16px;line-height:1.7;color:#334155;">We invite you to view the Hydrox company profile for an overview of who we are and how we can support your organisation.</p>
                        <div style="text-align:center;margin:28px 0;">
                            <a href="{{ \App\Mail\CompanyProfileMail::PROFILE_URL }}" style="display:inline-block;padding:14px 24px;border-radius:999px;background:#0082c9;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;">View Company Profile</a>
                        </div>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="8" style="border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;font-size:14px;line-height:1.55;">
                            <tr><td style="width:120px;color:#64748b;">Company</td><td style="color:#0f172a;"><strong>{{ $business['company_name'] }}</strong></td></tr>
                            @if (filled($business['website']))
                                <tr><td style="color:#64748b;">Website</td><td><a href="{{ $business['website'] }}" style="color:#0082c9;text-decoration:none;">{{ $business['website'] }}</a></td></tr>
                            @endif
                            @if (filled($business['email']))
                                <tr><td style="color:#64748b;">Email</td><td><a href="mailto:{{ $business['email'] }}" style="color:#0082c9;text-decoration:none;">{{ $business['email'] }}</a></td></tr>
                            @endif
                            @if (filled($business['phone']))
                                <tr><td style="color:#64748b;">Phone</td><td style="color:#0f172a;">{{ $business['phone'] }}</td></tr>
                            @endif
                            @php
                                $address = collect([
                                    $business['address_line_1'],
                                    $business['address_line_2'],
                                    $business['address_line_3'],
                                    collect([$business['city'], $business['state'], $business['postcode']])->filter()->join(' '),
                                    $business['country'],
                                ])->filter()->join(', ');
                            @endphp
                            @if (filled($address))
                                <tr><td style="color:#64748b;">Address</td><td style="color:#0f172a;">{{ $address }}</td></tr>
                            @endif
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:22px 34px;background:#062446;color:#b9c9d7;font-size:12px;line-height:1.6;">
                        {{ $business['company_name'] }} · You can count on us.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
