<?php

namespace App\Services;

use App\Mail\EmailVerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailVerificationService
{
    /**
     * Create a fresh 6-digit code and email it to the user.
     * Also mirrors the code onto users.otp for driver verify compatibility.
     */
    public function issueAndSend(User $user): array
    {
        $code = $this->storeCode($user);
        $sent = $this->sendCode($user, $code);

        return [
            'code' => $code,
            'sent' => $sent,
            'expires_in_minutes' => 30,
        ];
    }

    public function storeCode(User $user): string
    {
        DB::table('email_verification_tokens')
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        $code = (string) random_int(100000, 999999);

        DB::table('email_verification_tokens')->insert([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Driver verifyOtp still reads users.otp
        $user->forceFill(['otp' => $code])->save();

        return $code;
    }

    public function sendCode(User $user, ?string $code = null): bool
    {
        $code = $code ?: DB::table('email_verification_tokens')
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->orderByDesc('id')
            ->value('code');

        if (! $code) {
            return false;
        }

        $mailable = new EmailVerificationCodeMail((string) $code, $user->name ?? 'User');

        try {
            Mail::mailer(config('mail.default', 'smtp'))
                ->to($user->email)
                ->send($mailable);

            Log::info('Email verification mail sent', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return true;
        } catch (Throwable $primary) {
            report($primary);
            Log::error('Email verification mail failed (primary)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $primary->getMessage(),
            ]);
        }

        // Fallback: try alternate Hostinger port (587 / smtp)
        try {
            config([
                'mail.mailers.smtp_alt' => [
                    'transport' => 'smtp',
                    'scheme' => 'smtp',
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => 587,
                    'username' => config('mail.mailers.smtp.username'),
                    'password' => config('mail.mailers.smtp.password'),
                    'timeout' => 30,
                    'local_domain' => config('mail.mailers.smtp.local_domain', 'localhost'),
                ],
            ]);

            Mail::mailer('smtp_alt')->to($user->email)->send($mailable);

            Log::info('Email verification mail sent via smtp_alt:587', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return true;
        } catch (Throwable $secondary) {
            report($secondary);
            Log::error('Email verification mail failed (smtp_alt)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $secondary->getMessage(),
            ]);
        }

        // Local/dev safety net: keep code recoverable from logs
        if (app()->environment(['local', 'development', 'testing']) || config('app.debug')) {
            Log::warning('EMAIL VERIFICATION CODE (mail transport failed — use this code to verify)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'code' => $code,
            ]);
        }

        return false;
    }
}
