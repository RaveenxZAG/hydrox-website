@csrf
@php
    $existing = [
        'max_file_uploads' => (int) ini_get('max_file_uploads'),
        'upload_url' => route('reports.uploads.store'),
        'job_search_url' => route('reports.jobs.search'),
        'report_id' => $report->id,
        'selected_job' => $selectedJob,
        'draft_key' => 'completion-report-draft-v2-'.($report->exists ? $report->id : 'new-'.request('job_id', 'blank')),
        'checklist_items' => $checklistItems,
        'checklist' => old('checklist', $report->checklist ?? []),
        'areas' => old('areas', $report->exists ? $report->areas->map(fn ($area) => [
            'id' => $area->id,
            'area_name' => $area->area_name,
            'description' => $area->description,
            'completion_notes' => $area->completion_notes,
            'photo_notes' => $area->photo_notes,
            'video_url' => $area->video_url,
            'before_photos' => $area->beforePhotos->map(fn ($photo) => [
                'id' => $photo->id,
                'url' => route('media.show', ['path' => $photo->path], false),
                'name' => $photo->original_name ?: 'Before photo',
            ])->values(),
            'after_photos' => $area->afterPhotos->map(fn ($photo) => [
                'id' => $photo->id,
                'url' => route('media.show', ['path' => $photo->path], false),
                'name' => $photo->original_name ?: 'After photo',
            ])->values(),
        ])->values() : []),
        'issues' => old('issues', $report->exists ? $report->issues->map(fn ($issue) => [
            'id' => $issue->id,
            'issue_type' => $issue->issue_type,
            'description' => $issue->description,
            'severity' => $issue->severity,
            'recommendation' => $issue->recommendation,
            'photos' => $issue->photos->map(fn ($photo) => [
                'id' => $photo->id,
                'url' => route('media.show', ['path' => $photo->path], false),
                'name' => $photo->original_name ?: 'Issue photo',
            ])->values(),
        ])->values() : []),
    ];
@endphp

