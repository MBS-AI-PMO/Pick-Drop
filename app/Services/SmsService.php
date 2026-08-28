<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $phone, string $message): bool
    {
        $settings = PlatformSetting::current();
        $phone = preg_replace('/\D+/', '', $phone) ?: $phone;

        Log::info('SMS send', ['phone' => $phone, 'message' => $message]);

        if (!$settings->sms_enabled || !filled($settings->sms_api_url)) {
            return false;
        }

        $url = strtr($settings->sms_api_url, [
            '{phone}' => urlencode($phone),
            '{message}' => urlencode($message),
            '{key}' => urlencode((string) $settings->sms_api_key),
            '{sender}' => urlencode((string) $settings->sms_sender),
        ]);

        try {
            $response = str_contains($url, '{')
                ? Http::timeout(15)->asForm()->post($settings->sms_api_url, [
                    'phone' => $phone,
                    'to' => $phone,
                    'message' => $message,
                    'text' => $message,
                    'key' => $settings->sms_api_key,
                    'sender' => $settings->sms_sender,
                ])
                : Http::timeout(15)->get($url);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('SMS send failed', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
