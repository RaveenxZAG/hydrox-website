@extends('emails.layout')

@section('subject', 'Application Approved - Hydrox Subcontractor Portal')

@section('content')
    <div style="margin-bottom: 24px;">
        <span class="badge badge-success">Status: Approved &amp; Active</span>
    </div>

    <h2 style="color: #0f172a; font-size: 20px; font-weight: 800; margin: 0 0 12px 0;">Congratulations {{ $onboarding->first_name }}!</h2>

    <p style="margin-top: 0;">We are pleased to inform you that your subcontractor application for <strong>Hydrox Facility Management</strong> has been <strong>Approved</strong>!</p>
    <p>Your profile is now active in our system, and your portal access has been provisioned.</p>

    <div class="details-box">
        <div class="details-row">
            <div class="details-label">Registered Subcontractor</div>
            <div class="details-value">{{ $onboarding->full_name }}</div>
        </div>
        <div class="details-row">
            <div class="details-label">Registered Email</div>
            <div class="details-value">{{ $onboarding->email }}</div>
        </div>
        <div class="details-row">
            <div class="details-label">Registered Mobile</div>
            <div class="details-value">{{ $onboarding->mobile ?: $onboarding->phone }}</div>
        </div>
    </div>

    <h3 style="color: #0f172a; font-size: 16px; font-weight: 700; margin: 24px 0 12px 0;">Accessing Your Portal</h3>
    <p style="margin: 0 0 16px 0;">You can log in to the Hydrox Subcontractor Portal using your email address or mobile number via standard secure OTP verification.</p>

    <div style="text-align: center; margin: 28px 0;">
        <a href="{{ route('staff-portal.login') }}" class="btn-action">Log in to Subcontractor Portal</a>
    </div>

    <p style="margin-top: 24px;">Via the portal, you can view your active profile, submit monthly work logs, and update your compliance documentation.</p>
@endsection
