<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MicrosoftGraphMailService
{
    public function configured(): bool
    {
        return collect([
            config('services.microsoft_graph.tenant_id'),
            config('services.microsoft_graph.client_id'),
            config('services.microsoft_graph.client_secret'),
            config('services.microsoft_graph.sender'),
        ])->every(fn ($value) => is_string($value) && trim($value) !== '');
    }

    public function send(string $recipient, string $subject, string $html): void
    {
        if (! $this->configured()) {
            throw new RuntimeException('Microsoft Graph mail is not configured.');
        }

        $sender = (string) config('services.microsoft_graph.sender');
        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->post(
                'https://graph.microsoft.com/v1.0/users/'.rawurlencode($sender).'/sendMail',
                [
                    'message' => [
                        'subject' => $subject,
                        'body' => [
                            'contentType' => 'HTML',
                            'content' => $html,
                        ],
                        'toRecipients' => [
                            ['emailAddress' => ['address' => $recipient]],
                        ],
                    ],
                    'saveToSentItems' => true,
                ]
            );

        if (! $response->successful()) {
            throw new RuntimeException(
                'Microsoft Graph rejected the email: '.$response->status().' '.$response->body()
            );
        }
    }

    private function accessToken(): string
    {
        return Cache::remember('microsoft-graph-mail-token', now()->addMinutes(50), function (): string {
            $tenantId = (string) config('services.microsoft_graph.tenant_id');
            $response = Http::asForm()->post(
                'https://login.microsoftonline.com/'.rawurlencode($tenantId).'/oauth2/v2.0/token',
                [
                    'client_id' => config('services.microsoft_graph.client_id'),
                    'client_secret' => config('services.microsoft_graph.client_secret'),
                    'scope' => 'https://graph.microsoft.com/.default',
                    'grant_type' => 'client_credentials',
                ]
            );

            if (! $response->successful() || ! is_string($response->json('access_token'))) {
                throw new RuntimeException(
                    'Microsoft Graph authentication failed: '.$response->status().' '.$response->body()
                );
            }

            return $response->json('access_token');
        });
    }
}
