<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceSite;
use App\Models\InvoiceSiteShift;
use App\Models\SiteAssignmentDelivery;
use App\Models\SiteShiftAssignment;
use App\Models\StaffMember;
use App\Services\SiteAssignmentNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SiteAssignmentController extends Controller
{
    public function index(): View
    {
        return view('admin.site-assignments.index', [
            'sites' => InvoiceSite::query()
                ->where('active', true)
                ->with(['shifts' => fn ($query) => $query
                    ->where('active', true)
                    ->with('activeAssignments.staffMember')])
                ->withCount([
                    'shifts' => fn ($query) => $query->where('active', true),
                    'assignments as active_assignments_count' => fn ($query) => $query->where('site_shift_assignments.active', true),
                ])
                ->orderBy('site_code')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(InvoiceSite $site): View
    {
        abort_unless($site->active, 404);

        return view('admin.site-assignments.show', [
            'site' => $site->load(['shifts' => fn ($query) => $query->where('active', true)->with('activeAssignments.staffMember')]),
            'staffMembers' => StaffMember::query()
                ->where('active', true)
                ->where('staff_status', 'active')
                ->whereNull('archived_at')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(),
            'recentDeliveries' => SiteAssignmentDelivery::with('assignment.staffMember', 'assignment.shift.site')
                ->whereHas('assignment.shift', fn ($query) => $query->where('invoice_site_id', $site->id))
                ->latest('attempted_at')
                ->limit(20)
                ->get(),
        ]);
    }

    public function update(Request $request, InvoiceSiteShift $shift, SiteAssignmentNotificationService $notifications): RedirectResponse
    {
        abort_unless($shift->active && $shift->site?->active, 404);

        $data = $request->validate([
            'staff_ids' => ['nullable', 'array'],
            'staff_ids.*' => ['integer', 'exists:staff_members,id'],
        ]);

        $selectedIds = StaffMember::query()
            ->whereIn('id', $data['staff_ids'] ?? [])
            ->where('active', true)
            ->where('staff_status', 'active')
            ->whereNull('archived_at')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
        $current = $shift->assignments()->where('active', true)->get()->keyBy('staff_member_id');
        $addedIds = array_values(array_diff($selectedIds, $current->keys()->map(fn ($id): int => (int) $id)->all()));
        $removedIds = array_values(array_diff($current->keys()->map(fn ($id): int => (int) $id)->all(), $selectedIds));

        [$added, $removed] = DB::transaction(function () use ($shift, $addedIds, $removedIds): array {
            $added = collect($addedIds)->map(function (int $staffId) use ($shift): SiteShiftAssignment {
                $assignment = $shift->assignments()->firstOrNew(['staff_member_id' => $staffId]);
                $assignment->fill([
                    'assigned_by_user_id' => Auth::id(),
                    'active' => true,
                    'assigned_at' => now(),
                    'unassigned_at' => null,
                ])->save();
                return $assignment;
            });
            $removed = $shift->assignments()->whereIn('staff_member_id', $removedIds)->where('active', true)->get();
            $removed->each(fn (SiteShiftAssignment $assignment) => $assignment->update(['active' => false, 'unassigned_at' => now()]));
            return [$added, $removed];
        });

        $summary = ['sent' => 0, 'skipped' => 0, 'failed' => 0];
        foreach ($added as $assignment) $this->mergeSummary($summary, $notifications->send($assignment, 'assigned'));
        foreach ($removed as $assignment) $this->mergeSummary($summary, $notifications->send($assignment, 'removed'));

        return back()->with('status', "Assignments saved. Added: {$added->count()}. Removed: {$removed->count()}. Sent: {$summary['sent']}. Skipped: {$summary['skipped']}. Failed: {$summary['failed']}.");
    }

    private function mergeSummary(array &$summary, array $result): void
    {
        foreach ($summary as $key => $value) $summary[$key] += $result[$key] ?? 0;
    }
}
