<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Welcome to Hydrox Facility Management</title>
    <style>
        :root {
            color-scheme: light only;
            supported-color-schemes: light only;
        }

        body,
        table,
        td,
        div,
        p,
        h1,
        h2,
        h3,
        a,
        strong {
            color-scheme: light only !important;
            forced-color-adjust: none !important;
        }

        [data-light-email] {
            background-color: #ffffff !important;
            color: #07142e !important;
        }

        [data-light-page] {
            background-color: #eef6fb !important;
            color: #07142e !important;
        }

        [data-light-hero],
        [data-light-footer] {
            background-color: #062446 !important;
            color: #ffffff !important;
        }
    </style>
</head>
<body data-light-page style="margin:0; padding:0; background:#eef6fb !important; font-family:Arial, Helvetica, sans-serif; color:#07142e !important;">
    <table data-light-page role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef6fb !important; padding:28px 14px; color:#07142e !important;">
        <tr>
            <td align="center">
                <table data-light-email role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:760px; background:#ffffff !important; color:#07142e !important; border-radius:18px; overflow:hidden; box-shadow:0 18px 45px rgba(15, 35, 55, 0.12);">
                    <tr>
                        <td align="center" style="padding:28px 24px 24px;">
                            <img src="{{ $logoSrc ?? asset('images/hydrox-email-logo.png') }}" width="210" alt="Hydrox Facility Management" style="display:block; max-width:210px; height:auto; border:0; outline:none; text-decoration:none;">
                        </td>
                    </tr>

                    <tr>
                        <td data-light-hero style="background:#062446 !important; padding:44px 38px; color:#ffffff !important;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="vertical-align:middle;">
                                        <p style="margin:0 0 12px; font-size:16px; font-weight:bold; color:#7dd8f0;">Application approved</p>
                                        <h1 style="margin:0; font-size:36px; line-height:1.08; letter-spacing:-0.5px;">Welcome to<br>Hydrox Facility Management!</h1>
                                        <p style="margin:18px 0 0; max-width:410px; font-size:18px; line-height:1.55; color:#e8f6ff;">Hi {{ $firstName }}, we are excited to have you on board and look forward to working with you.</p>
                                        <div style="margin-top:26px; width:62px; height:4px; border-radius:999px; background:#0ea5c6;"></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:38px 32px 18px;">
                            <h2 style="margin:0; font-size:27px; line-height:1.2; color:#07142e !important;">What happens next?</h2>
                            <p style="margin:14px auto 0; max-width:590px; font-size:16px; line-height:1.6; color:#334155;">A separate ServiceM8 invitation will arrive shortly by SMS or email. Please download the ServiceM8 app and use the invitation link to complete your account setup.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 30px 10px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    @foreach ([
                                        ['1', 'Download the App', 'Download the ServiceM8 app from the App Store or Google Play.'],
                                        ['2', 'Open the Invitation', 'Open the ServiceM8 invitation message and follow the setup link.'],
                                        ['3', 'Complete Setup', 'Finish your account setup and sign in using the link provided.'],
                                        ['4', 'Start Working', 'Once activated, you will receive job assignments and important work information.'],
                                    ] as [$number, $title, $copy])
                                        <td width="25%" style="padding:8px; vertical-align:top;">
                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#ffffff !important; border:1px solid #d9e7f1; border-radius:14px; color:#07142e !important;">
                                                <tr>
                                                    <td align="center" style="padding:20px 12px 18px;">
                                                        <div style="display:inline-block; width:34px; height:34px; border-radius:999px; background:#0d7ee8; color:#ffffff; font-size:16px; line-height:34px; font-weight:bold;">{{ $number }}</div>
                                                        <h3 style="margin:14px 0 8px; font-size:15px; line-height:1.25; color:#07142e !important;">{{ $title }}</h3>
                                                        <p style="margin:0; font-size:12px; line-height:1.55; color:#334155;">{{ $copy }}</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 38px 0;">
                            <div style="border-radius:16px; background:#e4f3ff !important; padding:20px 24px; font-size:15px; line-height:1.6; color:#0f2747 !important;">
                                If you do not receive your ServiceM8 invitation within 24 hours, or if you experience any issues during setup, please contact our support team.
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 38px 36px;">
                            <h2 style="margin:0 0 8px; font-size:22px; color:#07142e !important;">Welcome to Hydrox Facility Management.</h2>
                            <p style="margin:0 0 24px; font-size:16px; line-height:1.6; color:#334155;">We look forward to achieving great things together.</p>
                            <p style="margin:0; font-size:15px; line-height:1.6; color:#334155;">Kind regards,<br><strong style="color:#0d6fe8;">Hydrox Facility Management Administration Team</strong></p>
                        </td>
                    </tr>

                    <tr>
                        <td data-light-footer style="background:#062446 !important; padding:26px 34px; color:#ffffff !important;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="font-size:14px; line-height:1.6;">
                                        <strong style="font-size:17px;">Hydrox Facility Management</strong><br>
                                        Professional Cleaning Services<br>
                                        You can count on.
                                    </td>
                                    <td align="right" style="font-size:14px; line-height:1.7;">
                                        <strong>Need Help?</strong><br>
                                        <a href="mailto:{{ $supportEmail }}" style="color:#79d8ff; text-decoration:none;">{{ $supportEmail }}</a><br>
                                        <a href="tel:{{ preg_replace('/\s+/', '', $supportPhone) }}" style="color:#79d8ff; text-decoration:none;">{{ $supportPhone }}</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
