@extends('emails.layout')

@section('subject', 'Profile Update Received - Hydrox Portal')

@section('content')
    <div style="margin-bottom: 24px;">
        <span class="badge badge-info">Status: Update Pending Admin Review</span>
    </div>

    <h2 style="color: #0f172a; font-size: 20px; font-weight: 800; margin: 0 0 12px 0;">Hello {{ $staff->first_name }},</h2>

    <p style="margin-top: 0;">We have received your requested profile and compliance document update for your <strong>Hydrox Subcontractor Account</strong>.</p>

    <div class="details-box">
        <div class="details-row">
            <div class="details-label">Subcontractor Name</div>
            <div class="details-value">{{ $staff->fullName() }}</div>
        </div>
        <div class="details-row">
            <div class="details-label">Submission Timestamp</div>
            <div class="details-value">{{ $profileChange->submitted_at?->format('d M Y, h:i A') ?: now()->format('d M Y') }}</div>
        </div>
        <div class="details-row">
            <div class="details-label">Updated Fields / Documents</div>
            <div class="details-value">{{ count($profileChange->changes ?? []) }} change(s) submitted</div>
        </div>
    </div>

    <p style="margin-top: 20px;">Our administration team will review your updated details and compliance documents. You will receive an automated confirmation email as soon as your changes are verified.</p>
@endsection
