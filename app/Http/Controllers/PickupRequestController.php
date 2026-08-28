<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\PickupRequest;
use App\Support\AppPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PickupRequestController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = PickupRequest::with(['parent', 'student', 'city', 'area', 'dropArea', 'driver', 'vehicle', 'latestInvoice'])
                ->latest();

            if ($request->input('status') === 'in_progress') {
                $query->whereIn('status', ['accepted', 'picked_up', 'dropped']);
            } elseif ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('city_id')) {
                $query->where('city_id', $request->integer('city_id'));
            }

            if ($request->filled('search')) {
                $search = trim((string) $request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('pickup_point', 'like', "%{$search}%")
                        ->orWhere('drop_point', 'like', "%{$search}%")
                        ->orWhereHas('parent', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('student', function ($sq) use ($search) {
                            $sq->where('name', 'like', "%{$search}%");
                        });
                });
            }

            $requests = $query->paginate(AppPagination::PER_PAGE)->withQueryString();
            $cities = City::query()->orderBy('name')->get(['id', 'name']);
            $counts = [
                'all' => PickupRequest::count(),
                'pending' => PickupRequest::where('status', 'pending')->count(),
                'accepted' => PickupRequest::whereIn('status', ['accepted', 'picked_up', 'dropped'])->count(),
                'completed' => PickupRequest::where('status', 'completed')->count(),
            ];

            return view('pickdrop.pickup-requests.index', compact('requests', 'cities', 'counts'));
        } catch (\Throwable $e) {
            Log::error('Failed to load pickup requests', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to load pickup requests: ' . $e->getMessage());
        }
    }

    public function show(PickupRequest $pickupRequest)
    {
        $pickupRequest->load([
            'parent',
            'student.city',
            'student.pickupArea',
            'city',
            'area',
            'dropArea',
            'driver',
            'vehicle.category',
            'latestInvoice.items',
            'latestInvoice.payments',
            'attendances',
            'ratings.fromUser',
            'issues.user',
        ]);

        $eligibleDrivers = $pickupRequest->status === 'pending' && !$pickupRequest->driver_id
            ? app(\App\Services\PickupRequestMatchingService::class)->eligibleDrivers($pickupRequest)
            : collect();

        return view('pickdrop.pickup-requests.show', [
            'requestItem' => $pickupRequest,
            'eligibleDrivers' => $eligibleDrivers,
        ]);
    }

    public function markDriverPaid(PickupRequest $pickupRequest)
    {
        if (!$pickupRequest->driver_id) {
            return redirect()
                ->route('pickup-requests.show', $pickupRequest)
                ->with('error', 'Assign a driver before recording a payout.');
        }

        if (!$pickupRequest->isShiftPaid()) {
            return redirect()
                ->route('pickup-requests.show', $pickupRequest)
                ->with('error', 'Pay the driver only after the customer advance payment is confirmed.');
        }

        $pickupRequest->update([
            'driver_payout_status' => PickupRequest::DRIVER_PAYOUT_PAID,
            'driver_payout_paid_at' => now(),
        ]);

        if ($pickupRequest->driver_id) {
            app(\App\Services\AppNotificationService::class)->notify(
                (int) $pickupRequest->driver_id,
                'driver_payout_paid',
                'Monthly payout sent',
                sprintf(
                    'PickDrop paid your month-end rate for request #%d.',
                    $pickupRequest->id
                ),
                ['pickup_request_id' => $pickupRequest->id]
            );
        }

        return redirect()
            ->route('pickup-requests.show', $pickupRequest)
            ->with('success', 'Driver month-end payout marked as paid.');
    }

    public function assignDriver(Request $request, PickupRequest $pickupRequest)
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $driver = \App\Models\User::query()->findOrFail($validated['driver_id']);

        try {
            app(\App\Services\PickupRequestAssignmentService::class)
                ->assign($pickupRequest, $driver, 'admin');
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('pickup-requests.show', $pickupRequest)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('pickup-requests.show', $pickupRequest)
            ->with('success', 'Driver assigned. Advance invoice created.');
    }
}
