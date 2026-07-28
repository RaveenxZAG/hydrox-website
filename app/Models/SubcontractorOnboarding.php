<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class SubcontractorOnboarding extends Model
{
    use HasFactory;

    public const STATUSES = [
        'Draft',
        'Submitted',
        'Pending Review',
        'Documents Required',
        'Approved',
        'Rejected',
        'Active',
        'Suspended',
        'Archived',
    ];

    public const MANDATORY_DOCUMENTS = [
        'public_liability_insurance' => 'Public Liability Insurance',
        'workers_compensation_insurance' => 'Victorian Working with Children Check',
        'police_clearance' => 'National Police Check',
        'driver_licence' => 'Driver Licence',
        'working_rights' => 'Working Rights / VISA or relevant document',
    ];

    protected $fillable = [
        'status',
        'legal_business_name',
        'trading_name',
        'abn',
        'gst_registered',
        'business_structure',
        'contact_person',
        'full_name',
        'first_name',
        'last_name',
        'email',
        'normalized_email',
        'phone',
        'mobile',
        'normalized_mobile',
        'position',
        'availability',
        'experience',
        'skills',
        'address',
        'business_address',
        'emergency_contact',
        'notes',
        'public_liability_insurance',
        'workers_compensation_insurance',
        'insurance_expiry',
        'police_clearance',
        'driver_licence',
        'working_rights',
        'uploaded_certificates',
        'service_types',
        'preferred_work_areas',
        'available_days',
        'available_hours',
        'crew_members',
        'supervisor',
        'payment_method',
        'bank_details',
        'superannuation',
        'servicem8_staff_uuid',
        'servicem8_sync_status',
        'servicem8_sync_message',
        'sync_logs',
        'submitted_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'portal_user_id',
        'staff_member_id',
    ];

    protected function casts(): array
    {
        return [
            'gst_registered' => 'boolean',
            'insurance_expiry' => 'date',
            'uploaded_certificates' => 'array',
            'service_types' => 'array',
            'skills' => 'array',
            'preferred_work_areas' => 'array',
            'available_days' => 'array',
            'sync_logs' => 'array',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function portalUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'portal_user_id');
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class, 'staff_member_id');
    }

    public function missingMandatoryDocuments(): array
    {
        return collect(self::MANDATORY_DOCUMENTS)
            ->filter(fn (string $label, string $field): bool => blank($this->{$field}))
            ->all();
    }

    public function documentFields(): array
    {
        return self::MANDATORY_DOCUMENTS;
    }

    public static function skillOptions(): array
    {
        return [
            'Office & Commercial Cleaning',
            'Retail Cleaning',
            'Medical & Infection Control Cleaning',
            'School Cleaning',
            'Industrial & Warehouse Cleaning',
            'Residential Cleaning',
            'NDIS & DVA Cleaning',
            'Strip & Seal',
            'High-Speed Floor Burnishing',
            'Carpet Steam Cleaning',
            'Pressure & Soft Washing',
            'High Access Cleaning',
            'Biohazard Cleaning',
            'Infection Control Cleaning',
            'Builders Final Cleans',
            'Gardening',
            'Graffiti Removal',
            'Solar Panel Cleaning',
            'Protective Surface Sealing',
        ];
    }

    public function skillsText(): string
    {
        return implode(', ', Arr::wrap($this->skills));
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'Draft' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
            'Submitted' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950 dark:text-cyan-200',
            'Pending Review' => 'bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
            'Documents Required' => 'bg-orange-50 text-orange-800 dark:bg-orange-950 dark:text-orange-200',
            'Approved', 'Active' => 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
            'Rejected', 'Suspended' => 'bg-rose-50 text-rose-800 dark:bg-rose-950 dark:text-rose-200',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        };
    }

    public function appendSyncLog(string $status, string $message): void
    {
        $log = [
            'status' => $status,
            'message' => $message,
            'logged_at' => now()->toDateTimeString(),
        ];

        $this->forceFill([
            'servicem8_sync_status' => $status,
            'servicem8_sync_message' => $message,
            'sync_logs' => [$log],
        ])->save();
    }

    public function serviceTypesText(): string
    {
        return implode(', ', Arr::wrap($this->service_types));
    }
}
