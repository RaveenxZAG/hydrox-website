<?php

namespace App\Http\Controllers\StaffPortal;

use App\Http\Controllers\Controller;
use App\Models\StaffMember;
use App\Models\StaffOtp;
use App\Services\ServiceM8StaffService;
use App\Services\StaffPortal\StaffIdentityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function login(Request $request): View
    {
        $action = in_array($request->query('action'), ['invoice', 'profile'], true)
            ? $request->query('action')
            : 'invoice';

        $request->session()->forget(['staff_member_id', 'staff_otp_mobile', 'staff_otp_identifier', 'staff_otp_channel']);
        session(['staff_portal_action' => $action]);

        return view('staff-portal.login', ['action' => $action]);
    }

    public function requestCode(Request $request, StaffIdentityService $identity, ServiceM8StaffService $serviceM8): RedirectResponse
    {
        $data = $request->validate(['identifier' => ['required', 'string', 'max:255']]);
        $identifier = trim($data['identifier']);
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
        $channel = $isEmail ? 'email' : 'sms';
        $normalized = $isEmail
            ? $identity->normalizeEmail($identifier)
            : $identity->normalizeMobile($identifier);
        $field = $isEmail ? 'identifier' : 'identifier';
        $key = 'staff-otp-request:'.$channel.'|'.$normalized.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors([$field => 'Too many verification attempts. Please wait before requesting another code.']);
        }

        $staffMatches = $isEmail
            ? $this->staffMatchesForEmail($normalized, $identity)
            : $this->staffMatchesForMobile($normalized, $identity);

        if ($staffMatches->count() > 1) {
            RateLimiter::hit($key, 300);

            return back()->withErrors([$field => 'Multiple subcontractor accounts match this login detail. Please contact support.']);
        }

        $staff = $staffMatches->first();

        if (! $staff) {
            RateLimiter::hit($key, 300);

            return back()->withErrors([$field => 'No active subcontractor profile was found for this mobile or email. Please contact administration.']);
        }

        if (! $staff->canAccessPortal()) {
            RateLimiter::hit($key, 300);

            return back()->withErrors([$field => 'Your subcontractor profile is currently inactive. Please contact administration.']);
        }

        $request->session()->forget('staff_member_id');
        StaffOtp::query()
            ->where('delivery_channel', $channel)
            ->when($channel === 'email', fn ($query) => $query->where('normalized_email', $normalized))
            ->when($channel === 'sms', fn ($query) => $query->where('normalized_mobile', $normalized))
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $code = (string) random_int(100000, 999999);
        StaffOtp::create([
            'staff_member_id' => $staff->id,
            'normalized_mobile' => $channel === 'sms' ? $normalized : 'email:'.sha1((string) $normalized),
            'normalized_email' => $channel === 'email' ? $normalized : null,
            'delivery_channel' => $channel,
            'otp_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(5),
            'request_ip' => $request->ip(),
            'sent_at' => now(),
        ]);

        try {
            if ($channel === 'email') {
                $body = "Hi {$staff->fullName()},\n\nYour Hydrox Facility Management verification code is {$code}.\n\nThis code expires in 5 minutes.\n\nHydrox Facility Management";
                $serviceM8->sendEmail(
                    $staff->email,
                    'Hydrox Facility Management verification code',
                    $body,
                    $this->htmlBody($body)
                );
            } else {
                $serviceM8->sendSms($staff->mobile, "Hydrox Facility Management verification code: {$code}. This code expires in 5 minutes.");
            }
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([$field => 'The verification code could not be sent. Please contact administration.']);
        }

        RateLimiter::hit($key, 60);
        session([
            'staff_otp_mobile' => $channel === 'sms' ? $normalized : null,
            'staff_otp_identifier' => $normalized,
            'staff_otp_channel' => $channel,
            'staff_otp_test_code' => app()->environment('local') ? $code : null,
        ]);

        return redirect()->route('staff-portal.verify')->with('status', 'Verification code sent.');
    }

    public function verify(): View|RedirectResponse
    {
        if (! session('staff_otp_identifier')) {
            return redirect()->route('staff-portal.login')->withErrors(['identifier' => 'Please request a new verification code.']);
        }

        return view('staff-portal.verify');
    }

    public function checkCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'Please enter your verification code.',
            'code.digits' => 'The verification code must be 6 digits.',
        ]);

        $identifier = session('staff_otp_identifier');
        $channel = session('staff_otp_channel', session('staff_otp_mobile') ? 'sms' : null);

        if (! $identifier || ! $channel) {
            return redirect()->route('staff-portal.login')->withErrors(['identifier' => 'Please request a new verification code.']);
        }

        $otp = StaffOtp::query()
            ->where('delivery_channel', $channel)
            ->when($channel === 'email', fn ($query) => $query->where('normalized_email', $identifier))
            ->when($channel === 'sms', fn ($query) => $query->where('normalized_mobile', $identifier))
            ->latest()
            ->first();

        if (! $otp) {
            $this->logOtpFailure('otp_missing', $identifier, $request);

            return back()->withErrors(['code' => 'No active verification code was found. Please request a new code.']);
        }

        if ($otp->consumed_at) {
            $this->logOtpFailure('otp_already_used', $identifier, $request, $otp->staff_member_id);

            return back()->withErrors(['code' => 'This verification code has already been used. Please request a new code.']);
        }

        if ($otp->expires_at->isPast()) {
            $this->logOtpFailure('otp_expired', $identifier, $request, $otp->staff_member_id);

            return back()->withErrors(['code' => 'This verification code has expired. Please request a new code.']);
        }

        if ($otp->attempts >= 5) {
            $otp->forceFill(['consumed_at' => now()])->save();
            $this->logOtpFailure('otp_attempt_limit_reached', $identifier, $request, $otp->staff_member_id);

            return back()->withErrors(['code' => 'Too many incorrect attempts. Please request a new verification code.']);
        }

        if (! Hash::check($data['code'], $otp->otp_hash)) {
            $otp->increment('attempts');
            $otp->refresh();

            if ($otp->attempts >= 5) {
                $otp->forceFill(['consumed_at' => now()])->save();
                $this->logOtpFailure('otp_attempt_limit_reached', $identifier, $request, $otp->staff_member_id);

                return back()->withErrors(['code' => 'Too many incorrect attempts. Please request a new verification code.']);
            }

            $this->logOtpFailure('otp_incorrect', $identifier, $request, $otp->staff_member_id);

            return back()->withErrors(['code' => 'Incorrect verification code. Please check the code and try again.']);
        }

        $staffMatches = $channel === 'email'
            ? $this->staffMatchesForEmail($identifier, app(StaffIdentityService::class))
            : $this->staffMatchesForMobile($identifier, app(StaffIdentityService::class));

        if ($staffMatches->count() > 1) {
            $otp->forceFill(['consumed_at' => now()])->save();
            $this->logOtpFailure('otp_duplicate_staff_identity', $identifier, $request, $otp->staff_member_id);

            return redirect()->route('staff-portal.login')->withErrors(['identifier' => 'Multiple subcontractor accounts match this login detail. Please contact support.']);
        }

        $staff = $otp->staffMember;

        if (! $staff?->canAccessPortal()) {
            $this->logOtpFailure('otp_inactive_staff', $identifier, $request, $otp->staff_member_id);

            return redirect()->route('staff-portal.login')->withErrors(['identifier' => 'Your subcontractor profile is currently inactive. Please contact administration.']);
        }

        $otp->forceFill(['consumed_at' => now()])->save();
        $request->session()->regenerate();
        session(['staff_member_id' => $staff->id]);
        $request->session()->forget(['staff_otp_test_code', 'staff_otp_identifier', 'staff_otp_channel', 'staff_otp_mobile']);

        return redirect()->route(match (session('staff_portal_action')) {
            'profile' => 'staff-portal.profile',
            default => 'staff-portal.invoices',
        });
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['staff_member_id', 'staff_otp_mobile', 'staff_otp_identifier', 'staff_otp_channel', 'staff_otp_test_code', 'staff_portal_action']);

        return redirect()->route('staff-portal.login');
    }

    private function staffMatchesForMobile(?string $normalized, StaffIdentityService $identity)
    {
        if (! $normalized) {
            return collect();
        }

        return StaffMember::query()
            ->get()
            ->filter(fn (StaffMember $staff): bool => $identity->normalizeMobile($staff->mobile ?: $staff->normalized_mobile) === $normalized)
            ->values();
    }

    private function staffMatchesForEmail(?string $normalized, StaffIdentityService $identity)
    {
        if (! $normalized) {
            return collect();
        }

        return StaffMember::query()
            ->get()
            ->filter(fn (StaffMember $staff): bool => $identity->normalizeEmail($staff->email ?: $staff->normalized_email) === $normalized)
            ->values();
    }

    private function htmlBody(string $body): string
    {
        $lines = collect(preg_split('/\R/', $body) ?: [])
            ->map(fn (string $line): string => e($line))
            ->implode('<br>');

        return '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f8fb;padding:24px;color:#0f172a;">'
            .'<div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #d9e7f1;border-radius:14px;padding:24px;">'
            .'<p style="margin:0 0 8px;font-size:12px;font-weight:bold;letter-spacing:.08em;text-transform:uppercase;color:#0082c9;">Hydrox Facility Management Subcontractor Portal</p>'
            .'<p style="margin:0;font-size:15px;line-height:1.65;color:#334155;">'.$lines.'</p>'
            .'</div>'
            .'</div>';
    }

    private function logOtpFailure(string $reason, string $identifier, Request $request, ?int $staffId = null): void
    {
        Log::warning('Staff portal OTP verification failed.', [
            'reason' => $reason,
            'staff_member_id' => $staffId,
            'identifier' => $this->maskIdentifier($identifier),
            'ip' => $request->ip(),
        ]);
    }

    private function maskIdentifier(string $identifier): string
    {
        if (str_contains($identifier, '@')) {
            [$name, $domain] = array_pad(explode('@', $identifier, 2), 2, '');

            return substr($name, 0, 1).str_repeat('*', max(strlen($name) - 1, 1)).'@'.$domain;
        }

        return str_repeat('*', max(strlen($identifier) - 3, 0)).substr($identifier, -3);
    }
}
