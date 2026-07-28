<?php

namespace App\Services;

use App\Models\AreaPhoto;
use App\Models\CompletionReport;
use App\Models\IssuePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportStoreService
{
    public function save(array $data, Request $request, ?CompletionReport $report = null): CompletionReport
    {
        return DB::transaction(function () use ($data, $request, $report): CompletionReport {
            $reportData = Arr::only($data, [
                'job_id',
                'completion_date',
                'technician',
                'weather',
                'overall_condition',
                'work_summary',
                'recommendations',
                'internal_notes',
                'technician_name',
                'technician_signed_at',
                'customer_name',
                'customer_signed_at',
            ]);

            $reportData['customer_present'] = (bool) ($data['customer_present'] ?? false);
            $reportData['checklist'] = array_values($data['checklist'] ?? []);

            foreach (['technician_signature', 'customer_signature'] as $signatureField) {
                if (! empty($data[$signatureField])) {
                    $pathField = str_replace('signature', 'signature_path', $signatureField);
                    $reportData[$pathField] = $this->storeSignature($data[$signatureField], $signatureField);
                }
            }

            if (! $report && ! empty($reportData['job_id'])) {
                $report = CompletionReport::where('job_id', $reportData['job_id'])->first();
            }

            $report = $report
                ? tap($report)->update($reportData)
                : CompletionReport::create($reportData);

            $this->removeAreaPhotos($report, $data['remove_area_photo_ids'] ?? []);
            $this->removeIssuePhotos($report, $data['remove_issue_photo_ids'] ?? []);

            $keptAreaIds = [];

            $report->products()->delete();

            foreach (($data['areas'] ?? []) as $index => $areaData) {
                $areaPayload = [
                    'area_name' => $areaData['area_name'],
                    'description' => $areaData['description'] ?? null,
                    'completion_notes' => $areaData['completion_notes'] ?? null,
                    'photo_notes' => $areaData['photo_notes'] ?? null,
                    'sort_order' => $index,
                ];

                if ($this->cleaningAreasHaveVideoUrl()) {
                    $areaPayload['video_url'] = $areaData['video_url'] ?? null;
                }

                $area = null;

                if (! empty($areaData['id'])) {
                    $area = $report->areas()->whereKey($areaData['id'])->first();
                }

                $area = $area
                    ? tap($area)->update($areaPayload)
                    : $report->areas()->create($areaPayload);

                $keptAreaIds[] = $area->id;

                foreach (['before' => 'before_photos', 'after' => 'after_photos'] as $type => $field) {
                    foreach ($request->file("areas.$index.$field", []) as $photo) {
                        $area->photos()->create([
                            'type' => $type,
                            'path' => $photo->store('area-photos/'.Str::slug($report->report_number), 'public'),
                            'original_name' => $photo->getClientOriginalName(),
                            'notes' => $areaData['photo_notes'] ?? null,
                            'captured_at' => now(),
                        ]);
                    }
                }

                foreach (['before' => 'before_photo_tokens', 'after' => 'after_photo_tokens'] as $type => $field) {
                    foreach (($areaData[$field] ?? []) as $tokenPayload) {
                        $stored = $this->storeUploadedPhotoToken($tokenPayload, 'area-photos/'.Str::slug($report->report_number));

                        if ($stored) {
                            $area->photos()->create([
                                'type' => $type,
                                'path' => $stored['path'],
                                'original_name' => $stored['name'],
                                'notes' => $areaData['photo_notes'] ?? null,
                                'captured_at' => now(),
                            ]);
                        }
                    }
                }

            }

            $report->areas()->whereNotIn('id', $keptAreaIds)->delete();

            $keptIssueIds = [];

            foreach (($data['issues'] ?? []) as $index => $issue) {
                if (! empty($issue['issue_type'])) {
                    $issuePayload = Arr::except($issue, ['id', 'photos']);
                    $storedIssue = null;

                    if (! empty($issue['id'])) {
                        $storedIssue = $report->issues()->whereKey($issue['id'])->first();
                    }

                    $storedIssue = $storedIssue
                        ? tap($storedIssue)->update($issuePayload)
                        : $report->issues()->create($issuePayload);

                    $keptIssueIds[] = $storedIssue->id;

                    foreach ($request->file("issues.$index.photos", []) as $photo) {
                        $path = $photo->store('issue-photos/'.Str::slug($report->report_number), 'public');

                        $storedIssue->photos()->create([
                            'path' => $path,
                            'original_name' => $photo->getClientOriginalName(),
                            'captured_at' => now(),
                        ]);

                        $storedIssue->photo_path ??= $path;
                    }

                    foreach (($issue['photo_tokens'] ?? []) as $tokenPayload) {
                        $stored = $this->storeUploadedPhotoToken($tokenPayload, 'issue-photos/'.Str::slug($report->report_number));

                        if ($stored) {
                            $storedIssue->photos()->create([
                                'path' => $stored['path'],
                                'original_name' => $stored['name'],
                                'captured_at' => now(),
                            ]);

                            $storedIssue->photo_path ??= $stored['path'];
                        }
                    }

                    $storedIssue->photo_path = $storedIssue->photos()->oldest('id')->value('path');
                    $storedIssue->save();
                }
            }

            $report->issues()->whereNotIn('id', $keptIssueIds)->delete();

            $report->job()->update(['status' => 'Completed']);

            return $report->fresh(['job.customer', 'areas.photos', 'products', 'issues.photos']);
        });
    }

    private function storeSignature(string $dataUri, string $name): string
    {
        [$meta, $encoded] = explode(',', $dataUri, 2);
        $extension = str_contains($meta, 'image/jpeg') ? 'jpg' : 'png';
        $path = 'signatures/'.$name.'-'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, base64_decode($encoded));

        return $path;
    }

    private function cleaningAreasHaveVideoUrl(): bool
    {
        static $hasColumn = null;

        return $hasColumn ??= Schema::hasColumn('cleaning_areas', 'video_url');
    }

    /**
     * @return array{path: string, name: string}|null
     */
    private function storeUploadedPhotoToken(string $payload, string $destinationDir): ?array
    {
        $data = json_decode($payload, true);

        if (! is_array($data) || empty($data['token'])) {
            return null;
        }

        $token = $data['token'];

        if (! str_starts_with($token, 'report-temp/') || ! Storage::disk('public')->exists($token)) {
            return null;
        }

        $destination = trim($destinationDir, '/').'/'.basename($token);
        Storage::disk('public')->move($token, $destination);

        return [
            'path' => $destination,
            'name' => $data['name'] ?? 'Uploaded photo',
        ];
    }

    /**
     * @param  array<int, int|string>  $photoIds
     */
    private function removeAreaPhotos(CompletionReport $report, array $photoIds): void
    {
        if ($photoIds === []) {
            return;
        }

        AreaPhoto::query()
            ->whereIn('id', $photoIds)
            ->whereHas('area', fn ($query) => $query->where('completion_report_id', $report->id))
            ->get()
            ->each(function (AreaPhoto $photo): void {
                Storage::disk('public')->delete($photo->path);
                $photo->delete();
            });
    }

    /**
     * @param  array<int, int|string>  $photoIds
     */
    private function removeIssuePhotos(CompletionReport $report, array $photoIds): void
    {
        if ($photoIds === []) {
            return;
        }

        IssuePhoto::query()
            ->whereIn('id', $photoIds)
            ->whereHas('issue', fn ($query) => $query->where('completion_report_id', $report->id))
            ->get()
            ->each(function (IssuePhoto $photo): void {
                Storage::disk('public')->delete($photo->path);
                $photo->delete();
            });
    }
}
