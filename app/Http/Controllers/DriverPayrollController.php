<?php

namespace App\Http\Controllers;

use App\Models\DriverPayroll;
use App\Models\DriverPayrollItem;
use App\Services\AttendanceService;
use App\Services\DriverPayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use RuntimeException;

class DriverPayrollController extends Controller
{
    public function index(Request $request, DriverPayrollService $payrolls)
    {
        $month = $this->month($request);

        if ($month === now()->format('Y-m')) {
            $payrolls->generate($month);
        }

        $rows = DriverPayroll::query()
            ->with('driver')
            ->withCount('items')
            ->where('month', $month)
            ->orderByDesc('expected_net')
            ->get();

        $cursor = Carbon::createFromFormat('Y-m', $month);

        return view('pickdrop.payrolls.index', [
            'month' => $month,
            'cursor' => $cursor,
            'prev' => $cursor->copy()->subMonth()->format('Y-m'),
            'next' => $cursor->copy()->addMonth()->format('Y-m'),
            'monthEnded' => now()->gte($cursor->copy()->endOfMonth()->addDay()->startOfDay()),
            'payrolls' => $rows,
            'totals' => [
                'drivers' => $rows->count(),
                'present' => $rows->sum('worked_days'),
                'upcoming' => $rows->sum('upcoming_days'),
                'cut' => $rows->sum('leave_days') + $rows->sum('absent_days'),
                'earned' => $rows->sum('net'),
                'expected' => $rows->sum('expected_net'),
                'deduction' => $rows->sum('deduction'),
            ],
        ]);
    }

    public function recalculate(Request $request, DriverPayrollService $payrolls)
    {
        $month = $this->month($request);
        $count = $payrolls->generate($month);

        return redirect()
            ->route('payrolls.index', ['month' => $month])
            ->with('success', $count . ' driver payroll(s) updated for ' . Carbon::createFromFormat('Y-m', $month)->format('F Y') . '.');
    }

    public function show(DriverPayroll $payroll, DriverPayrollService $payrolls)
    {
        $payroll->load(['driver', 'items.pickupRequest.parent', 'items.pickupRequest.student']);

        $shifts = $payroll->items->map(function (DriverPayrollItem $item) use ($payroll, $payrolls) {
            $request = $item->pickupRequest;

            return [
                'item' => $item,
                'request' => $request,
                'days' => $request ? $payrolls->dayBreakdown($request, $payroll->month) : [],
            ];
        });

        return view('pickdrop.payrolls.show', [
            'payroll' => $payroll,
            'shifts' => $shifts,
            'monthLabel' => Carbon::createFromFormat('Y-m', $payroll->month)->format('F Y'),
        ]);
    }

    public function markDay(
        Request $request,
        DriverPayroll $payroll,
        AttendanceService $attendance,
        DriverPayrollService $payrolls
    ) {
        if ($payroll->isLocked()) {
            return back()->with('error', 'This month is already paid. Days cannot be changed.');
        }

        $validated = $request->validate([
            'pickup_request_id' => ['required', 'integer', 'exists:pickup_requests,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:present,leave,absent'],
        ]);

        $item = $payroll->items()
            ->where('pickup_request_id', $validated['pickup_request_id'])
            ->first();

        if (!$item?->pickupRequest) {
            return back()->with('error', 'That shift is not on this payroll.');
        }

        try {
            $attendance->setByAdmin(
                $item->pickupRequest,
                $validated['date'],
                $validated['status'],
                $request->user()
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $payrolls->generate($payroll->month, $payroll->driver_id);

        $label = match ($validated['status']) {
            'present' => 'present (will be paid)',
            'leave' => 'leave (deducted)',
            default => 'no-show (deducted)',
        };

        return back()->with('success', Carbon::parse($validated['date'])->format('d M') . ' marked as ' . $label . '.');
    }

    public function approve(Request $request, DriverPayroll $payroll, DriverPayrollService $payrolls)
    {
        $payrolls->generate($payroll->month, $payroll->driver_id);
        $payrolls->approve($payroll->fresh(), $request->user());

        return back()->with('success', 'Numbers checked. You can pay this driver.');
    }

    public function pay(Request $request, DriverPayroll $payroll, DriverPayrollService $payrolls)
    {
        $payrolls->generate($payroll->month, $payroll->driver_id);
        $payroll = $payroll->fresh();

        if (!$payroll->monthEnded() && !$request->boolean('pay_now')) {
            return back()->with('error', 'Month is still running. Pay at month end, or tick “Pay earned amount now” to pay only the days already present.');
        }

        $payrolls->markPaid($payroll, $request->user());

        return back()->with('success', 'Driver marked as paid PKR ' . number_format((float) $payroll->net, 2) . '.');
    }

    private function month(Request $request): string
    {
        $value = (string) $request->input('month', $request->query('month', now()->format('Y-m')));
        try {
            return Carbon::createFromFormat('Y-m', $value)->format('Y-m');
        } catch (\Throwable $e) {
            return now()->format('Y-m');
        }
    }
}
