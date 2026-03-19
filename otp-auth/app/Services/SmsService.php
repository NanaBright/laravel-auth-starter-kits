<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send SMS message.
     */
    public function send(string $to, string $message): bool
    {
        $provider = config('otp.sms.provider', 'log');

        return match ($provider) {
            'vonage' => $this->sendViaVonage($to, $message),
            'twilio' => $this->sendViaTwilio($to, $message),
            'log' => $this->sendViaLog($to, $message),
            default => $this->sendViaLog($to, $message),
        };
    }

    /**
     * Send SMS via Vonage.
     */
    protected function sendViaVonage(string $to, string $message): bool
    {
        try {
            $config = config('otp.sms.providers.vonage');

            $response = Http::post('https://rest.nexmo.com/sms/json', [
                'api_key' => $config['key'],
                'api_secret' => $config['secret'],
                'to' => $this->formatPhoneNumber($to),
                'from' => $config['from'],
                'text' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['messages'][0]['status'] ?? '1';
                return $status === '0';
            }

            Log::error('Vonage SMS failed', ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Vonage SMS exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send SMS via Twilio.
     */
    protected function sendViaTwilio(string $to, string $message): bool
    {
        try {
            $config = config('otp.sms.providers.twilio');

            $response = Http::withBasicAuth($config['sid'], $config['token'])
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$config['sid']}/Messages.json", [
                    'To' => $this->formatPhoneNumber($to),
                    'From' => $config['from'],
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Twilio SMS failed', ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Twilio SMS exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send SMS via log (development).
     */
    protected function sendViaLog(string $to, string $message): bool
    {
        $channel = config('otp.sms.providers.log.channel', 'daily');

        Log::channel($channel)->info('SMS Message', [
            'to' => $to,
            'message' => $message,
        ]);

        return true;
    }

    /**
     * Format phone number to E.164.
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove any non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Ensure it starts with +
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }
}
