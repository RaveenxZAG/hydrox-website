<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 30px 46px; }
        body { color: #10243a; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.45; }
        h1, h2, h3 { margin: 0; }
        h1 { color: #061b35; font-size: 25px; line-height: 1.18; }
        h2 { border-bottom: 1px solid #dbe6ed; color: #0082c9; font-size: 14px; margin-bottom: 10px; padding-bottom: 6px; text-transform: uppercase; }
        h3 { font-size: 12px; margin-bottom: 6px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 7px 6px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; color: #475569; font-size: 10px; text-transform: uppercase; }
        .header { background: #f8fafc; border: 1px solid #d9e2ec; border-radius: 12px; margin-bottom: 20px; overflow: hidden; padding: 18px 18px 14px; }
        .header-accent { background: #0082c9; height: 4px; margin: -18px -18px 16px; }
        .brand-logo { background: #fff; border: 1px solid #d9e2ec; border-radius: 10px; height: 86px; object-fit: contain; padding: 5px; width: 86px; }
        .brand-cell { border: 0; width: 100px; }
        .title-kicker { color: #0082c9; font-size: 10px; font-weight: bold; letter-spacing: 1.2px; margin-bottom: 5px; text-transform: uppercase; }
        .contact-line { color: #475569; font-size: 10px; margin-top: 8px; }
        .meta-strip { background: #fff; border: 1px solid #d9e2ec; border-radius: 9px; margin-top: 14px; padding: 7px 10px; }
        .meta-strip table { table-layout: fixed; }
        .meta-strip td { border: 0; padding: 0 8px; text-align: center; width: 25%; }
        .meta-label { color: #64748b; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .meta-value { color: #111827; font-size: 10px; font-weight: bold; white-space: nowrap; }
        .muted { color: #64748b; }
        .grid { width: 100%; }
        .grid td { border: 0; padding: 4px 8px 4px 0; width: 50%; }
        .section { margin-bottom: 16px; page-break-inside: avoid; }
        .pill { background: #eaf6fc; border: 1px solid #b8dff3; border-radius: 12px; color: #07527d; display: inline-block; margin: 2px; padding: 4px 8px; }
        .video-section { background: #eaf6fc; border: 1px solid #b8dff3; border-radius: 8px; margin-bottom: 16px; padding: 10px; page-break-inside: avoid; }
        .video-section h2 { border-bottom: 0; color: #07527d; margin-bottom: 8px; padding-bottom: 0; }
        .video-link-row { border-top: 1px solid #b8dff3; padding: 8px 0; }
        .video-link-row:first-of-type { border-top: 0; padding-top: 0; }
        .area { border: 1px solid #d9e2ec; border-radius: 8px; margin-bottom: 14px; padding: 10px; page-break-inside: avoid; }
        .video-button { background: #0082c9; border-radius: 6px; color: #fff; display: inline-block; font-size: 10px; font-weight: bold; padding: 7px 10px; text-decoration: none; }
        .video-url { color: #64748b; font-size: 8px; margin-top: 4px; word-break: break-all; }
        .photos { table-layout: fixed; }
        .photos td { border: 0; padding: 6px; width: 50%; }
        .photo-frame { border: 1px solid #d9e2ec; border-radius: 5px; min-height: 132px; padding: 5px; text-align: center; }
        .photo { display: block; height: auto; margin: 0 auto; max-height: 190px; max-width: 100%; width: auto; }
        .issue-card { border: 1px solid #d9e2ec; border-radius: 8px; margin-bottom: 12px; padding: 10px; page-break-inside: avoid; }
        .issue-summary td { border: 0; padding: 3px 8px 5px 0; }
        .issue-label { color: #64748b; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .issue-photo-table { table-layout: fixed; margin-top: 8px; }
        .issue-photo-table td { border: 0; padding: 5px; text-align: center; width: 33.333%; }
        .issue-photo-frame { border: 1px solid #d9e2ec; border-radius: 5px; min-height: 92px; padding: 5px; text-align: center; }
        .issue-photo { display: block; height: auto; margin: 0 auto; max-height: 112px; max-width: 100%; width: auto; }
        .signature { border: 1px solid #d9e2ec; border-radius: 6px; height: 76px; object-fit: contain; width: 230px; }
        .footer { bottom: -28px; color: #64748b; font-size: 9px; left: 0; position: fixed; right: 0; text-align: center; }
    </style>
</head>
<body>
    <div class="footer">{{ $company['name'] }} · {{ $company['phone'] }} · {{ $company['phone_secondary'] }} · {{ $company['email'] }} · Confidential</div>

    <div class="header">
        <div class="header-accent"></div>
        <table>
            <tr>
                <td class="brand-cell">
                    @if (is_file($company['logo']))
                        <img class="brand-logo" src="{{ $company['logo'] }}">
                    @endif
                </td>
                <td style="border:0;">
                    <div class="title-kicker">{{ $company['name'] }}</div>
                    <h1>Quality Service<br>Completion Report</h1>
                    <div class="contact-line">{{ $company['address'] }}</div>
                    <div class="contact-line">{{ $company['phone'] }} &nbsp; | &nbsp; {{ $company['phone_secondary'] }}<br>{{ $company['email'] }}</div>
                </td>
            </tr>
        </table>
        <div class="meta-strip">
            <table>
                <tr>
                    <td><div class="meta-label">Report</div><div class="meta-value">{{ $report->report_number }}</div></td>
                    <td><div class="meta-label">Job</div><div class="meta-value">{{ $report->job?->job_number ?: 'Missing job' }}</div></td>
                    <td><div class="meta-label">Date</div><div class="meta-value">{{ $report->completion_date?->format('d M Y') }}</div></td>
                    <td><div class="meta-label">Status</div><div class="meta-value">{{ $report->status }}</div></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>Client and Service Information</h2>
        <table class="grid">
            <tr><td><strong>Client</strong><br>{{ $report->job?->customer?->customer_name ?: 'Missing client' }}</td><td><strong>Company</strong><br>{{ $report->job?->customer?->company ?: 'N/A' }}</td></tr>
            <tr><td><strong>Contact</strong><br>{{ $report->job?->customer?->phone ?: 'N/A' }} · {{ $report->job?->customer?->email ?: 'N/A' }}</td><td><strong>Address</strong><br>{{ $report->job?->customer ? collect([$report->job->customer->address, $report->job->customer->suburb, $report->job->customer->state, $report->job->customer->postcode])->filter()->join(', ') : 'N/A' }}</td></tr>
            <tr><td><strong>Service</strong><br>{{ $report->job?->cleaning_service ?: 'Missing service' }}</td><td><strong>Prepared By</strong><br>{{ $report->technician }}</td></tr>
            <tr><td><strong>Overall Condition</strong><br>{{ $report->overall_condition }}</td><td><strong>Client Present</strong><br>{{ $report->customer_present ? 'Yes' : 'No' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Checklist</h2>
        @foreach ($report->checklist ?? [] as $item)
            <span class="pill">{{ $item }}</span>
        @endforeach
    </div>

    <div class="section">
        <h2>Work Summary</h2>
        <p>{{ $report->work_summary }}</p>
        <h3>Recommendations</h3>
        <p>{{ $report->recommendations }}</p>
    </div>

    @php
        $areasWithVideos = $report->areas->filter(fn ($area) => filled($area->video_url));
    @endphp

    @if ($areasWithVideos->isNotEmpty())
        <div class="video-section">
            <h2>Video Folder Links</h2>
            @foreach ($areasWithVideos as $area)
                <div class="video-link-row">
                    <strong>{{ $area->area_name }}</strong><br>
                    <a class="video-button" href="{{ $area->video_url }}">Open Video Folder</a>
                    <div class="video-url">{{ $area->video_url }}</div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($report->issues->isNotEmpty())
        <div class="section">
            <h2>Issues Found</h2>
            @foreach ($report->issues as $issue)
                <div class="issue-card">
                    <table class="issue-summary">
                        <tr>
                            <td style="width:35%;"><span class="issue-label">Type</span><br>{{ $issue->issue_type }}</td>
                            <td style="width:15%;"><span class="issue-label">Severity</span><br>{{ $issue->severity }}</td>
                            <td style="width:50%;"><span class="issue-label">Recommendation</span><br>{{ $issue->recommendation ?: 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td colspan="3"><span class="issue-label">Description</span><br>{{ $issue->description ?: 'N/A' }}</td>
                        </tr>
                    </table>

                    @if ($issue->photos->isNotEmpty())
                        <table class="issue-photo-table">
                            @foreach ($issue->photos->chunk(3) as $row)
                                <tr>
                                    @foreach ($row as $photo)
                                        @php $src = $photo->pdf_src; @endphp
                                        <td>
                                            @if ($src && is_file($src))
                                                <div class="issue-photo-frame"><img class="issue-photo" src="{{ $src }}"></div>
                                            @endif
                                        </td>
                                    @endforeach
                                    @for ($i = $row->count(); $i < 3; $i++)
                                        <td></td>
                                    @endfor
                                </tr>
                            @endforeach
                        </table>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="section">
        <h2>Cleaning Areas</h2>
        @foreach ($report->areas as $area)
            <div class="area">
                <h3>{{ $area->area_name }}</h3>
                <p class="muted">{{ $area->description }}</p>
                <table class="photos">
                    <tr><th>Before</th><th>After</th></tr>
                    @php $max = max($area->beforePhotos->count(), $area->afterPhotos->count()); @endphp
                    @for ($i = 0; $i < $max; $i++)
                        <tr>
                            <td>
                                @if($photo = $area->beforePhotos->values()->get($i))
                                    @php $src = $photo->pdf_src; @endphp
                                    @if ($src && is_file($src))
                                        <div class="photo-frame"><img class="photo" src="{{ $src }}"></div>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($photo = $area->afterPhotos->values()->get($i))
                                    @php $src = $photo->pdf_src; @endphp
                                    @if ($src && is_file($src))
                                        <div class="photo-frame"><img class="photo" src="{{ $src }}"></div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endfor
                </table>
                <p><strong>Notes:</strong> {{ $area->completion_notes }}</p>
            </div>
        @endforeach
    </div>

    <div class="section">
        <h2>Declaration</h2>
        <table class="grid">
            <tr>
                @php
                    $signaturePath = $report->technician_signature_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($report->technician_signature_path)
                        ? \Illuminate\Support\Facades\Storage::disk('public')->path($report->technician_signature_path)
                        : null;
                @endphp
                <td><strong>Prepared By</strong><br>{{ $report->technician_name ?: $report->technician }}<br>@if($signaturePath)<img class="signature" src="{{ $signaturePath }}">@endif</td>
                <td><strong>Date</strong><br>{{ $report->technician_signed_at?->format('d M Y') ?: $report->completion_date?->format('d M Y') }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
