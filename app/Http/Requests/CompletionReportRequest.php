<?php

namespace App\Http\Requests;

use App\Models\CompletionReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class CompletionReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $report = $this->route('report');
        $reportId = $report instanceof CompletionReport ? $report->id : null;

        return [
            'job_id' => [
                'required',
                'exists:jobs,id',
                Rule::unique('completion_reports', 'job_id')->ignore($reportId),
            ],
            'completion_date' => ['required', 'date'],
            'technician' => ['required', 'string', 'max:255'],
            'weather' => ['nullable', 'string', 'max:255'],
            'overall_condition' => ['required', 'in:Excellent,Good,Fair,Poor'],
            'customer_present' => ['nullable', 'boolean'],
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['string', 'max:255'],
            'work_summary' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'areas' => ['required', 'array', 'min:1'],
            'areas.*.id' => ['nullable', 'integer', 'exists:cleaning_areas,id'],
            'areas.*.area_name' => ['required', 'string', 'max:255'],
            'areas.*.description' => ['nullable', 'string'],
            'areas.*.completion_notes' => ['nullable', 'string'],
            'areas.*.photo_notes' => ['nullable', 'string'],
            'areas.*.video_url' => ['nullable', 'url', 'max:2048'],
            'areas.*.before_photos.*' => ['nullable', 'image', 'max:10240'],
            'areas.*.after_photos.*' => ['nullable', 'image', 'max:10240'],
            'areas.*.before_photo_tokens.*' => ['nullable', 'string'],
            'areas.*.after_photo_tokens.*' => ['nullable', 'string'],
            'expected_file_upload_count' => ['nullable', 'integer', 'min:0'],
            'remove_area_photo_ids' => ['nullable', 'array'],
            'remove_area_photo_ids.*' => ['integer', 'exists:area_photos,id'],
            'issues' => ['nullable', 'array'],
            'issues.*.id' => ['nullable', 'integer', 'exists:issue_founds,id'],
            'issues.*.issue_type' => ['nullable', 'string', 'max:255'],
            'issues.*.description' => ['nullable', 'string'],
            'issues.*.severity' => ['nullable', 'in:Low,Medium,High,Critical'],
            'issues.*.photos' => ['nullable', 'array'],
            'issues.*.photos.*' => ['nullable', 'image', 'max:10240'],
            'issues.*.photo_tokens.*' => ['nullable', 'string'],
            'issues.*.recommendation' => ['nullable', 'string'],
            'remove_issue_photo_ids' => ['nullable', 'array'],
            'remove_issue_photo_ids.*' => ['integer', 'exists:issue_photos,id'],
            'technician_name' => ['nullable', 'string', 'max:255'],
            'technician_signature' => ['nullable', 'string'],
            'technician_signed_at' => ['nullable', 'date'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_signature' => ['nullable', 'string'],
            'customer_signed_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $expected = $this->integer('expected_file_upload_count');

            if ($expected <= 0) {
                return;
            }

            $actual = $this->uploadedFileCount($this->allFiles());

            if ($actual < $expected) {
                $maxFiles = (int) ini_get('max_file_uploads');

                $validator->errors()->add(
                    'expected_file_upload_count',
                    "Only {$actual} of {$expected} selected photos reached the server. Please upload fewer photos at once or increase the server max_file_uploads setting"
                        .($maxFiles > 0 ? " above {$maxFiles}" : '')
                        .'.'
                );
            }
        });
    }

    private function uploadedFileCount(array $files): int
    {
        $count = 0;

        array_walk_recursive($files, function ($file) use (&$count): void {
            if ($file) {
                $count++;
            }
        });

        return $count;
    }

    public function messages(): array
    {
        return [
            'job_id.unique' => 'This job already has a completion report. Please open the existing report and edit it instead.',
        ];
    }
}
