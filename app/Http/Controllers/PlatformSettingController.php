<?php

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class PlatformSettingController extends Controller
{
    public function edit()
    {
        $settings = PlatformSetting::current();

        return view('pickdrop.settings.platform', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'sms_enabled' => ['nullable', 'boolean'],
            'sms_provider' => ['nullable', 'string', 'max:50'],
            'sms_api_url' => ['nullable', 'string', 'max:500'],
            'sms_api_key' => ['nullable', 'string', 'max:255'],
            'sms_sender' => ['nullable', 'string', 'max:50'],
            'fcm_enabled' => ['nullable', 'boolean'],
            'fcm_server_key' => ['nullable', 'string'],
            'jazzcash_enabled' => ['nullable', 'boolean'],
            'jazzcash_merchant_id' => ['nullable', 'string', 'max:50'],
            'jazzcash_password' => ['nullable', 'string', 'max:255'],
            'jazzcash_integrity_salt' => ['nullable', 'string', 'max:255'],
            'jazzcash_return_url' => ['nullable', 'url', 'max:500'],
            'easypaisa_enabled' => ['nullable', 'boolean'],
            'easypaisa_store_id' => ['nullable', 'string', 'max:50'],
            'easypaisa_hash_key' => ['nullable', 'string', 'max:255'],
            'easypaisa_return_url' => ['nullable', 'url', 'max:500'],
            'cancel_hours' => ['required', 'integer', 'min:0', 'max:168'],
            'cancel_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'geofence_meters' => ['required', 'integer', 'min:50', 'max:5000'],
            'referral_bonus' => ['required', 'numeric', 'min:0'],
            'pickup_otp_enabled' => ['nullable', 'boolean'],
        ]);

        $settings = PlatformSetting::current();
        foreach (['sms_api_key', 'fcm_server_key', 'jazzcash_password', 'jazzcash_integrity_salt', 'easypaisa_hash_key'] as $secret) {
            if (!filled($validated[$secret] ?? null)) {
                unset($validated[$secret]);
            }
        }

        $validated['sms_enabled'] = $request->boolean('sms_enabled');
        $validated['fcm_enabled'] = $request->boolean('fcm_enabled');
        $validated['jazzcash_enabled'] = $request->boolean('jazzcash_enabled');
        $validated['easypaisa_enabled'] = $request->boolean('easypaisa_enabled');
        $validated['pickup_otp_enabled'] = $request->boolean('pickup_otp_enabled');

        $settings->update($validated);

        return back()->with('success', 'Platform settings saved.');
    }
}
