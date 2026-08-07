@extends('emails.layout')

@section('subject', $notification->title)

@section('content')
    <div style="margin-bottom: 24px;">
        <span class="badge badge-info">Admin Alert</span>
    </div>

    <h2 style="color: #0f172a; font-size: 20px; font-weight: 800; margin: 0 0 12px 0;">{{ $notification->title }}</h2>

    @if ($notification->message)
        <div style="background-color: #f8fafc; border-left: 4px solid #0082c9; border-radius: 4px; padding: 16px; margin: 20px 0; font-size: 14px; white-space: pre-line; color: #334155;">
            {{ $notification->message }}
        </div>
    @endif

    @if ($notification->action_url)
        <div style="text-align: center; margin: 28px 0;">
            <a href="{{ $notification->action_url }}" class="btn-action">Open in Hydrox Portal</a>
        </div>
    @endif

    <p style="margin-top: 24px; font-size: 13px; color: #64748b;">This notification was generated automatically by the Hydrox Portal management system.</p>
@endsection
