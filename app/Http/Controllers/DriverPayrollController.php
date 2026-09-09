<?php

namespace App\Http\Controllers;

use App\Models\DriverPayrollBill;
use App\Models\PickupRequest;
use App\Models\User;
use App\Services\DriverPayrollService;
use App\Support\AppPagination;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use RuntimeException;

class DriverPayrollController extends Controller
{
    public function index(Request $request, DriverPayrollService $payrolls)
    {
        $readyMonth = $this->monthOrNull($request->query('ready_month'));
        $readyRows = $payrolls->groupedReadyForBilling($readyMonth);
        $groupedReady = $this->paginateCollection($readyRows, $request, 'ready_page');

        $billsQuery = DriverPayrollBill::query()
            ->with(['driver', 'pickupRequest.student'])
            ->latest('id');

        if ($request->filled('status')) {
            $billsQuery->where('status', $request->string('status')->toString());
        }

        if ($request->filled('bill_month')) {
            $month = $this->monthOrNull($request->query('bill_month'));
            if ($month) {
                $start = Carbon::parse($month.'-01')->startOfMonth();
                $end = $start->copy()->endOfMonth();

                $billsQuery->whereDate('period_start', '<=', $end->toDateString())
                    ->whereDate('period_end', '>=', $start->toDateString());
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $billsQuery->where(function ($query) use ($search) {
                $query->where('bill_number', 'like', "%{$search}%")
                    ->orWhereHas('driver', function ($driver) use ($search) {
                        $driver->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('pickupRequest.student', function ($student) use ($search) {
                        $student->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $bills = $billsQuery->paginate(AppPagination::PER_PAGE)->withQueryString();

        $summary = [
            'pending_count' => DriverPayrollBill::query()
                ->where('status', DriverPayrollBill::STATUS_PENDING)
                ->count(),
            'pending' => DriverPayrollBill::query()
                ->where('status', DriverPayrollBill::STATUS_PENDING)
                ->sum('calculated_amount'),
            'approved' => DriverPayrollBill::query()
                ->where('status', DriverPayrollBill::STATUS_APPROVED)
                ->sum('calculated_amount'),
            'paid' => DriverPayrollBill::query()
                ->where('status', DriverPayrollBill::STATUS_PAID)
                ->sum('calculated_amount'),
        ];

        $billMonths = DriverPayrollBill::query()
            ->latest('period_start')
            ->pluck('period_start')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m'))
            ->unique()
            ->values()
            ->map(fn (string $value) => [
                'value' => $value,
                'label' => Carbon::parse($value.'-01')->format('F Y'),
            ])
            ->all();

        $readyMonths = $payrolls->readyBillingMonths();

        return view('pickdrop.driver-payroll.index', compact(
            'bills',
            'summary',
            'groupedReady',
            'readyMonth',
            'readyMonths',
            'billMonths'
        ));
    }

    public function driver(Request $request, User $user, DriverPayrollService $payrolls)
    {
        abort_unless(strcasecmp((string) $user->role, 'driver') === 0, 404);

        $view = $request->string('view')->toString();
        $view = in_array($view, ['monthly', 'weekly', 'daily'], true) ? $view : 'monthly';
        $month = $this->monthOrNull($request->query('month')) ?? now()->format('Y-m');
        $week = max(1, min(5, $request->integer('week', 1)));
        $date = $this->dateOrToday($request->query('date'));

        $overview = $payrolls->driverOverview($user, $view, $month, $week, $date);

        return view('pickdrop.driver-payroll.driver', [
            'driver' => $user,
            'overview' => $overview,
            'view' => $view,
            'month' => $month,
            'week' => $week,
            'date' => $date,
        ]);
    }

    public function show(DriverPayrollBill $bill)
    {
        $bill->load(['driver', 'pickupRequest.student', 'approver', 'payer']);

        return view('pickdrop.driver-payroll.show', compact('bill'));
    }

    public function generate(Request $request, DriverPayrollService $payrolls)
    {
        $validated = $request->validate([
            'pickup_request_id' => ['required', 'integer', 'exists:pickup_requests,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ]);

        $pickupRequest = PickupRequest::query()
            ->with(['driver', 'attendances'])
            ->findOrFail($validated['pickup_request_id']);

        try {
            $bill = $payrolls->generateBill(
                $pickupRequest,
                $validated['period_start'],
                $validated['period_end'],
                $request->user()
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('driver-payroll.show', $bill)
            ->with('success', 'Driver bill generated successfully.');
    }

    public function generateAll(Request $request, User $user, DriverPayrollService $payrolls)
    {
        abort_unless(strcasecmp((string) $user->role, 'driver') === 0, 404);

        try {
            if ($request->filled('month')) {
                $generated = $payrolls->generateBillsForDriverMonth(
                    $user,
                    $this->monthOrNull($request->input('month')) ?? now()->format('Y-m'),
                    $request->user()
                );
            } else {
                $generated = $payrolls->generateAllReadyForDriver($user, $request->user());
            }
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', count($generated).' driver bill(s) generated.');
    }

    public function approve(Request $request, DriverPayrollBill $bill, DriverPayrollService $payrolls)
    {
        try {
            $payrolls->approve($bill, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Driver bill approved.');
    }

    public function pay(Request $request, DriverPayrollBill $bill, DriverPayrollService $payrolls)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $payrolls->markPaid($bill, $request->user(), $validated['notes'] ?? null);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Driver payment recorded.');
    }

    public function reject(Request $request, DriverPayrollBill $bill, DriverPayrollService $payrolls)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $payrolls->reject($bill, $request->user(), $validated['notes'] ?? null);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Driver bill rejected.');
    }

    private function monthOrNull(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m', $value)->format('Y-m');
        } catch (\Throwable) {
            return null;
        }
    }

    private function dateOrToday(mixed $value): string
    {
        try {
            return Carbon::parse($value ?: now())->toDateString();
        } catch (\Throwable) {
            return now()->toDateString();
        }
    }

    private function paginateCollection($items, Request $request, string $pageName): LengthAwarePaginator
    {
        $page = max(1, $request->integer($pageName, 1));
        $perPage = AppPagination::PER_PAGE;

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
                'query' => $request->query(),
            ]
        );
    }
}