<div x-data='reportBuilder(@json($existing))' data-draft-key="{{ $existing['draft_key'] }}" @report-submit-start.document="startSubmit" class="grid gap-6">
    <div x-show="submitting" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-slate-950/70 px-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-950">
            <div class="mb-4 flex items-center gap-3">
                <div class="h-10 w-10 animate-spin rounded-full border-4 border-cyan-100 border-t-cyan-600"></div>
                <div>
                    <h2 class="text-lg font-bold text-slate-950 dark:text-white">Saving report</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400" x-text="submitMessage"></p>
                </div>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div class="h-full rounded-full bg-cyan-600 transition-all duration-500 ease-out" :style="`width: ${progress}%`"></div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs font-semibold text-slate-500 dark:text-slate-400">
                <span>Uploading photos and generating PDF</span>
                <span x-text="`${progress}%`"></span>
            </div>
        </div>
    </div>

    <template x-for="photoId in removedAreaPhotoIds" :key="`area-photo-${photoId}`">
        <input type="hidden" name="remove_area_photo_ids[]" :value="photoId">
    </template>
    <template x-for="photoId in removedIssuePhotoIds" :key="`issue-photo-${photoId}`">
        <input type="hidden" name="remove_issue_photo_ids[]" :value="photoId">
    </template>
    <input type="hidden" name="expected_file_upload_count" :value="selectedFileCount()">

    <x-card>
        <h2 class="mb-4 text-lg font-bold">Job and Completion Details</h2>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <x-field class="md:col-span-2 xl:col-span-3" label="Job Number" name="job_id">
                <div class="relative" @click.outside="jobResultsOpen = false">
                    <input type="hidden" name="job_id" :value="selectedJob?.id || ''" required>
                    <input
                        class="input"
                        type="search"
                        x-model="jobSearch"
                        @input.debounce.250ms="searchJobs"
                        @focus="openJobSearch"
                        @keydown.escape="jobResultsOpen = false"
                        placeholder="Enter a job number, client name, phone, email, or service"
                        autocomplete="off"
                    >
                    <div x-show="jobResultsOpen" x-cloak class="absolute z-30 mt-2 max-h-80 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-2 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                        <p x-show="jobSearching" class="px-3 py-4 text-sm text-slate-500">Searching jobs...</p>
                        <template x-for="job in jobResults" :key="job.id">
                            <button type="button" class="block w-full rounded-lg px-3 py-3 text-left transition hover:bg-cyan-50 dark:hover:bg-slate-800" @click="selectJob(job)">
                                <span class="block font-bold text-slate-950 dark:text-white" x-text="job.job_number"></span>
                                <span class="mt-1 block text-sm text-slate-600 dark:text-slate-300" x-text="[job.customer?.name || job.customer?.company || 'Missing client', job.cleaning_service || 'No service'].join(' · ')"></span>
                                <span class="mt-1 block text-xs text-slate-400" x-text="[job.booking_date, job.status].filter(Boolean).join(' · ')"></span>
                            </button>
                        </template>
                        <p x-show="!jobSearching && jobResults.length === 0" class="px-3 py-4 text-sm text-slate-500">No available jobs found.</p>
                    </div>
                </div>
            </x-field>

            <div x-show="selectedJob" x-cloak class="md:col-span-2 xl:col-span-3 grid gap-4 rounded-xl border border-cyan-200 bg-cyan-50/70 p-4 dark:border-cyan-900 dark:bg-cyan-950/30 lg:grid-cols-2">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-cyan-700 dark:text-cyan-300">Client Details</p>
                    <p class="mt-2 font-bold text-slate-950 dark:text-white" x-text="selectedJob?.customer?.name || selectedJob?.customer?.company || 'Not recorded'"></p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300" x-text="selectedJob?.customer?.company || ''"></p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300" x-text="selectedJob?.customer?.phone || 'No phone recorded'"></p>
                    <p class="text-sm text-slate-600 dark:text-slate-300" x-text="selectedJob?.customer?.email || 'No email recorded'"></p>
                    <p class="text-sm text-slate-600 dark:text-slate-300" x-text="selectedJob?.customer?.address || 'No address recorded'"></p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-cyan-700 dark:text-cyan-300">Job Details</p>
                    <p class="mt-2 font-bold text-slate-950 dark:text-white" x-text="selectedJob?.cleaning_service || 'No service recorded'"></p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300" x-text="['Job ' + (selectedJob?.job_number || ''), selectedJob?.booking_date].filter(Boolean).join(' · ')"></p>
                    <p class="text-sm text-slate-600 dark:text-slate-300" x-text="[selectedJob?.start_time, selectedJob?.finish_time].filter(Boolean).join(' – ') || 'No time recorded'"></p>
                    <p class="text-sm text-slate-600 dark:text-slate-300" x-text="selectedJob?.technician ? 'Technician: ' + selectedJob.technician : 'No technician recorded'"></p>
                    <p class="text-sm text-slate-600 dark:text-slate-300" x-text="selectedJob?.status ? 'Status: ' + selectedJob.status : ''"></p>
                </div>
            </div>
            <x-field label="Completion Date" name="completion_date"><input class="input" type="date" name="completion_date" value="{{ old('completion_date', optional($report->completion_date)->format('Y-m-d') ?: now()->format('Y-m-d')) }}" required></x-field>
            <x-field label="Prepared By" name="technician"><input class="input" name="technician" value="{{ old('technician', $report->technician) }}" required></x-field>
            <x-field label="Overall Condition" name="overall_condition">
                <select class="input" name="overall_condition" required>@foreach(['Excellent','Good','Fair','Poor'] as $value)<option @selected(old('overall_condition', $report->overall_condition ?: 'Good') === $value)>{{ $value }}</option>@endforeach</select>
            </x-field>
            <label class="flex items-center gap-3 pt-7 text-sm font-medium"><input type="hidden" name="customer_present" value="0"><input class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" type="checkbox" name="customer_present" value="1" @checked(old('customer_present', $report->customer_present))> Client present</label>
        </div>
    </x-card>

    <x-card>
        <h2 class="mb-4 text-lg font-bold">Cleaning Checklist</h2>
        <div class="grid gap-4">
            <div class="relative">
                <label class="grid gap-1.5">
                    <span class="label">Add checklist item</span>
                    <div class="flex gap-2">
                        <input class="input" type="text" x-model="checklistInput" @keydown.enter.prevent="addChecklistItem()" placeholder="Type an item, e.g. Vacuum floors">
                        <button type="button" class="btn-secondary shrink-0" @click="addChecklistItem()">Add</button>
                    </div>
                </label>
                <div class="mt-2 grid gap-2 rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-800 dark:bg-slate-950" x-show="checklistSuggestions().length">
                    <template x-for="item in checklistSuggestions()" :key="item">
                        <button type="button" class="rounded-md px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-cyan-50 hover:text-cyan-800 dark:text-slate-200 dark:hover:bg-slate-800" @click="addChecklistItem(item)" x-text="item"></button>
                    </template>
                </div>
            </div>

            <div class="flex flex-wrap gap-2" x-show="checklist.length">
                <template x-for="(item, index) in checklist" :key="`${item}-${index}`">
                    <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1.5 text-sm font-semibold text-cyan-800 dark:border-cyan-900 dark:bg-cyan-950 dark:text-cyan-100">
                        <input type="hidden" name="checklist[]" :value="item">
                        <span x-text="item"></span>
                        <button type="button" class="text-cyan-900/70 hover:text-rose-600 dark:text-cyan-100" @click="removeChecklistItem(index)" :aria-label="`Remove ${item}`">x</button>
                    </span>
                </template>
            </div>
            <p class="text-sm text-slate-500" x-show="!checklist.length">No checklist items added yet.</p>
        </div>
    </x-card>

    <x-card>
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold">Cleaning Areas</h2>
            <button type="button" class="btn-secondary" @click="addArea">Add Cleaning Area</button>
        </div>
        <template x-for="(area, index) in areas" :key="index">
            <div class="mb-5 rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-semibold">Area <span x-text="index + 1"></span></h3>
                    <button type="button" class="text-sm font-semibold text-rose-600" @click="remove('areas', index)">Remove</button>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <input type="hidden" :name="`areas[${index}][id]`" :value="area.id || ''">
                    <label class="grid gap-1.5"><span class="label">Area Name</span><input class="input" :name="`areas[${index}][area_name]`" x-model="area.area_name" required></label>
                    <label class="grid gap-1.5"><span class="label">Photo Notes</span><input class="input" :name="`areas[${index}][photo_notes]`" x-model="area.photo_notes"></label>
                    <label class="grid gap-1.5"><span class="label">Description</span><textarea class="input min-h-24" :name="`areas[${index}][description]`" x-model="area.description"></textarea></label>
                    <label class="grid gap-1.5"><span class="label">Completion Notes</span><textarea class="input min-h-24" :name="`areas[${index}][completion_notes]`" x-model="area.completion_notes"></textarea></label>
                    <div class="grid gap-2">
                        <span class="label">Before Photos</span>
                        <label class="grid cursor-pointer place-items-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center transition hover:border-cyan-400 hover:bg-cyan-50 dark:border-slate-700 dark:bg-slate-950 dark:hover:border-cyan-700 dark:hover:bg-slate-900"
                            @dragover.prevent="$event.currentTarget.classList.add('border-cyan-500', 'bg-cyan-50', 'dark:bg-slate-900')"
                            @dragleave.prevent="$event.currentTarget.classList.remove('border-cyan-500', 'bg-cyan-50', 'dark:bg-slate-900')"
                            @drop.prevent="$event.currentTarget.classList.remove('border-cyan-500', 'bg-cyan-50', 'dark:bg-slate-900'); dropFiles($event, `areas[${index}][before_photos][]`, $event.currentTarget.querySelector('input[type=file]'), $event.currentTarget.nextElementSibling)">
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">Drop before photos here</span>
                            <span class="mt-1 text-xs text-slate-500">or click to choose photos</span>
                            <input class="sr-only" type="file" accept="image/*" multiple :name="`areas[${index}][before_photos][]`" @change="previewFiles($event, $event.target.closest('label').nextElementSibling)">
                        </label>
                        <div class="flex flex-wrap gap-2"></div>
                        <div class="flex flex-wrap gap-2" x-show="area.before_photos?.length">
                            <template x-for="photo in area.before_photos" :key="photo.id">
                                <div class="relative h-24 w-24 overflow-hidden rounded-lg ring-1 ring-slate-200 dark:ring-slate-700">
                                    <img class="h-full w-full object-cover" :src="photo.url" :alt="photo.name">
                                    <button type="button" class="absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-rose-600 text-xs font-bold text-white shadow" @click="removeExistingPhoto(area.before_photos, photo.id, 'area')" aria-label="Remove existing before photo">x</button>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <span class="label">After Photos</span>
                        <label class="grid cursor-pointer place-items-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center transition hover:border-cyan-400 hover:bg-cyan-50 dark:border-slate-700 dark:bg-slate-950 dark:hover:border-cyan-700 dark:hover:bg-slate-900"
                            @dragover.prevent="$event.currentTarget.classList.add('border-cyan-500', 'bg-cyan-50', 'dark:bg-slate-900')"
                            @dragleave.prevent="$event.currentTarget.classList.remove('border-cyan-500', 'bg-cyan-50', 'dark:bg-slate-900')"
                            @drop.prevent="$event.currentTarget.classList.remove('border-cyan-500', 'bg-cyan-50', 'dark:bg-slate-900'); dropFiles($event, `areas[${index}][after_photos][]`, $event.currentTarget.querySelector('input[type=file]'), $event.currentTarget.nextElementSibling)">
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">Drop after photos here</span>
                            <span class="mt-1 text-xs text-slate-500">or click to choose photos</span>
                            <input class="sr-only" type="file" accept="image/*" multiple :name="`areas[${index}][after_photos][]`" @change="previewFiles($event, $event.target.closest('label').nextElementSibling)">
                        </label>
                        <div class="flex flex-wrap gap-2"></div>
                        <div class="flex flex-wrap gap-2" x-show="area.after_photos?.length">
                            <template x-for="photo in area.after_photos" :key="photo.id">
                                <div class="relative h-24 w-24 overflow-hidden rounded-lg ring-1 ring-slate-200 dark:ring-slate-700">
                                    <img class="h-full w-full object-cover" :src="photo.url" :alt="photo.name">
                                    <button type="button" class="absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-rose-600 text-xs font-bold text-white shadow" @click="removeExistingPhoto(area.after_photos, photo.id, 'area')" aria-label="Remove existing after photo">x</button>
                                </div>
                            </template>
                        </div>
                    </div>
                    <label class="grid gap-1.5 md:col-span-2">
                        <span class="label">Google Drive Video Link</span>
                        <input class="input" type="url" :name="`areas[${index}][video_url]`" x-model="area.video_url" placeholder="Paste Google Drive folder or video link">
                    </label>
                </div>
            </div>
        </template>
    </x-card>

    <x-card>
        <div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-bold">Issues Found</h2><button type="button" class="btn-secondary" @click="addIssue">Add Issue</button></div>
        <template x-for="(issue, index) in issues" :key="index">
            <div class="mb-3 grid gap-3 rounded-lg border border-slate-200 p-3 dark:border-slate-800 lg:grid-cols-5">
                <input type="hidden" :name="`issues[${index}][id]`" :value="issue.id || ''">
                <input class="input" :name="`issues[${index}][issue_type]`" x-model="issue.issue_type" placeholder="Issue type">
                <input class="input" :name="`issues[${index}][description]`" x-model="issue.description" placeholder="Description">
                <select class="input" :name="`issues[${index}][severity]`" x-model="issue.severity"><option>Low</option><option>Medium</option><option>High</option><option>Critical</option></select>
                <div class="grid gap-2">
                    <label class="grid cursor-pointer place-items-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-3 py-4 text-center transition hover:border-cyan-400 hover:bg-cyan-50 dark:border-slate-700 dark:bg-slate-950 dark:hover:border-cyan-700 dark:hover:bg-slate-900"
                        @dragover.prevent="$event.currentTarget.classList.add('border-cyan-500', 'bg-cyan-50', 'dark:bg-slate-900')"
                        @dragleave.prevent="$event.currentTarget.classList.remove('border-cyan-500', 'bg-cyan-50', 'dark:bg-slate-900')"
                        @drop.prevent="$event.currentTarget.classList.remove('border-cyan-500', 'bg-cyan-50', 'dark:bg-slate-900'); dropFiles($event, `issues[${index}][photos][]`, $event.currentTarget.querySelector('input[type=file]'), $event.currentTarget.nextElementSibling)">
                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-100">Drop issue photos</span>
                        <span class="mt-1 text-xs text-slate-500">or click</span>
                        <input class="sr-only" type="file" accept="image/*" multiple :name="`issues[${index}][photos][]`" @change="previewFiles($event, $event.target.closest('label').nextElementSibling)">
                    </label>
                    <div class="flex flex-wrap gap-2"></div>
                    <div class="flex flex-wrap gap-2" x-show="issue.photos?.length">
                        <template x-for="photo in issue.photos" :key="photo.id">
                            <div class="relative h-24 w-24 overflow-hidden rounded-lg ring-1 ring-slate-200 dark:ring-slate-700">
                                <img class="h-full w-full object-cover" :src="photo.url" :alt="photo.name">
                                <button type="button" class="absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-rose-600 text-xs font-bold text-white shadow" @click="removeExistingPhoto(issue.photos, photo.id, 'issue')" aria-label="Remove existing issue photo">x</button>
                            </div>
                        </template>
                    </div>
                </div>
                <input class="input" :name="`issues[${index}][recommendation]`" x-model="issue.recommendation" placeholder="Recommendation">
            </div>
        </template>
    </x-card>

    <x-card>
        <h2 class="mb-4 text-lg font-bold">Summary and Recommendations</h2>
        <div class="grid gap-4 md:grid-cols-2">
            <x-field label="Work Summary" name="work_summary"><textarea class="input min-h-32" name="work_summary">{{ old('work_summary', $report->work_summary) }}</textarea></x-field>
            <x-field label="Recommendations" name="recommendations"><textarea class="input min-h-32" name="recommendations">{{ old('recommendations', $report->recommendations) }}</textarea></x-field>
            <x-field class="md:col-span-2" label="Internal Notes" name="internal_notes"><textarea class="input min-h-24" name="internal_notes">{{ old('internal_notes', $report->internal_notes) }}</textarea></x-field>
        </div>
    </x-card>

    <div class="grid gap-6">
        <x-card>
            <h2 class="mb-4 text-lg font-bold">Declaration</h2>
            <div class="grid gap-4 lg:grid-cols-2">
                <x-field label="Prepared By" name="technician_name"><input class="input" name="technician_name" value="{{ old('technician_name', $report->technician_name) }}"></x-field>
                <x-field label="Date" name="technician_signed_at"><input class="input" type="datetime-local" name="technician_signed_at" value="{{ old('technician_signed_at') }}"></x-field>
                <div x-data="signatureBox('technician_signature')" class="grid gap-2 lg:col-span-2">
                    <span class="label">Signature</span>
                    <canvas x-ref="canvas" class="h-40 w-full rounded-lg border border-slate-300 bg-white dark:border-slate-700"></canvas>
                    <input x-ref="input" type="hidden" name="technician_signature">
                    <button type="button" class="btn-secondary w-fit" @click="clear">Clear Signature</button>
                </div>
            </div>
        </x-card>
    </div>

    <div class="sticky bottom-0 -mx-4 border-t border-slate-200 bg-white/95 px-4 py-4 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="flex flex-wrap justify-end gap-3">
            <a class="btn-secondary" href="{{ route('reports.index') }}">Cancel</a>
            <button class="btn-primary disabled:cursor-not-allowed disabled:opacity-70" :disabled="submitting">
                <span x-show="!submitting">{{ $report->exists ? 'Save Changes and Regenerate PDF' : 'Save and Generate PDF' }}</span>
                <span x-show="submitting">Processing...</span>
            </button>
        </div>
    </div>
</div>
