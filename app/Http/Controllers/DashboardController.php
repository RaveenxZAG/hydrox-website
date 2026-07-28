<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\StaffInvoiceSubmission;
use App\Models\StaffMember;
use App\Services\StaffPortal\InvoicePeriodService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(InvoicePeriodService $periods): View
    {
        $window = $periods->currentWindow();
        $periodDate = $window['period']->startOfMonth()->toDateString();
        $submittedStaffIds = StaffInvoiceSubmission::whereDate('invoice_period', $periodDate)->pluck('staff_member_id');

        return view('dashboard.index', [
            'stats' => [
                'new_bookings' => Booking::where('status', 'new')->count(),
                'upcoming_bookings' => Booking::whereNotIn('status', ['completed', 'cancelled'])
                    ->whereDate('preferred_date', '>=', today())
                    ->count(),
                'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
                'active_subcontractors' => StaffMember::where('staff_status', 'active')->count(),
            ],
            'invoiceStats' => [
                'period' => $window['label'],
                'submitted' => $submittedStaffIds->count(),
                'missing' => StaffMember::where('staff_status', 'active')
                    ->where('portal_access_enabled', true)
                    ->where('invoicing_enabled', true)
                    ->whereNotIn('id', $submittedStaffIds)
                    ->count(),
            ],
            'recentBookings' => Booking::latest()->limit(8)->get(),
        ]);
    }
}
