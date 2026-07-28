<?php

namespace App\Services;

use App\Models\StaffMember;
use App\Models\SubcontractorOnboarding;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ServiceM8StaffService
{
    private const DEFAULT_EMAIL_SUBJECT = '{company} ServiceM8 access';

    private const DEFAULT_EMAIL_BODY = "Hi {first_name},\n\n{company} has created your ServiceM8 subcontractor access.\n\nOpen ServiceM8 here: {servicem8_login_url}\nUse this email address to sign in or reset/setup your password: {email}\n\n{company}";

    private const DEFAULT_SMS_BODY = 'Hi {first_name}, {company} has created your ServiceM8 subcontractor access. Open {servicem8_login_url} and use {email} to sign in or reset/setup your password.';

    public function securityRoles(): array
    {
        $response = $this->request()->get('https://api.servicem8.com/api_1.0/securityrole.json');

        if ($response->failed()) {
            throw new RuntimeException($this->errorMessage($response, 'Could not load ServiceM8 security roles.'));
        }

        return $response->json() ?: [];
    }

    public function securityRoleNames(): array
    {
        return collect($this->securityRoles())
            ->mapWithKeys(function (array $role): array {
                $uuid = $this->roleUuid($role);
                $name = $role['name'] ?? null;

                if (! $uuid || ! $name) {
                    return [];
                }

                return [$uuid => $name];
            })
            ->all();
    }

    public function createStaff(SubcontractorOnboarding $onboarding): string
    {
        $securityRoleUuid = $this->securityRoleUuid();
        $payload = $this->staffPayload($onboarding, $securityRoleUuid);

        if ($onboarding->servicem8_staff_uuid) {
            $this->updateStaff($onboarding->servicem8_staff_uuid, $payload);
            $this->verifyStaffRole($onboarding->servicem8_staff_uuid, $securityRoleUuid);

            return $onboarding->servicem8_staff_uuid;
        }

        $response = $this->request()->post('https://api.servicem8.com/api_1.0/staff.json', $payload);

        if ($response->failed()) {
            throw new RuntimeException($this->errorMessage($response, 'ServiceM8 rejected the subcontractor creation request.'));
        }

        $uuid = $response->json('uuid') ?: $response->header('x-record-uuid');

        if (! $uuid) {
            throw new RuntimeException('ServiceM8 did not return a subcontractor UUID.');
        }

        $this->updateStaff($uuid, $payload);
        $this->verifyStaffRole($uuid, $securityRoleUuid);

        return $uuid;
    }

    public function syncStaffMembers(): int
    {
        $response = $this->request()->get('https://api.servicem8.com/api_1.0/staff.json');

        if ($response->failed()) {
            throw new RuntimeException($this->errorMessage($response, 'Could not sync ServiceM8 subcontractors.'));
        }

        $records = collect($response->json() ?: []);

        $records->each(function (array $record): void {
            $uuid = $record['uuid'] ?? null;

            if (! $uuid) {
                return;
            }

            if ((int) ($record['active'] ?? 1) === 0) {
                StaffMember::where('servicem8_staff_uuid', $uuid)->update([
                    'active' => false,
                    'servicem8_synced_at' => now(),
                    'servicem8_sync_message' => 'ServiceM8 subcontractor is inactive.',
                ]);

                return;
            }

            StaffMember::updateOrCreate(
                ['servicem8_staff_uuid' => $uuid],
                [
                    'first_name' => $record['first'] ?? null,
                    'last_name' => $record['last'] ?? null,
                    'email' => $record['email'] ?? null,
                    'mobile' => $record['mobile'] ?? null,
                    'job_title' => $record['job_title'] ?? null,
                    'security_role' => $record['security_role_uuid'] ?? null,
                    'schedule_colour' => $record['color'] ?? null,
                    'active' => true,
                    'show_on_schedule' => ! (bool) ($record['hide_from_schedule'] ?? false),
                    'servicem8_synced_at' => now(),
                    'servicem8_sync_message' => 'Synced from ServiceM8 subcontractor list.',
                ]
            );
        });

        return $records->count();
    }

    public function updateStaffMember(StaffMember $staffMember): void
    {
        if (! $staffMember->servicem8_staff_uuid) {
            return;
        }

        $payload = array_filter([
            'first' => $staffMember->first_name,
            'last' => $staffMember->last_name,
            'email' => $staffMember->email,
            'mobile' => $staffMember->mobile,
            'job_title' => $staffMember->job_title,
            'color' => $staffMember->schedule_colour,
            'active' => $staffMember->active ? 1 : 0,
            'hide_from_schedule' => $staffMember->show_on_schedule ? 0 : 1,
            'security_role_uuid' => Str::isUuid((string) $staffMember->security_role) ? $staffMember->security_role : null,
        ], fn ($value): bool => filled($value) || $value === 0);

        $this->updateStaff($staffMember->servicem8_staff_uuid, $payload);
    }

    public function deleteStaffMember(StaffMember $staffMember): void
    {
        if (! $staffMember->servicem8_staff_uuid) {
            return;
        }

        $response = $this->request()->delete("https://api.servicem8.com/api_1.0/staff/{$staffMember->servicem8_staff_uuid}.json");

        if ($response->failed()) {
            throw new RuntimeException($this->errorMessage($response, 'ServiceM8 could not delete this subcontractor.'));
        }
    }

    private function staffPayload(SubcontractorOnboarding $onboarding, string $securityRoleUuid): array
    {
        return array_filter([
            'first' => $onboarding->first_name,
            'last' => $onboarding->last_name,
            'email' => $onboarding->email,
            'active' => 1,
            'mobile' => $onboarding->mobile ?: $onboarding->phone,
            'job_title' => 'Sub Contractor',
            'security_role_uuid' => $securityRoleUuid,
            'color' => config('services.servicem8.default_schedule_colour', '#0891b2'),
            'hide_from_schedule' => config('services.servicem8.hide_from_schedule') ? 1 : 0,
        ], fn ($value): bool => filled($value) || $value === 0);
    }

    private function updateStaff(string $uuid, array $payload): void
    {
        $url = "https://api.servicem8.com/api_1.0/staff/{$uuid}.json";
        $response = $this->request()->post($url, $payload);

        if ($response->failed()) {
            throw new RuntimeException($this->errorMessage($response, 'ServiceM8 created the subcontractor, but could not update the security role.'));
        }

        $formResponse = $this->request()
            ->asForm()
            ->post($url, $payload);

        if ($formResponse->failed()) {
            throw new RuntimeException($this->errorMessage($formResponse, 'ServiceM8 created the subcontractor, but could not update the security role using form payload.'));
        }
    }

    private function verifyStaffRole(string $uuid, string $securityRoleUuid): void
    {
        $response = $this->request()->get("https://api.servicem8.com/api_1.0/staff/{$uuid}.json");

        if ($response->failed()) {
            throw new RuntimeException($this->errorMessage($response, 'ServiceM8 created the subcontractor, but could not verify the subcontractor record.'));
        }

        if (! array_key_exists('security_role_uuid', $response->json() ?? [])) {
            return;
        }

        if ($response->json('security_role_uuid') !== $securityRoleUuid) {
            $actualRole = $response->json('security_role_uuid') ?: '0 / empty';

            throw new RuntimeException("ServiceM8 created the subcontractor, but did not apply the Strict Contractor security role. Expected security_role_uuid {$securityRoleUuid}, ServiceM8 returned {$actualRole}.");
        }
    }

    public function sendServiceM8Messages(string $staffUuid, SubcontractorOnboarding $onboarding): void
    {
        $this->sendStaffEmail($staffUuid, $onboarding);
        $this->sendStaffSms($onboarding);
    }

    private function sendStaffEmail(string $staffUuid, SubcontractorOnboarding $onboarding): void
    {
        $subjectTemplate = SystemSetting::getValue('servicem8_onboarding_email_subject', self::DEFAULT_EMAIL_SUBJECT);
        $bodyTemplate = SystemSetting::getValue('servicem8_onboarding_email_body', self::DEFAULT_EMAIL_BODY);
        $subject = $this->renderMessageTemplate($subjectTemplate, $onboarding, $staffUuid);
        $textBody = $this->renderMessageTemplate($bodyTemplate, $onboarding, $staffUuid);
        $htmlBody = nl2br(e($textBody), false);

        $response = $this->request()->post('https://api.servicem8.com/platform_service_email', [
            'to' => $onboarding->email,
            'subject' => $subject,
            'textBody' => $textBody,
            'htmlBody' => $htmlBody,
        ]);

        if ($response->failed()) {
            throw new RuntimeException($this->errorMessage($response, 'ServiceM8 subcontractor was created, but the ServiceM8 email could not be sent.'));
        }
    }

    public function sendStaffInvite(string $staffUuid, SubcontractorOnboarding $onboarding): void
    {
        $configuredEndpoint = config('services.servicem8.staff_invite_endpoint');
        $endpoints = array_filter([
            $configuredEndpoint,
            "https://api.servicem8.com/api_1.0/staff/{$staffUuid}/invite.json",
            "https://api.servicem8.com/api_1.0/staff/{$staffUuid}/send_invite.json",
        ]);

        $messages = [];

        foreach ($endpoints as $endpoint) {
            $response = $this->request()->post($endpoint, [
                'email' => $onboarding->email,
                'staff_uuid' => $staffUuid,
            ]);

            if ($response->successful()) {
                return;
            }

            $messages[] = $this->errorMessage($response, "ServiceM8 invite endpoint failed: {$endpoint}");
        }

        throw new RuntimeException('ServiceM8 subcontractor was created, but ServiceM8 did not send the subcontractor invite. Set SERVICEM8_STAFF_INVITE_ENDPOINT to the correct ServiceM8 invite endpoint if your account uses a different invite URL. Last response: '.implode(' | ', array_unique($messages)));
    }

    public function sendStaffSms(SubcontractorOnboarding $onboarding): void
    {
        $mobile = $this->normaliseMobile($onboarding->mobile ?: $onboarding->phone);
        $messageTemplate = SystemSetting::getValue('servicem8_onboarding_sms_body', self::DEFAULT_SMS_BODY);

        $response = $this->request()->post('https://api.servicem8.com/platform_service_sms', [
            'to' => $mobile,
            'message' => $this->renderMessageTemplate($messageTemplate, $onboarding),
        ]);

        if ($response->failed()) {
            throw new RuntimeException($this->errorMessage($response, 'ServiceM8 subcontractor was created, but the ServiceM8 SMS could not be sent.'));
        }
    }

    public function sendSms(string $mobile, string $message): void
    {
        $response = $this->request()->post('https://api.servicem8.com/platform_service_sms', [
            'to' => $this->normaliseMobile($mobile),
            'message' => $message,
        ]);

        if ($response->failed()) {
            throw new RuntimeException($this->errorMessage($response, 'ServiceM8 SMS could not be sent.'));
        }
    }

    public function sendEmail(string $email, string $subject, string $textBody, string $htmlBody, array $attachmentUuids = []): void
    {
        $response = $this->request()->post('https://api.servicem8.com/platform_service_email', [
            'to' => $email,
            'subject' => $subject,
            'textBody' => $textBody,
            'htmlBody' => $htmlBody,
            'attachments' => array_values($attachmentUuids),
        ]);

        if ($response->failed()) {
            throw new RuntimeException($this->errorMessage($response, 'ServiceM8 email could not be sent.'));
        }
    }

    public function uploadStaffAttachment(string $staffUuid, string $filename, string $contents): string
    {
        if (blank($staffUuid)) {
            throw new RuntimeException('The subcontractor is not linked to ServiceM8, so the remittance PDF cannot be attached.');
        }

        $extension = '.'.strtolower(pathinfo($filename, PATHINFO_EXTENSION) ?: 'pdf');
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $response = $this->request()->post('https://api.servicem8.com/api_1.0/attachment.json', [
            'related_object' => 'staff',
            'related_object_uuid' => $staffUuid,
            'attachment_name' => $name,
            'file_type' => $extension,
            'attachment_source' => 'REMITTANCE',
            'active' => 1,
        ]);

        if ($response->failed()) {
            throw new RuntimeException($this->errorMessage($response, 'ServiceM8 could not create the remittance attachment.'));
        }

        $uuid = $response->json('uuid') ?: $response->header('x-record-uuid');
        if (! $uuid) {
            throw new RuntimeException('ServiceM8 did not return an attachment UUID for the remittance PDF.');
        }

        $upload = $this->request()
            ->attach('file', $contents, $filename)
            ->post("https://api.servicem8.com/api_1.0/Attachment/{$uuid}.file");

        if ($upload->failed()) {
            throw new RuntimeException($this->errorMessage($upload, 'ServiceM8 created the remittance attachment but could not upload the PDF.'));
        }

        return $uuid;
    }

    private function renderMessageTemplate(?string $template, SubcontractorOnboarding $onboarding, ?string $staffUuid = null): string
    {
        $company = config('app.company_name', 'Hydrox Facility Management');
        $fullName = $onboarding->full_name ?: trim($onboarding->first_name.' '.$onboarding->last_name);

        return strtr((string) $template, [
            '{first_name}' => (string) $onboarding->first_name,
            '{last_name}' => (string) $onboarding->last_name,
            '{full_name}' => $fullName,
            '{email}' => (string) $onboarding->email,
            '{mobile}' => (string) ($onboarding->mobile ?: $onboarding->phone),
            '{company}' => $company,
            '{servicem8_login_url}' => 'https://go.servicem8.com',
            '{staff_uuid}' => (string) $staffUuid,
        ]);
    }

    private function securityRoleUuid(): string
    {
        $configuredUuid = config('services.servicem8.default_security_role_uuid');

        if ($configuredUuid) {
            return $configuredUuid;
        }

        $roleName = config('services.servicem8.default_security_role', 'Strict Contractor');
        $roles = $this->securityRoles();

        $role = collect($roles)
            ->first(fn (array $role): bool => (int) ($role['active'] ?? 1) === 1
                && Str::lower($role['name'] ?? '') === Str::lower($roleName));

        $uuid = $this->roleUuid($role);

        if (! $role || blank($uuid)) {
            $availableRoles = collect($roles)
                ->pluck('name')
                ->filter()
                ->implode(', ');

            throw new RuntimeException("Could not find a ServiceM8 security role named {$roleName}. Available roles: {$availableRoles}. Set SERVICEM8_DEFAULT_SECURITY_ROLE_UUID in .env if the role name is different.");
        }

        return $uuid;
    }

    private function roleUuid(?array $role): ?string
    {
        if (! $role) {
            return null;
        }

        return $role['uuid'] ?? $role['security_role_uuid'] ?? $role['role_uuid'] ?? null;
    }

    private function request()
    {
        $apiKey = config('services.servicem8.api_key');
        $token = config('services.servicem8.token');

        if (! $apiKey && ! $token) {
            throw new RuntimeException('ServiceM8 API key is not configured.');
        }

        $request = Http::acceptJson();

        if ($apiKey) {
            return $request->withHeaders(['X-Api-Key' => $apiKey]);
        }

        return $request->withToken($token);
    }

    private function normaliseMobile(?string $mobile): string
    {
        $number = preg_replace('/[^0-9+]/', '', (string) $mobile);

        if ($number === '') {
            throw new RuntimeException('Cannot send ServiceM8 SMS because the subcontractor mobile number is missing.');
        }

        if (str_starts_with($number, '+')) {
            return $number;
        }

        if (str_starts_with($number, '04')) {
            return '+61'.substr($number, 1);
        }

        if (str_starts_with($number, '61')) {
            return '+'.$number;
        }

        throw new RuntimeException('Cannot send ServiceM8 SMS because the mobile number must be in E.164 format, for example +61400111222.');
    }

    private function errorMessage($response, string $fallback): string
    {
        $apiError = $response->json('error');
        $apiMessage = $response->json('error_description')
            ?: $response->json('message')
            ?: $response->body();

        if ($apiError === 'invalid_token' || $response->status() === 401) {
            return 'ServiceM8 authentication failed. Check SERVICEM8_API_KEY on the server and clear the Laravel config cache.';
        }

        return $apiMessage ?: $fallback;
    }
}
