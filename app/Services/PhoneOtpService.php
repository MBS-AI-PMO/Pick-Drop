<?php

namespace App\Services;

use App\Mail\PhoneVerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class PhoneOtpService
{
    public function send(User $user): void
    {
        $phone = trim((string) $user->phone);
        if ($phone === '') {
            throw new RuntimeException('Add a phone number before requesting an OTP.');
        }

        if ($user->phone_verified_at) {
            throw new RuntimeException('Phone number is already verified.');
        }

        $otp = (string) random_int(100000, 999999);

        $user->update([
            'phone_otp' => $otp,
            'phone_otp_expires_at' => now()->addMinutes(10),
        ]);

        Log::info('Phone OTP generated', [
            'user_id' => $user->id,
            'phone' => $phone,
            'otp' => $otp,
        ]);

        if ($user->email) {
            Mail::to($user->email)->send(new PhoneVerificationCodeMail($otp, $user->name ?? 'User', $phone));
        }

        app(SmsService::class)->send($phone, 'PickDrop code: ' . $otp . '. Valid 10 minutes.');
    }

    public function verify(User $user, string $code): User
    {
        if ($user->phone_verified_at) {
            return $user;
        }

        if (!$user->phone_otp || $user->phone_otp !== $code) {
            throw new RuntimeException('Invalid phone verification code.');
        }

        if ($user->phone_otp_expires_at && $user->phone_otp_expires_at->lt(now())) {
            throw new RuntimeException('Phone verification code expired.');
        }

        $user->update([
            'phone_verified_at' => now(),
            'phone_otp' => null,
            'phone_otp_expires_at' => null,
        ]);

        return $user->fresh();
    }
}
