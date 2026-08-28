<?php

namespace App\Http\Controllers;

use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentSettingController extends Controller
{
    public function edit()
    {
        $settings = PaymentSetting::current();
        $banks = \App\Support\PakistaniBanks::names();

        return view('pickdrop.payments.settings', compact('settings', 'banks'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'tax_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'bank_enabled' => ['nullable', 'boolean'],
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_account_title' => ['required', 'string', 'max:255'],
            'bank_account_number' => ['required', 'string', 'max:100'],
            'bank_iban' => ['nullable', 'string', 'max:50'],
            'bank_swift' => ['nullable', 'string', 'max:50'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $settings = PaymentSetting::current();

            $payload = [
                'company_name' => $validated['company_name'],
                'company_email' => $validated['company_email'] ?? null,
                'company_phone' => $validated['company_phone'] ?? null,
                'company_address' => $validated['company_address'] ?? null,
                'invoice_prefix' => strtoupper($validated['invoice_prefix']),
                'tax_percent' => $validated['tax_percent'],
                'bank_enabled' => true,
                'stripe_enabled' => false,
                'bank_name' => $validated['bank_name'],
                'bank_account_title' => $validated['bank_account_title'],
                'bank_account_number' => $validated['bank_account_number'],
                'bank_iban' => $validated['bank_iban'] ?? null,
                'bank_swift' => $validated['bank_swift'] ?? null,
                'bank_branch' => $validated['bank_branch'] ?? null,
            ];

            $settings->update($payload);

            return redirect()
                ->route('payments.settings')
                ->with('success', 'Payment settings saved.');
        } catch (Throwable $e) {
            Log::error('Failed to save payment settings', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Failed to save payment settings: ' . $e->getMessage());
        }
    }
}
