<?php

namespace App\Services;

use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\WalletTransaction;

class ReferralService
{
    public function creditOnFirstPaidInvoice(User $customer): void
    {
        if (!$customer->referred_by) {
            return;
        }

        $already = WalletTransaction::query()
            ->where('referred_user_id', $customer->id)
            ->where('reason', 'referral_bonus')
            ->exists();

        if ($already) {
            return;
        }

        $bonus = (float) PlatformSetting::current()->referral_bonus;
        if ($bonus <= 0) {
            return;
        }

        $referrer = User::query()->find($customer->referred_by);
        if (!$referrer) {
            return;
        }

        $referrer->increment('referral_balance', $bonus);
        WalletTransaction::query()->create([
            'user_id' => $referrer->id,
            'amount' => $bonus,
            'type' => 'credit',
            'reason' => 'referral_bonus',
            'referred_user_id' => $customer->id,
        ]);

        app(AppNotificationService::class)->notify(
            $referrer->id,
            'referral_bonus',
            'Referral bonus',
            sprintf('You earned PKR %s because %s completed a payment.', number_format($bonus, 2), $customer->name),
            ['referred_user_id' => $customer->id]
        );
    }
}
