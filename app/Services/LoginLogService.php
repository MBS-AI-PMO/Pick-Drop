<?php

namespace App\Services;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class LoginLogService
{
    public function record(
        Request $request,
        string $channel,
        string $status,
        ?User $user = null,
        ?string $email = null
    ): void {
        try {
            $agent = $request->userAgent();

            LoginLog::query()->create([
                'user_id' => $user?->id,
                'name' => $user?->name,
                'email' => $user?->email ?: $email,
                'role' => $user?->role,
                'channel' => $channel,
                'status' => $status,
                'ip_address' => $request->ip(),
                'user_agent' => $agent ? Str::limit($agent, 1000, '') : null,
            ]);
        } catch (Throwable) {
            // Never block login if logging fails.
        }
    }
}
