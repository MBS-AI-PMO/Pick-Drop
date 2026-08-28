<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushService
{
    public function send(int $userId, string $title, string $body, array $data = []): void
    {
        $settings = PlatformSetting::current();
        if (!$settings->fcm_enabled || !filled($settings->fcm_server_key)) {
            return;
        }

        $tokens = DeviceToken::query()->where('user_id', $userId)->pluck('token')->filter()->all();
        if ($tokens === []) {
            return;
        }

        try {
            Http::timeout(15)
                ->withToken((string) $settings->fcm_server_key)
                ->withHeaders([
                    'Authorization' => 'key=' . $settings->fcm_server_key,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://fcm.googleapis.com/fcm/send', [
                    'registration_ids' => array_values($tokens),
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_map('strval', $data),
                    'priority' => 'high',
                ]);
        } catch (\Throwable $e) {
            Log::warning('FCM push failed', ['user_id' => $userId, 'error' => $e->getMessage()]);
        }
    }
}
