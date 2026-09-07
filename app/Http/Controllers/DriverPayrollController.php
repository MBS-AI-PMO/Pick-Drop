<?php

namespace App\Http\Controllers;

use App\Models\DriverPayrollBill;
use App\Models\User;
use App\Services\DriverPayrollService;
use App\Support\AppPagination;
use Illuminate\Http\Request;
use RuntimeException;

class DriverPayrollController extends Controller
{
    public function __construct(
        private readonly DriverPayrollService $payroll,
    ) {
    }

    public function index(Request $request)
    {
        $query = DriverPayrollBill::query()
            ->with(['driver', 'pickupRequest.student'])
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('bill_month')) {
            $monthStart = \Illuminate\Support\Carbon::parse($request->bill_month . '-01')->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $query->whereDate('period_start', '<=', $monthEnd->toDateString())
                ->whereDate('period_end', '>=', $monthStart->toDateString());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                    ->orWhereHas('driver', fn ($dq) => $dq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"))
                    ->orWhereHas('pickupRequest.student', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $bills = $query->paginate(AppPagination::PER_PAGE)->withQueryString();

        $summary = [
            'pending' => DriverPayrollBill::where('status', DriverPayrollBill::STATUS_PENDING)->sum('calculated_amount'),
            'approved' => DriverPayrollBill::where('status', DriverPayrollBill::STATUS_APPROVED)->sum('calculated_amount'),
            'paid' => DriverPayrollBill::where('status', DriverPayrollBill::STATUS_PAID)->sum('calculated_amount'),
            'pending_count' => DriverPayrollBill::where('status', DriverPayrollBill::STATUS_PENDING)->count(),
        ];

        $readyMonth = $request->input('ready_month');
        $groupedReadyAll = $this->payroll->groupedReadyForBilling($readyMonth);
        $readyPage = max(1, (int) $request->input('ready_page', 1));
        $groupedReady = new \Illuminate\Pagination\LengthAwarePaginator(
            $groupedReadyAll->forPage($readyPage, AppPagination::PER_PAGE)->values(),
            $groupedReadyAll->count(),
            AppPagination::PER_PAGE,
            $readyPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'ready_page',
            ]
        );
        $readyMonths = $this->payroll->readyBillingMonths();

        $billMonths = DriverPayrollBill::query()
            ->orderByDesc('period_start')
            ->get(['period_start'])
            ->map(fn ($bill) => $bill->period_start?->format('Y-m'))
            ->filter()
            ->unique()
            ->values()
            ->map(fn ($m) => [
                'value' => $m,
                'label' => \Illuminate\Support\Carbon::parse($m . '-01')->format('F Y'),
            ])
            ->all();

        return view('pickdrop.driver-payroll.index', compact(
            'bills',
            'summary',
            'groupedReady',
            'readyMonths',
            'readyMonth',
            'billMonths'
        ));
    }

    public function driver(Request $request, User $user)
    {
        $role = strtolower(trim((string) $user->role));
        if (! in_array($role, ['driver'], true) && ! str_contains($role, 'driver')) {
            return redirect()
                ->route('driver-payroll.index')
                ->with('error', 'Selected user is not a driver.');
        }

        $view = $request->input('view', 'monthly');
        $month = $request->input('month', now()->format('Y-m'));
        $week = $request->integer('week', 1);
        $date = $request->input('date', now()->toDateString());

        $overview = $this->payroll->driverOverview($user, $view, $month, $week, $date);
        $user->load('driverVerification');

        return view('pickdrop.driver-payroll.driver', [
            'driver' => $user,
            'overview' => $overview,
            'view' => $view,
            'month' => $month,
            'week' => $week,
            'date' => $date,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'pickup_request_id' => ['required', 'integer', 'exists:pickup_requests,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ]);

        $pickupRequest = \App\Models\PickupRequest::query()->findOrFail($validated['pickup_request_id']);

        try {
            $bill = $this->payroll->generateBill(
                $pickupRequest,
                $validated['period_start'],
                $validated['period_end'],
                auth()->user()
            );
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('driver-payroll.show', $bill)
            ->with('success', 'Bill generated successfully.');
    }

    public function generateAll(Request $request, User $user)
    {
        $role = strtolower(trim((string) $user->role));
        if (! in_array($role, ['driver'], true) && ! str_contains($role, 'driver')) {
            return redirect()
                ->route('driver-payroll.index')
                ->with('error', 'Selected user is not a driver.');
        }

        if ($request->filled('month')) {
            $validated = $request->validate([
                'month' => ['required', 'date_format:Y-m'],
            ]);

            try {
                $bills = $this->payroll->generateBillsForDriverMonth(
                    $user,
                    $validated['month'],
                    auth()->user()
                );
            } catch (RuntimeException $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        } elseif ($request->filled('period_start') && $request->filled('period_end')) {
            $validated = $request->validate([
                'period_start' => ['required', 'date'],
                'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            ]);

            try {
                $bills = $this->payroll->generateBillsForDriver(
                    $user,
                    $validated['period_start'],
                    $validated['period_end'],
                    auth()->user()
                );
            } catch (RuntimeException $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        } else {
            try {
                $bills = $this->payroll->generateAllReadyForDriver($user, auth()->user());
            } catch (RuntimeException $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        $count = count($bills);

        return redirect()
            ->route('driver-payroll.index')
            ->with('success', "{$count} shift bill" . ($count === 1 ? '' : 's') . ' generated for ' . $user->name . '.');
    }

    public function show(DriverPayrollBill $bill)
    {
        $bill->load(['driver.driverVerification', 'pickupRequest.student', 'approver', 'payer']);

        return view('pickdrop.driver-payroll.show', [
            'bill' => $bill,
        ]);
    }

    public function approve(DriverPayrollBill $bill)
    {
        try {
            $this->payroll->approve($bill, auth()->user());
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('driver-payroll.show', $bill)
            ->with('success', 'Payment request approved.');
    }

    public function pay(Request $request, DriverPayrollBill $bill)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->payroll->markPaid($bill, auth()->user(), $validated['notes'] ?? null);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('driver-payroll.show', $bill)
            ->with('success', 'Driver payment recorded.');
    }

    public function reject(Request $request, DriverPayrollBill $bill)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->payroll->reject($bill, auth()->user(), $validated['notes'] ?? null);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('driver-payroll.index')
            ->with('success', 'Payment request rejected.');
    }
}
