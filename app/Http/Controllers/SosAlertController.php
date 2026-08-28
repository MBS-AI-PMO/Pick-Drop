<?php

namespace App\Http\Controllers;

use App\Models\SosAlert;
use App\Support\AppPagination;
use Illuminate\Http\Request;

class SosAlertController extends Controller
{
    public function index(Request $request)
    {
        $query = SosAlert::with(['user', 'pickupRequest', 'handler'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $alerts = $query->paginate(AppPagination::PER_PAGE)->withQueryString();
        $counts = [
            'open' => SosAlert::where('status', SosAlert::OPEN)->count(),
            'all' => SosAlert::count(),
        ];

        return view('pickdrop.sos.index', compact('alerts', 'counts'));
    }

    public function show(SosAlert $sosAlert)
    {
        $sosAlert->load(['user', 'pickupRequest.parent', 'pickupRequest.driver', 'handler']);

        return view('pickdrop.sos.show', ['alert' => $sosAlert]);
    }

    public function acknowledge(Request $request, SosAlert $sosAlert)
    {
        if ($sosAlert->status === SosAlert::RESOLVED) {
            return back()->with('error', 'This SOS is already resolved.');
        }

        $sosAlert->update([
            'status' => SosAlert::ACKNOWLEDGED,
            'handled_by' => $request->user()->id,
            'acknowledged_at' => now(),
        ]);

        return back()->with('success', 'SOS acknowledged.');
    }

    public function resolve(Request $request, SosAlert $sosAlert)
    {
        $sosAlert->update([
            'status' => SosAlert::RESOLVED,
            'handled_by' => $request->user()->id,
            'resolved_at' => now(),
            'acknowledged_at' => $sosAlert->acknowledged_at ?: now(),
        ]);

        return back()->with('success', 'SOS marked resolved.');
    }
}
