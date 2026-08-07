@extends('emails.layout')

@section('subject', 'Profile Update Status - Hydrox Portal')

@section('content')
    <div style="margin-bottom: 24px;">
        @if ($profileChange->status === 'approved')
            <span class="badge badge-success">Status: Changes Approved</span>
        @else
            <span class="badge badge-danger">Status: Changes Rejected</span>
        @endif
    </div>

    <h2 style="color: #0f172a; font-size: 20px; font-weight: 800; margin: 0 0 12px 0;">Hello {{ $staff->first_name }},</h2>

    @if ($profileChange->status === 'approved')
        <p style="margin-top: 0;">Your recent profile update request and uploaded compliance documentation have been <strong>Approved</strong> and applied to your official Hydrox subcontractor profile.</p>
    @else
        <p style="margin-top: 0;">Your recent profile update request was reviewed by administration and <strong>Not Approved</strong> at this time.</p>
        @if ($profileChange->admin_notes)
            <div class="details-box" style="border-left: 4px solid #be123c;">
                <div class="details-label" style="color: #be123c;">Admin Notes</div>
                <div class="details-value" style="font-weight: 500; color: #334155; margin-top: 6px;">{{ $profileChange->admin_notes }}</div>
            </div>
        @endif
    @endif

    <div style="text-align: center; margin: 28px 0;">
        <a href="{{ route('staff-portal.profile') }}" class="btn-action">View Your Subcontractor Profile</a>
    </div>

    <p style="margin-top: 24px;">Thank you for keeping your profile and compliance documents up to date with Hydrox Facility Management.</p>
@endsection
