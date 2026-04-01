<?php

namespace App\Http\Controllers;

use App\Models\PickDropCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PickDropChargeController extends Controller
{
    public function index()
    {
        try {
            $charge = PickDropCharge::firstOrCreate(
                ['id' => 1],
                [
                    'per_km_rate' => 0,
                    'currency' => 'PKR',
                    'is_active' => true,
                ]
            );

            return view('pickdrop.charges.index', compact('charge'));
        } catch (\Throwable $e) {
            Log::error('Failed to load pick-drop charges page', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to load charges settings: ' . $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'per_km_rate' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        try {
            $charge = PickDropCharge::firstOrCreate(
                ['id' => 1],
                ['per_km_rate' => 0, 'currency' => 'PKR', 'is_active' => true]
            );

            $charge->update([
                'per_km_rate' => $request->input('per_km_rate'),
                'currency' => strtoupper($request->input('currency', 'PKR')),
                'is_active' => $request->boolean('is_active'),
            ]);

            return redirect()
                ->route('charges.index')
                ->with('success', 'Pick-drop charges updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update pick-drop charges', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update charges: ' . $e->getMessage());
        }
    }
}

