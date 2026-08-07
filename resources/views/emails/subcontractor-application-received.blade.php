@extends('emails.layout')

@section('subject', 'Application Received - Hydrox Subcontractor Onboarding')

@section('content')
    <div style="margin-bottom: 24px;">
        <span class="badge badge-info">Status: Under Review</span>
    </div>

    <h2 style="color: #0f172a; font-size: 20px; font-weight: 800; margin: 0 0 12px 0;">Hello {{ $onboarding->first_name }},</h2>

    <p style="margin-top: 0;">Thank you for submitting your subcontractor onboarding application to <strong>Hydrox Facility Management</strong>.</p>
    <p>Your application is currently <strong>Under Review</strong> by our compliance and administrative team. We are verifying your uploaded business details, insurance documentation, and qualifications.</p>

    <div class="details-box">
        <div class="details-row">
            <div class="details-label">Full Name / Contact</div>
            <div class="details-value">{{ $onboarding->full_name }}</div>
        </div>
        @if ($onboarding->legal_business_name || $onboarding->trading_name)
            <div class="details-row">
                <div class="details-label">Business Name</div>
                <div class="details-value">{{ $onboarding->legal_business_name ?: $onboarding->trading_name }}</div>
            </div>
        @endif
        <div class="details-row">
            <div class="details-label">ABN</div>
            <div class="details-value">{{ $onboarding->abn }}</div>
        </div>
        <div class="details-row">
            <div class="details-label">Submission Date</div>
            <div class="details-value">{{ $onboarding->submitted_at?->format('d M Y, h:i A') ?: now()->format('d M Y') }}</div>
        </div>
    </div>

    <h3 style="color: #0f172a; font-size: 16px; font-weight: 700; margin: 24px 0 12px 0;">Next Steps</h3>
    <ol style="padding-left: 20px; margin: 0;">
        <li style="margin-bottom: 8px;">Our management team will inspect your application &amp; documents.</li>
        <li style="margin-bottom: 8px;">If any required documents (such as insurance, Working Rights, or Police Check) are missing or require clarification, our team will reach out.</li>
        <li style="margin-bottom: 8px;">Once approved, you will receive an active subcontractor notification with access to the Hydrox Subcontractor Portal.</li>
    </ol>

    <p style="margin-top: 24px;">If you have any urgent questions regarding your application, please reach out to us directly.</p>
@endsection
