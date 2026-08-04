<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ \App\Mail\CompanyProfileMail::SUBJECT }}</title>
    <style>
        @media only screen and (max-width: 640px) {
            .email-shell { width: 100% !important; }
            .mobile-pad { padding-left: 22px !important; padding-right: 22px !important; }
            .stack-cell { display: block !important; width: 100% !important; box-sizing: border-box !important; }
            .stack-gap { height: 12px !important; }
            .mobile-title { font-size: 30px !important; line-height: 36px !important; }
            .mobile-center { text-align: center !important; }
        }
    </style>
</head>
@php
    $companyName = $business['company_name'] ?: 'Hydrox Facility Management';
    $website = rtrim($business['website'] ?: 'https://hydrox.au', '/');
    $email = $business['email'] ?: 'admin@hydrox.au';
    $phone = $business['phone'] ?: '0418 222 477';
    $phoneHref = 'tel:'.preg_replace('/[^+0-9]/', '', $phone);
    $quoteUrl = 'https://hydrox.au/contact-us/';
    $address = collect([
        $business['address_line_1'], $business['address_line_2'], $business['address_line_3'],
        collect([$business['city'], $business['state'], $business['postcode']])->filter()->join(' '), $business['country'],
    ])->filter()->join(', ');
    $services = [
        ['Commercial & Industrial Cleaning', 'Offices, schools, retail properties, warehouses, industrial facilities and shared commercial spaces.'],
        ['Residential Cleaning', 'Regular, deep, move-in, move-out and tailored home-cleaning arrangements.'],
        ['NDIS & Aged-Care Cleaning', 'Respectful cleaning support shaped around accessibility, comfort and individual hygiene needs.'],
        ['Floor & Surface Care', 'Floor scrubbing, polishing, carpet extraction, steam cleaning, tile and grout care.'],
        ['Window & High-Access Cleaning', 'Interior and exterior glass cleaning and planned high-access services, subject to assessment.'],
        ['Pressure Washing', 'Paths, entrances, concrete, driveways and selected commercial external surfaces.'],
        ['Gardening & Grounds', 'Lawn mowing, edge trimming, garden maintenance and recurring outdoor presentation.'],
        ['Additional Facility Services', 'Washrooms, consumables, waste areas, deep cleans, builders cleans and tailored support.'],
    ];
