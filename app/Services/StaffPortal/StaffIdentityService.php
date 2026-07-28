<?php

namespace App\Services\StaffPortal;

use App\Models\StaffIdentityLock;
use App\Models\StaffMember;
use App\Models\SubcontractorOnboarding;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class StaffIdentityService
{
    public const DUPLICATE_EMAIL_MESSAGE = 'Email address already in use. This email address is linked to an existing application or subcontractor profile. Please use a different email address or contact support.';
    public const DUPLICATE_MOBILE_MESSAGE = 'Mobile number already in use. This mobile number is linked to an existing application or subcontractor profile. Please use a different mobile number or contact support.';

    public function normalizeEmail(?string $email): ?string
    {
        $email = trim((string) $email);

        return $email === '' ? null : mb_strtolower($email);
    }

    public function normalizeMobile(?string $mobile): ?string
    {
        $number = preg_replace('/[^0-9+]/', '', (string) $mobile);

        if ($number === '') {
            return null;
        }

        if (str_starts_with($number, '+61')) {
            return '+61'.preg_replace('/[^0-9]/', '', substr($number, 3));
        }

        if (str_starts_with($number, '61')) {
            return '+'.$number;
        }

        if (str_starts_with($number, '04')) {
            return '+61'.substr($number, 1);
        }

        if (str_starts_with($number, '4') && strlen($number) === 9) {
            return '+61'.$number;
        }

        return $number;
    }

    public function assertAvailable(?string $email, ?string $mobile, ?string $ignoreOwnerType = null, ?int $ignoreOwnerId = null): void
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $normalizedMobile = $this->normalizeMobile($mobile);

        if ($normalizedEmail && $this->emailExists($normalizedEmail, $ignoreOwnerType, $ignoreOwnerId)) {
            throw ValidationException::withMessages(['email' => self::DUPLICATE_EMAIL_MESSAGE]);
        }

        if ($normalizedMobile && $this->mobileExists($normalizedMobile, $ignoreOwnerType, $ignoreOwnerId)) {
            throw ValidationException::withMessages(['mobile' => self::DUPLICATE_MOBILE_MESSAGE]);
        }
    }

    public function createLock(object $owner, ?string $email, ?string $mobile): void
    {
        try {
            StaffIdentityLock::query()->create([
                'normalized_email' => $this->normalizeEmail($email),
                'normalized_mobile' => $this->normalizeMobile($mobile),
                'owner_type' => $owner::class,
                'owner_id' => $owner->id,
            ]);
        } catch (QueryException $exception) {
            $message = str($exception->getMessage())->lower();

            if ($message->contains('normalized_email')) {
                throw ValidationException::withMessages(['email' => self::DUPLICATE_EMAIL_MESSAGE]);
            }

            if ($message->contains('normalized_mobile')) {
                throw ValidationException::withMessages(['mobile' => self::DUPLICATE_MOBILE_MESSAGE]);
            }

            throw $exception;
        }
    }

    public function updateLock(object $owner, ?string $email, ?string $mobile): void
    {
        StaffIdentityLock::query()->updateOrCreate(
            ['owner_type' => $owner::class, 'owner_id' => $owner->id],
            [
                'normalized_email' => $this->normalizeEmail($email),
                'normalized_mobile' => $this->normalizeMobile($mobile),
            ]
        );
    }

    public function transferLock(object $fromOwner, object $toOwner, ?string $email, ?string $mobile): void
    {
        StaffIdentityLock::query()
            ->where('owner_type', $fromOwner::class)
            ->where('owner_id', $fromOwner->id)
            ->update([
                'owner_type' => $toOwner::class,
                'owner_id' => $toOwner->id,
                'normalized_email' => $this->normalizeEmail($email),
                'normalized_mobile' => $this->normalizeMobile($mobile),
                'updated_at' => now(),
            ]);
    }

    private function lockExists(string $column, string $value, ?string $ignoreOwnerType, ?int $ignoreOwnerId): bool
    {
        return StaffIdentityLock::query()
            ->where($column, $value)
            ->when($ignoreOwnerType && $ignoreOwnerId, fn ($query) => $query
                ->where(fn ($query) => $query
                    ->where('owner_type', '!=', $ignoreOwnerType)
                    ->orWhere('owner_id', '!=', $ignoreOwnerId)))
            ->exists();
    }

    private function emailExists(string $email, ?string $ignoreOwnerType, ?int $ignoreOwnerId): bool
    {
        if ($this->lockExists('normalized_email', $email, $ignoreOwnerType, $ignoreOwnerId)) {
            return true;
        }

        if ($this->staffExists('normalized_email', $email, $ignoreOwnerType, $ignoreOwnerId)) {
            return true;
        }

        if ($this->onboardingExists('normalized_email', $email, $ignoreOwnerType, $ignoreOwnerId)) {
            return true;
        }

        return StaffMember::query()
            ->when($ignoreOwnerType === StaffMember::class && $ignoreOwnerId, fn ($query) => $query->whereKeyNot($ignoreOwnerId))
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists()
            || SubcontractorOnboarding::query()
                ->when($ignoreOwnerType === SubcontractorOnboarding::class && $ignoreOwnerId, fn ($query) => $query->whereKeyNot($ignoreOwnerId))
                ->when($ignoreOwnerType === StaffMember::class && $ignoreOwnerId, fn ($query) => $query->where(fn ($query) => $query->where('staff_member_id', '!=', $ignoreOwnerId)->orWhereNull('staff_member_id')))
                ->whereRaw('LOWER(email) = ?', [$email])
                ->exists();
    }

    private function mobileExists(string $mobile, ?string $ignoreOwnerType, ?int $ignoreOwnerId): bool
    {
        if ($this->lockExists('normalized_mobile', $mobile, $ignoreOwnerType, $ignoreOwnerId)) {
            return true;
        }

        if ($this->staffExists('normalized_mobile', $mobile, $ignoreOwnerType, $ignoreOwnerId)) {
            return true;
        }

        if ($this->onboardingExists('normalized_mobile', $mobile, $ignoreOwnerType, $ignoreOwnerId)) {
            return true;
        }

        $staffMobiles = StaffMember::query()
            ->when($ignoreOwnerType === StaffMember::class && $ignoreOwnerId, fn ($query) => $query->whereKeyNot($ignoreOwnerId))
            ->pluck('mobile');

        if ($staffMobiles->contains(fn (?string $value): bool => $this->normalizeMobile($value) === $mobile)) {
            return true;
        }

        return SubcontractorOnboarding::query()
            ->when($ignoreOwnerType === SubcontractorOnboarding::class && $ignoreOwnerId, fn ($query) => $query->whereKeyNot($ignoreOwnerId))
            ->when($ignoreOwnerType === StaffMember::class && $ignoreOwnerId, fn ($query) => $query->where(fn ($query) => $query->where('staff_member_id', '!=', $ignoreOwnerId)->orWhereNull('staff_member_id')))
            ->get(['mobile', 'phone'])
            ->contains(fn (SubcontractorOnboarding $onboarding): bool => $this->normalizeMobile($onboarding->mobile ?: $onboarding->phone) === $mobile);
    }

    private function staffExists(string $column, string $value, ?string $ignoreOwnerType, ?int $ignoreOwnerId): bool
    {
        return StaffMember::query()
            ->when($ignoreOwnerType === StaffMember::class && $ignoreOwnerId, fn ($query) => $query->whereKeyNot($ignoreOwnerId))
            ->where($column, $value)
            ->exists();
    }

    private function onboardingExists(string $column, string $value, ?string $ignoreOwnerType, ?int $ignoreOwnerId): bool
    {
        return SubcontractorOnboarding::query()
            ->when($ignoreOwnerType === SubcontractorOnboarding::class && $ignoreOwnerId, fn ($query) => $query->whereKeyNot($ignoreOwnerId))
            ->when($ignoreOwnerType === StaffMember::class && $ignoreOwnerId, fn ($query) => $query->where(fn ($query) => $query->where('staff_member_id', '!=', $ignoreOwnerId)->orWhereNull('staff_member_id')))
            ->where($column, $value)
            ->exists();
    }
}
