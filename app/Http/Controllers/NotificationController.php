<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function open(SystemNotification $notification): RedirectResponse
    {
        if (! $notification->read_at) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return redirect()->to($notification->action_url ?: route('dashboard'));
    }

    public function markAllRead(): RedirectResponse
    {
        SystemNotification::unread()->update(['read_at' => now()]);

        return back()->with('status', 'Notifications marked as read.');
    }
}