@endphp
<body style="margin:0;padding:0;background:#eaf1f5;font-family:Arial,Helvetica,sans-serif;color:#172033;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">Discover reliable, professional and tailored cleaning and facility-management solutions from Hydrox Facility Management.</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#eaf1f5;">
    <tr><td align="center" style="padding:24px 10px;">
        <table class="email-shell" role="presentation" width="640" cellspacing="0" cellpadding="0" border="0" style="width:640px;max-width:640px;background:#ffffff;">
            <tr>
                <td class="mobile-pad" style="padding:20px 32px;background:#ffffff;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
                        <td class="stack-cell mobile-center" width="50%"><img src="{{ asset('images/hydrox-email-logo-transparent.png') }}" width="190" height="64" alt="Hydrox Facility Management" style="display:block;width:190px;height:auto;border:0;max-width:100%;"></td>
                        <td class="stack-cell mobile-center" width="50%" align="right" style="font-size:13px;line-height:21px;color:#526477;"><a href="{{ $website }}" style="color:#007ebf;text-decoration:none;font-weight:bold;">hydrox.au</a><br><a href="{{ $phoneHref }}" style="color:#526477;text-decoration:none;">{{ $phone }}</a></td>
                    </tr></table>
                </td>
            </tr>
            <tr><td><img src="{{ asset('images/company-profile-email/hydrox-team-hero.jpg') }}" width="640" height="410" alt="Professional Hydrox facility-management team in a modern commercial building" style="display:block;width:100%;max-width:640px;height:auto;border:0;background:#dce8ef;"></td></tr>
            <tr>
                <td class="mobile-pad" style="padding:42px 46px 44px;background:#061b35;text-align:center;color:#ffffff;">
                    <p style="margin:0 0 12px;font-size:12px;line-height:18px;font-weight:bold;letter-spacing:2px;color:#67e7c0;">CLEANING · PROPERTY CARE · FACILITY SUPPORT</p>
                    <h1 class="mobile-title" style="margin:0 0 17px;font-size:38px;line-height:44px;color:#ffffff;">Professional Facility Solutions Built Around Your Organisation</h1>
                    <p style="margin:0 auto 26px;max-width:530px;font-size:16px;line-height:26px;color:#d6e4ef;">Hydrox Facility Management provides dependable cleaning, property maintenance and specialised facility services tailored to commercial, residential and supported-care environments across Melbourne.</p>
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center"><tr><td bgcolor="#0082c9" style="border-radius:28px;"><a href="{{ \App\Mail\CompanyProfileMail::PROFILE_URL }}" style="display:inline-block;padding:15px 28px;border-radius:28px;color:#ffffff;text-decoration:none;font-size:14px;line-height:18px;font-weight:bold;letter-spacing:.4px;">VIEW OUR COMPANY PROFILE</a></td></tr></table>
                    <p style="margin:12px 0 0;font-size:12px;line-height:18px;color:#9fb5c8;">Opens securely in your web browser.</p>
                </td>
            </tr>
            <tr>
                <td class="mobile-pad" style="padding:42px 42px 34px;">
                    <p style="margin:0 0 8px;font-size:12px;line-height:18px;font-weight:bold;letter-spacing:1.5px;color:#0082c9;">A RELIABLE FACILITY PARTNER</p>
                    <h2 style="margin:0 0 16px;font-size:27px;line-height:34px;color:#071b35;">Cleaner, Safer and Better-Presented Facilities</h2>
                    <p style="margin:0 0 14px;font-size:16px;line-height:26px;color:#45566a;">Hello,</p>
                    <p style="margin:0 0 14px;font-size:16px;line-height:26px;color:#45566a;">Every property has different operating hours, risks, surfaces and service expectations. Hydrox works with organisations to create practical, site-specific programs that support presentation, hygiene and smooth day-to-day operations.</p>
                    <p style="margin:0;font-size:16px;line-height:26px;color:#45566a;">Our approach centres on reliability, quality, clear communication, flexible scheduling and consistent service delivery.</p>
                </td>
            </tr>
            <tr><td class="mobile-pad" style="padding:0 42px 42px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
                    <td class="stack-cell" width="50%" valign="top"><img src="{{ asset('images/company-profile-email/commercial-floor-care.jpg') }}" width="270" height="185" alt="Commercial floor cleaning with professional equipment" style="display:block;width:100%;height:auto;border:0;border-radius:12px;background:#e5edf2;"></td>
                    <td class="stack-gap" width="18"></td>
                    <td class="stack-cell" width="50%" valign="middle" style="padding:18px 20px;background:#eff8fb;border-radius:12px;"><p style="margin:0 0 8px;font-size:18px;line-height:24px;font-weight:bold;color:#071b35;">Planned around your site</p><p style="margin:0;font-size:14px;line-height:23px;color:#526477;">Service frequency, access, safety requirements and priority areas are considered before work begins.</p></td>
                </tr></table>
            </td></tr>
            <tr>
                <td class="mobile-pad" style="padding:40px 34px;background:#f5f8fa;">
                    <p style="margin:0 0 8px;text-align:center;font-size:12px;line-height:18px;font-weight:bold;letter-spacing:1.5px;color:#0082c9;">SERVICE CAPABILITIES</p>
                    <h2 style="margin:0 0 26px;text-align:center;font-size:27px;line-height:34px;color:#071b35;">Practical Support for Every Environment</h2>
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                        @foreach (array_chunk($services, 2) as $row)
                            <tr>
                                @foreach ($row as $service)
                                    <td class="stack-cell" width="50%" valign="top" style="padding:8px;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td style="padding:20px;background:#ffffff;border:1px solid #dfe8ee;border-radius:12px;"><div style="width:28px;height:4px;margin-bottom:13px;background:#11d394;"></div><h3 style="margin:0 0 8px;font-size:16px;line-height:22px;color:#071b35;">{{ $service[0] }}</h3><p style="margin:0;font-size:13px;line-height:21px;color:#607183;">{{ $service[1] }}</p></td></tr></table></td>
                                @endforeach
                                @if (count($row) === 1)<td class="stack-cell" width="50%"></td>@endif
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
            <tr><td><img src="{{ asset('images/company-profile-email/specialised-carpet-care.jpg') }}" width="640" height="425" alt="Specialist commercial carpet extraction service" style="display:block;width:100%;height:auto;border:0;background:#dce8ef;"></td></tr>
            <tr>
                <td class="mobile-pad" style="padding:42px;">
                    <h2 style="margin:0 0 22px;font-size:27px;line-height:34px;text-align:center;color:#071b35;">Why Organisations Choose Hydrox</h2>
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size:15px;line-height:23px;color:#3f5164;">
                        @foreach (array_chunk(['Tailored service plans', 'Flexible recurring schedules', 'Trained and properly equipped personnel', 'Police-checked and insured team members', 'Professional-grade products and equipment', 'Responsive communication and support'], 2) as $benefits)
                            <tr>@foreach ($benefits as $benefit)<td class="stack-cell" width="50%" valign="top" style="padding:8px 10px;"><span style="color:#11d394;font-weight:bold;">✓</span>&nbsp; {{ $benefit }}</td>@endforeach</tr>
                        @endforeach
                    </table>
                </td>
            </tr>
            <tr>
                <td class="mobile-pad" style="padding:40px 34px;background:#eaf6fc;">
                    <p style="margin:0 0 8px;text-align:center;font-size:12px;line-height:18px;font-weight:bold;letter-spacing:1.5px;color:#07527d;">OUR SERVICE PROCESS</p>
                    <h2 style="margin:0 0 26px;text-align:center;font-size:27px;line-height:34px;color:#071b35;">Clear from Consultation to Delivery</h2>
                    @foreach ([['1', 'Initial Consultation', 'We discuss your property, services, schedule and operating requirements.'], ['2', 'Site Assessment', 'Where required, we assess access, risks, surfaces and the service scope.'], ['3', 'Tailored Proposal', 'You receive a clear proposal covering frequency, inclusions and pricing.'], ['4', 'Service & Support', 'Scheduled work is delivered with ongoing communication and quality monitoring.']] as $step)
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:12px;"><tr><td width="42" valign="top"><div style="width:34px;height:34px;line-height:34px;border-radius:50%;background:#071b35;color:#ffffff;text-align:center;font-size:14px;font-weight:bold;">{{ $step[0] }}</div></td><td valign="top"><p style="margin:0 0 3px;font-size:16px;line-height:22px;font-weight:bold;color:#071b35;">{{ $step[1] }}</p><p style="margin:0;font-size:14px;line-height:22px;color:#526477;">{{ $step[2] }}</p></td></tr></table>
                    @endforeach
                </td>
            </tr>
            <tr><td><img src="{{ asset('images/company-profile-email/grounds-maintenance.jpg') }}" width="640" height="356" alt="Professional grounds maintenance at a commercial property" style="display:block;width:100%;height:auto;border:0;background:#dce8ef;"></td></tr>
            <tr>
                <td class="mobile-pad" style="padding:42px;background:#071b35;text-align:center;color:#ffffff;">
                    <h2 style="margin:0 0 13px;font-size:28px;line-height:35px;color:#ffffff;">Explore Our Complete Capabilities</h2>
                    <p style="margin:0 auto 24px;max-width:520px;font-size:15px;line-height:25px;color:#cbd9e5;">View the Hydrox company profile to learn more about our services, equipment, operating approach and available facility solutions.</p>
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center"><tr><td bgcolor="#11d394" style="border-radius:28px;"><a href="{{ \App\Mail\CompanyProfileMail::PROFILE_URL }}" style="display:inline-block;padding:15px 29px;border-radius:28px;color:#04291f;text-decoration:none;font-size:14px;line-height:18px;font-weight:bold;">OPEN COMPANY PROFILE</a></td></tr></table>
                    <p style="margin:14px 0 0;font-size:12px;line-height:18px;"><a href="{{ \App\Mail\CompanyProfileMail::PROFILE_URL }}" style="color:#9fc8df;word-break:break-all;">{{ \App\Mail\CompanyProfileMail::PROFILE_URL }}</a></p>
                </td>
            </tr>
            <tr>
                <td class="mobile-pad" style="padding:42px;text-align:center;">
                    <h2 style="margin:0 0 13px;font-size:27px;line-height:34px;color:#071b35;">Let’s Discuss Your Facility Requirements</h2>
                    <p style="margin:0 auto 24px;max-width:520px;font-size:15px;line-height:25px;color:#526477;">Whether you need regular cleaning, specialised floor care, outdoor property maintenance or a complete service arrangement, our team can prepare a solution suited to your site.</p>
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center"><tr><td bgcolor="#0082c9" style="border-radius:26px;"><a href="{{ $quoteUrl }}" style="display:inline-block;padding:14px 24px;border-radius:26px;color:#ffffff;text-decoration:none;font-size:14px;line-height:18px;font-weight:bold;">REQUEST A FREE QUOTE</a></td><td width="10"></td><td bgcolor="#eef4f7" style="border-radius:26px;"><a href="{{ $phoneHref }}" style="display:inline-block;padding:14px 21px;border-radius:26px;color:#071b35;text-decoration:none;font-size:14px;line-height:18px;font-weight:bold;">CALL {{ $phone }}</a></td></tr></table>
                    <p style="margin:24px 0 0;font-size:14px;line-height:23px;color:#526477;"><a href="{{ $website }}" style="color:#0082c9;text-decoration:none;">{{ $website }}</a> &nbsp;·&nbsp; <a href="mailto:{{ $email }}" style="color:#0082c9;text-decoration:none;">{{ $email }}</a><br>Melbourne and surrounding areas, Victoria</p>
                    <p style="margin:28px 0 0;font-size:15px;line-height:25px;color:#45566a;">Thank you for taking the time to learn more about {{ $companyName }}. We look forward to the opportunity to support your organisation.</p>
                    <p style="margin:18px 0 0;font-size:15px;line-height:24px;color:#071b35;"><strong>Kind regards,<br>The Hydrox Facility Management Team</strong></p>
                </td>
            </tr>
            <tr>
                <td class="mobile-pad" style="padding:28px 34px;background:#051426;text-align:center;color:#9fb0bf;font-size:12px;line-height:20px;">
                    <img src="{{ asset('images/hydrox-email-logo-transparent.png') }}" width="160" height="54" alt="Hydrox Facility Management" style="display:block;width:160px;height:auto;max-width:60%;margin:0 auto 15px;border:0;">
                    <p style="margin:0 0 8px;"><a href="{{ $website }}" style="color:#79c5e9;text-decoration:none;">{{ $website }}</a> · <a href="mailto:{{ $email }}" style="color:#79c5e9;text-decoration:none;">{{ $email }}</a> · {{ $phone }}</p>
                    @if (filled($address))<p style="margin:0 0 8px;">{{ $address }}</p>@endif
                    <p style="margin:0 0 8px;"><a href="https://hydrox.au/privacy-policy/" style="color:#9fc8df;">Privacy Policy</a></p>
                    <p style="margin:0;">You are receiving this email because your organisation may benefit from Hydrox Facility Management’s cleaning or facility services. Reply with “unsubscribe” if you do not wish to receive future company-profile emails.</p>
                </td>
            </tr>
        </table>
    </td></tr>
</table>
</body>
</html>
