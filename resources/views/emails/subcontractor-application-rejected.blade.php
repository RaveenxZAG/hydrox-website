@extends('emails.layout')

@section('subject', 'Application Update - Hydrox Subcontractor Onboarding')

@section('content')
    <div style="margin-bottom: 24px;">
        <span class="badge badge-danger">Status: Application Not Approved</span>
    </div>

    <h2 style="color: #0f172a; font-size: 20px; font-weight: 800; margin: 0 0 12px 0;">Hello {{ $onboarding->first_name }},</h2>

    <p style="margin-top: 0;">Thank you for your interest in partnering with <strong>Hydrox Facility Management</strong>.</p>
    <p>After reviewing your onboarding submission and documentation, our administrative team is unable to approve your application at this time.</p>

    @if ($onboarding->rejection_reason)
        <div class="details-box" style="border-left: 4px solid #be123c;">
            <div class="details-label" style="color: #be123c;">Reason / Feedback</div>
            <div class="details-value" style="font-weight: 500; color: #334155; margin-top: 6px;">{{ $onboarding->rejection_reason }}</div>
        </div>
    @endif

    <p style="margin-top: 24px;">If you have updated documentation or wish to clarify details regarding this decision, please feel free to reach out to our admin team at <a href="mailto:admin@hydrox.au" style="color: #0082c9;">admin@hydrox.au</a>.</p>
@endsection
