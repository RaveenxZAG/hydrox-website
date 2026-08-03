<?php

namespace App\Http\Controllers;

use App\Mail\CompanyProfileMail;
use App\Models\CompanyProfileEmailDelivery;
use App\Models\SystemSetting;
use App\Services\MicrosoftGraphMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CompanyProfileEmailController extends Controller
{
    private const MAX_RECIPIENTS = 50;

    public function create(): View
    {
        return view('emails.company-profile-create', [
            'business' => SystemSetting::businessInformation(),
            'deliveries' => CompanyProfileEmailDelivery::query()
                ->with('user:id,name')
                ->latest()
                ->paginate(15),
            'subject' => CompanyProfileMail::SUBJECT,
            'profileUrl' => CompanyProfileMail::PROFILE_URL,
            'maxRecipients' => self::MAX_RECIPIENTS,
        ]);
    }

    public function store(Request $request, MicrosoftGraphMailService $graph): RedirectResponse
    {
        $request->validate([
            'recipients' => ['required', 'string', 'max:10000'],
        ]);

        $recipients = $this->parseRecipients((string) $request->input('recipients'));

        if ($recipients === []) {
            throw ValidationException::withMessages(['recipients' => 'Enter at least one email address.']);
        }

        if (count($recipients) > self::MAX_RECIPIENTS) {
            throw ValidationException::withMessages([
                'recipients' => 'You can send to a maximum of '.self::MAX_RECIPIENTS.' recipients at once.',
            ]);
        }

        $validator = Validator::make(
            ['recipients' => $recipients],
            ['recipients.*' => ['required', 'email:rfc', 'max:255']],
            ['recipients.*.email' => 'One or more recipient email addresses are invalid.']
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages(['recipients' => 'One or more recipient email addresses are invalid.']);
        }

        $business = SystemSetting::businessInformation();
        $sent = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            $mail = new CompanyProfileMail($business);

            try {
                if ($graph->configured()) {
                    $graph->send($recipient, CompanyProfileMail::SUBJECT, $mail->render());
                } else {
                    Mail::to($recipient)->send($mail);
                }

                CompanyProfileEmailDelivery::create([
                    'user_id' => $request->user()->id,
                    'recipient' => $recipient,
                    'subject' => CompanyProfileMail::SUBJECT,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                $sent++;
            } catch (Throwable $exception) {
                report($exception);

                CompanyProfileEmailDelivery::create([
                    'user_id' => $request->user()->id,
                    'recipient' => $recipient,
                    'subject' => CompanyProfileMail::SUBJECT,
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error_message' => 'Delivery failed via the configured mail provider.',
                ]);
                $failed++;
            }
        }

        $message = "Sent {$sent} ".($sent === 1 ? 'email' : 'emails').'.';

        if ($failed > 0) {
            $message .= " {$failed} ".($failed === 1 ? 'delivery failed' : 'deliveries failed').'.';
        }

        return redirect()->route('emails.create')->with($failed > 0 ? 'error' : 'status', $message);
    }

    private function parseRecipients(string $input): array
    {
        $recipients = preg_split('/[\s,;]+/', trim($input), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $unique = [];

        foreach ($recipients as $recipient) {
            $normalized = mb_strtolower(trim($recipient));
            $unique[$normalized] = $normalized;
        }

        return array_values($unique);
    }
}
