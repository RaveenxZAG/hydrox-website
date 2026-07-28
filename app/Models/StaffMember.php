<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class StaffMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcontractor_onboarding_id',
        'public_id',
        'servicem8_staff_uuid',
        'first_name',
        'last_name',
        'email',
        'normalized_email',
        'mobile',
        'normalized_mobile',
        'staff_status',
        'portal_access_enabled',
        'invoicing_enabled',
        'address',
        'emergency_contact',
        'legal_business_name',
        'trading_name',
        'abn',
        'gst_registered',
        'business_structure',
        'contact_person',
        'business_address',
        'availability',
        'skills',
        'experience',
        'bank_details',
        'superannuation',
        'insurance_expiry',
        'public_liability_insurance',
        'public_liability_insurance_name',
        'workers_compensation_insurance',
        'workers_compensation_insurance_name',
        'police_clearance',
        'police_clearance_name',
        'driver_licence',
        'driver_licence_name',
        'working_rights',
        'working_rights_name',
        'job_title',
        'security_role',
        'schedule_colour',
        'labour_rate',
        'active',
        'show_on_schedule',
        'notes',
        'servicem8_synced_at',
        'servicem8_sync_message',
        'servicem8_invite_status',
        'servicem8_invited_at',
        'servicem8_invited_by',
        'servicem8_status_note',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'gst_registered' => 'boolean',
            'portal_access_enabled' => 'boolean',
            'invoicing_enabled' => 'boolean',
            'skills' => 'array',
            'show_on_schedule' => 'boolean',
            'labour_rate' => 'decimal:2',
            'insurance_expiry' => 'date',
            'servicem8_synced_at' => 'datetime',
            'servicem8_invited_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $staff): void {
            $staff->public_id ??= (string) Str::uuid();
            $staff->staff_status ??= $staff->active ? 'active' : 'inactive';
        });
    }

    public function onboarding(): BelongsTo
    {
        return $this->belongsTo(SubcontractorOnboarding::class, 'subcontractor_onboarding_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(StaffInvoiceSubmission::class);
    }

    public function siteShiftAssignments(): HasMany
    {
        return $this->hasMany(SiteShiftAssignment::class);
    }

    public function profileChangeRequests(): HasMany
    {
        return $this->hasMany(StaffProfileChangeRequest::class);
    }

    public function serviceM8Inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'servicem8_invited_by');
    }

    public function fullName(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? '')) ?: 'Unnamed subcontractor';
    }

    public function securityRoleName(array $roleNames = []): string
    {
        if (blank($this->security_role)) {
            return 'Not recorded';
        }

        return $roleNames[$this->security_role] ?? $this->security_role;
    }

    public function canAccessPortal(): bool
    {
        return $this->active
            && $this->portal_access_enabled
            && $this->staff_status === 'active'
            && blank($this->archived_at);
    }

    public function canSubmitInvoices(): bool
    {
        return $this->canAccessPortal() && $this->invoicing_enabled;
    }

    public function serviceM8InviteLabel(): string
    {
        return match ($this->servicem8_invite_status) {
            'invited' => 'Invited',
            'not_required' => 'Not Required',
            default => 'Not Invited',
        };
    }

    public function documentFields(): array
    {
        return [
            'public_liability_insurance' => 'Public Liability Insurance',
            'workers_compensation_insurance' => 'Victorian Working with Children Check',
            'police_clearance' => 'Police Clearance',
            'driver_licence' => 'Driver Licence',
            'working_rights' => 'Working Rights / VISA or relevant document',
        ];
    }

    public function documentName(string $field): ?string
    {
        return $this->{$field.'_name'} ?: ($this->{$field} ? basename((string) $this->{$field}) : null);
    }

    public function skillsText(): string
    {
        return implode(', ', \Illuminate\Support\Arr::wrap($this->skills));
    }

    public function missingInfo(): array
    {
        $onboarding = $this->relationLoaded('onboarding') ? $this->onboarding : $this->onboarding()->first();

        return collect([
            'Email' => $this->email ?: $onboarding?->email,
            'Mobile' => $this->mobile ?: $onboarding?->mobile ?: $onboarding?->phone,
            'Address' => $this->address ?: $this->business_address ?: $onboarding?->business_address ?: $onboarding?->address,
            'Availability' => $this->availability ?: $onboarding?->available_hours ?: $onboarding?->availability,
            'Bank Details' => $this->bank_details ?: $onboarding?->bank_details,
            'Public Liability Insurance' => $this->public_liability_insurance ?: $onboarding?->public_liability_insurance,
            'Working with Children Check' => $this->workers_compensation_insurance ?: $onboarding?->workers_compensation_insurance,
            'Police Clearance' => $this->police_clearance ?: $onboarding?->police_clearance,
            'Driver Licence' => $this->driver_licence ?: $onboarding?->driver_licence,
            'Working Rights' => $this->working_rights ?: $onboarding?->working_rights,
        ])
            ->filter(fn ($value): bool => blank($value))
            ->keys()
            ->all();
    }

    public function missingInfoCount(): int
    {
        return count($this->missingInfo());
    }

    public static function upsertFromOnboarding(SubcontractorOnboarding $onboarding): self
    {
        return self::updateOrCreate(
            ['subcontractor_onboarding_id' => $onboarding->id],
            [
                'first_name' => $onboarding->first_name,
                'last_name' => $onboarding->last_name,
                'email' => $onboarding->email,
                'normalized_email' => $onboarding->normalized_email,
                'mobile' => $onboarding->mobile ?: $onboarding->phone,
                'normalized_mobile' => $onboarding->normalized_mobile,
                'address' => $onboarding->business_address ?: $onboarding->address,
                'emergency_contact' => $onboarding->emergency_contact,
                'legal_business_name' => $onboarding->legal_business_name,
                'trading_name' => $onboarding->trading_name,
                'abn' => $onboarding->abn,
                'gst_registered' => $onboarding->gst_registered,
                'business_structure' => $onboarding->business_structure,
                'contact_person' => $onboarding->contact_person,
                'business_address' => $onboarding->business_address,
                'availability' => $onboarding->available_hours ?: $onboarding->availability,
                'skills' => $onboarding->skills,
                'experience' => $onboarding->experience,
                'bank_details' => $onboarding->bank_details,
                'superannuation' => $onboarding->superannuation,
                'insurance_expiry' => $onboarding->insurance_expiry,
                'public_liability_insurance' => $onboarding->public_liability_insurance,
                'workers_compensation_insurance' => $onboarding->workers_compensation_insurance,
                'police_clearance' => $onboarding->police_clearance,
                'driver_licence' => $onboarding->driver_licence,
                'working_rights' => $onboarding->working_rights,
                'job_title' => 'Sub Contractor',
                'security_role' => config('services.servicem8.default_security_role_uuid') ?: config('services.servicem8.default_security_role', 'Strict Contractor'),
                'active' => true,
                'staff_status' => 'active',
                'portal_access_enabled' => true,
                'invoicing_enabled' => true,
                'show_on_schedule' => true,
                'servicem8_synced_at' => now(),
                'servicem8_sync_message' => 'Created locally from approved subcontractor onboarding.',
                'servicem8_invite_status' => 'not_invited',
            ]
        );
    }
}
