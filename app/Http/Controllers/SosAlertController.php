<?php

namespace App\Http\Controllers;

use App\Models\SosAlert;
use App\Services\ShiftDayService;
use App\Support\AppPagination;
use Illuminate\Http\Request;
use RuntimeException;

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

    public function endTrip(Request $request, SosAlert $sosAlert)
    {
        $driver = $sosAlert->user;
        if (!$driver || $driver->role !== 'driver') {
            return back()->with('error', 'This alert is not from a driver.');
        }

        try {
            app(ShiftDayService::class)->endTripByAdmin($driver);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $sosAlert->update([
            'status' => SosAlert::RESOLVED,
            'handled_by' => $request->user()->id,
            'resolved_at' => now(),
            'acknowledged_at' => $sosAlert->acknowledged_at ?: now(),
        ]);

        return back()->with('success', 'Trip ended.');
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

    public function destroy(SosAlert $sosAlert)
    {
        $sosAlert->delete();

        return redirect()
            ->route('sos.index')
            ->with('success', 'SOS alert deleted.');
    }

    public function clear()
    {
        SosAlert::query()->delete();

        return redirect()
            ->route('sos.index')
            ->with('success', 'All SOS alerts cleared.');
    }
}
