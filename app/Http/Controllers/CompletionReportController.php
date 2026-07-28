<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompletionReportRequest;
use App\Models\CompletionReport;
use App\Models\Job;
use App\Services\ReportPdfService;
use App\Services\ReportStoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CompletionReportController extends Controller
{
    public function index(Request $request): View
    {
        $reports = CompletionReport::with('job.customer')
            ->when($request->search, function ($query, $search): void {
                $query->where('report_number', 'like', "%{$search}%")
                    ->orWhereHas('job', fn ($job) => $job->where('job_number', 'like', "%{$search}%"))
                    ->orWhereHas('job.customer', fn ($customer) => $customer->where('customer_name', 'like', "%{$search}%"));
            })
            ->when($request->date, fn ($query, $date) => $query->whereDate('completion_date', $date))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('reports.index', compact('reports'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->filled('job_id')) {
            $existingReport = CompletionReport::where('job_id', $request->integer('job_id'))->first();

            if ($existingReport) {
                return redirect()
                    ->route('reports.edit', $existingReport)
                    ->with('status', 'This job already has a report. You can update it here.');
            }
        }

        $selectedJobId = (int) old('job_id', $request->integer('job_id'));
        $selectedJob = $selectedJobId
            ? Job::with('customer')->whereDoesntHave('report')->find($selectedJobId)
            : null;

        return view('reports.create', [
            'report' => new CompletionReport(),
            'selectedJob' => $selectedJob ? $this->jobPayload($selectedJob) : null,
            'checklistItems' => CompletionReport::CHECKLIST_ITEMS,
        ]);
    }

    public function store(CompletionReportRequest $request, ReportStoreService $store, ReportPdfService $pdf): RedirectResponse
    {
        $report = $store->save($request->validated(), $request);

        try {
            $pdf->generate($report);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('reports.show', $report)
                ->with('error', 'Report saved, but the PDF could not be generated on this server. Please check cPanel PHP extensions and storage permissions.');
        }

        return redirect()->route('reports.show', $report)->with('status', 'Report saved and PDF generated.');
    }

    public function show(CompletionReport $report): View
    {
        return view('reports.show', [
            'report' => $report->load('job.customer', 'areas.beforePhotos', 'areas.afterPhotos', 'products', 'issues.photos'),
        ]);
    }

    public function edit(CompletionReport $report): View
    {
        return view('reports.edit', [
            'report' => $report->load('areas.beforePhotos', 'areas.afterPhotos', 'products', 'issues.photos'),
            'selectedJob' => ($job = Job::with('customer')->find($report->job_id)) ? $this->jobPayload($job) : null,
            'checklistItems' => CompletionReport::CHECKLIST_ITEMS,
        ]);
    }

    public function searchJobs(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q'));
        $reportId = $request->integer('report_id');
        $currentJobId = $reportId ? CompletionReport::whereKey($reportId)->value('job_id') : null;

        $jobs = Job::query()
            ->with('customer')
            ->where(function ($query) use ($currentJobId): void {
                $query->whereDoesntHave('report');

                if ($currentJobId) {
                    $query->orWhereKey($currentJobId);
                }
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('job_number', 'like', "%{$search}%")
                        ->orWhere('cleaning_service', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customer) use ($search): void {
                            $customer->where('customer_name', 'like', "%{$search}%")
                                ->orWhere('company', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('booking_date')
            ->limit(12)
            ->get()
            ->map(fn (Job $job): array => $this->jobPayload($job))
            ->values();

        return response()->json(['jobs' => $jobs]);
    }

    public function update(CompletionReportRequest $request, CompletionReport $report, ReportStoreService $store, ReportPdfService $pdf): RedirectResponse
    {
        $report = $store->save($request->validated(), $request, $report);

        try {
            $pdf->generate($report);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('reports.show', $report)
                ->with('error', 'Report updated, but the PDF could not be regenerated on this server. Please check cPanel PHP extensions and storage permissions.');
        }

        return redirect()->route('reports.show', $report)->with('status', 'Report updated and PDF regenerated.');
    }

    public function upload(Request $request): JsonResponse
    {
        $data = $request->validate([
            'photo' => ['required', 'image', 'max:10240'],
        ]);

        $file = $data['photo'];
        $name = $file->getClientOriginalName();
        $path = $file->storeAs(
            'report-temp',
            Str::uuid().'.'.$file->getClientOriginalExtension(),
            'public'
        );

        return response()->json([
            'token' => $path,
            'name' => $name,
            'url' => route('media.show', ['path' => $path], false),
        ]);
    }

    public function destroy(CompletionReport $report): RedirectResponse
    {
        $report->delete();

        return redirect()->route('reports.index')->with('status', 'Report deleted.');
    }

    public function download(CompletionReport $report, ReportPdfService $pdf)
    {
        try {
            $pdf->generate($report);
            $report->refresh();
        } catch (Throwable $exception) {
            report($exception);

            if (! $report->pdf_path || ! Storage::disk('public')->exists($report->pdf_path)) {
                return back()->with('error', 'The PDF could not be generated on this server. Please check cPanel PHP extensions and storage permissions.');
            }
        }

        return Storage::disk('public')->download($report->pdf_path, $report->report_number.'.pdf');
    }

    private function jobPayload(Job $job): array
    {
        $customer = $job->customer;

        return [
            'id' => $job->id,
            'job_number' => $job->job_number,
            'cleaning_service' => $job->cleaning_service,
            'booking_date' => $job->booking_date?->format('d M Y'),
            'start_time' => $job->start_time,
            'finish_time' => $job->finish_time,
            'technician' => $job->technician,
            'priority' => $job->priority,
            'status' => $job->status,
            'internal_notes' => $job->internal_notes,
            'customer' => [
                'name' => $customer?->customer_name,
                'company' => $customer?->company,
                'phone' => $customer?->phone,
                'email' => $customer?->email,
                'address' => collect([
                    $customer?->address,
                    $customer?->suburb,
                    $customer?->state,
                    $customer?->postcode,
                ])->filter()->implode(', '),
            ],
        ];
    }
}
