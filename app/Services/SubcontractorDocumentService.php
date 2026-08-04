<?php

namespace App\Services;

use App\Models\StaffMember;
use App\Models\SubcontractorDocument;
use App\Models\SubcontractorDocumentVersion;
use App\Models\SubcontractorOnboarding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SubcontractorDocumentService
{
    public function addForOnboarding(SubcontractorOnboarding $onboarding, string $category, UploadedFile $file, ?int $uploaderId = null, ?string $reason = null): SubcontractorDocumentVersion
    {
        return $this->add($category, $file, 'public', 'subcontractor-documents', $onboarding, $onboarding->staffMember, $uploaderId, $reason);
    }

    public function addForStaff(StaffMember $staff, string $category, UploadedFile $file, ?int $uploaderId = null, ?string $reason = null): SubcontractorDocumentVersion
    {
        return $this->add($category, $file, 'local', 'staff-documents/'.$staff->id, $staff->onboarding, $staff, $uploaderId, $reason);
    }

    private function add(string $category, UploadedFile $file, string $disk, string $directory, ?SubcontractorOnboarding $onboarding, ?StaffMember $staff, ?int $uploaderId, ?string $reason): SubcontractorDocumentVersion
    {
        return DB::transaction(function () use ($category, $file, $disk, $directory, $onboarding, $staff, $uploaderId, $reason): SubcontractorDocumentVersion {
            $document = SubcontractorDocument::query()
                ->when($onboarding, fn ($query) => $query->where('subcontractor_onboarding_id', $onboarding->id), fn ($query) => $query->whereNull('subcontractor_onboarding_id'))
                ->when($staff, fn ($query) => $query->where('staff_member_id', $staff->id), fn ($query) => $query->whereNull('staff_member_id'))
                ->where('category', $category)->first();

            $document ??= SubcontractorDocument::create([
                'subcontractor_onboarding_id' => $onboarding?->id,
                'staff_member_id' => $staff?->id,
                'category' => $category,
            ]);

            $nextVersion = ((int) $document->versions()->max('version_number')) + 1;
            $document->versions()->where('is_current', true)->update(['is_current' => false]);
            $path = $file->store($directory, $disk);

            return $document->versions()->create([
                'version_number' => $nextVersion,
                'original_filename' => $file->getClientOriginalName(),
                'storage_disk' => $disk,
                'storage_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'review_status' => 'Pending Review',
                'uploaded_by' => $uploaderId,
                'replacement_reason' => $reason,
                'is_current' => true,
            ]);
        });
    }
}
