<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('subject', 'Hydrox Portal Notification')</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table {
            border-collapse: collapse;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f1f5f9;
            padding: 40px 0;
        }
        .main-card {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #10243a;
            background-image: linear-gradient(135deg, #10243a 0%, #004c7d 100%);
            padding: 24px 32px;
            text-align: left;
            border-bottom: 3px solid #0082c9;
        }
        .header-logo {
            max-height: 46px;
            height: 46px;
            width: auto;
            display: block;
            border: 0;
        }
        .header-title {
            color: #ffffff;
            font-size: 22px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .header-subtitle {
            color: #79c5e9;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .content {
            padding: 32px;
            font-size: 15px;
            line-height: 1.6;
            color: #334155;
        }
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-info {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-danger {
            background-color: #ffe4e6;
            color: #be123c;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #b45309;
        }
        .details-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .details-row {
            margin-bottom: 10px;
        }
        .details-row:last-child {
            margin-bottom: 0;
        }
        .details-label {
            font-weight: 700;
            color: #64748b;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .details-value {
            color: #0f172a;
            font-size: 15px;
            font-weight: 600;
            margin-top: 2px;
        }
        .btn-action {
            display: inline-block;
            background-color: #0082c9;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 14px;
            padding: 12px 28px;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: 0 2px 6px rgba(0, 130, 201, 0.3);
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px 32px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
        }
        .footer a {
            color: #0082c9;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td align="center">
                    <div class="main-card">
                        <div class="header">
                            @php
                                $logoFile = public_path('images/hydrox-email-logo-transparent.png');
                                $logoSrc = file_exists($logoFile)
                                    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile))
                                    : config('app.url') . '/images/hydrox-email-logo-transparent.png';
                            @endphp
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td valign="middle" align="left">
                                        <img src="{{ $logoSrc }}" alt="Hydrox Facility Management" class="header-logo" style="max-height: 46px; height: 46px; width: auto; display: block; border: 0;" />
                                    </td>
                                    <td valign="middle" align="right">
                                        <div class="header-subtitle">Subcontractor Portal</div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="content">
                            @yield('content')
                        </div>
                        <div class="footer">
                            <p style="margin: 0 0 8px 0;"><strong>Hydrox Facility Management</strong></p>
                            <p style="margin: 0 0 8px 0;">Commercial &amp; Residential Cleaning Solutions</p>
                            <p style="margin: 0;">Need support? Email us at <a href="mailto:admin@hydrox.au">admin@hydrox.au</a></p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
