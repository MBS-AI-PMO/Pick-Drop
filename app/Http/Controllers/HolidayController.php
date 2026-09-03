<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Holiday;
use App\Services\ShiftOpsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $cursor = $this->monthCursor($request);
        $gridStart = $cursor->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $monthHolidays = Holiday::with('city')
            ->whereDate('date', '>=', $gridStart->toDateString())
            ->whereDate('date', '<=', $gridEnd->toDateString())
            ->orderBy('date')
            ->get()
            ->groupBy(fn (Holiday $holiday) => $holiday->date?->toDateString());

        $days = [];
        for ($day = $gridStart->copy(); $day->lte($gridEnd); $day->addDay()) {
            $key = $day->toDateString();
            $days[] = [
                'date' => $key,
                'label' => $day->day,
                'in_month' => $day->month === $cursor->month,
                'is_today' => $day->isToday(),
                'holidays' => $monthHolidays->get($key, collect()),
            ];
        }

        $announcements = Holiday::with('city')
            ->whereMonth('date', $cursor->month)
            ->whereYear('date', $cursor->year)
            ->orderBy('date')
            ->get();

        $cities = City::query()->orderBy('name')->get(['id', 'name']);
        $prev = $cursor->copy()->subMonth();
        $next = $cursor->copy()->addMonth();

        return view('pickdrop.holidays.index', [
            'days' => $days,
            'announcements' => $announcements,
            'cities' => $cities,
            'types' => Holiday::TYPES,
            'cursor' => $cursor,
            'prevMonthUrl' => route('holidays.index', ['year' => $prev->year, 'month' => $prev->month]),
            'nextMonthUrl' => route('holidays.index', ['year' => $next->year, 'month' => $next->month]),
            'todayUrl' => route('holidays.index'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:'.implode(',', array_keys(Holiday::TYPES))],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
        ]);

        $holiday = Holiday::create($validated);
        app(ShiftOpsService::class)->applyHolidayToShifts($holiday);

        $date = Carbon::parse($validated['date']);

        return redirect()
            ->route('holidays.index', ['year' => $date->year, 'month' => $date->month])
            ->with('success', 'Off day announced. Matching pickups will be skipped.');
    }

    public function destroy(Request $request, Holiday $holiday)
    {
        $date = $holiday->date?->copy() ?? now();
        $holiday->delete();

        return redirect()
            ->route('holidays.index', [
                'year' => $request->integer('year') ?: $date->year,
                'month' => $request->integer('month') ?: $date->month,
            ])
            ->with('success', 'Announcement removed.');
    }

    private function monthCursor(Request $request): Carbon
    {
        $year = $request->integer('year') ?: now()->year;
        $month = $request->integer('month') ?: now()->month;

        $year = max(2000, min(2100, $year));
        $month = max(1, min(12, $month));

        return Carbon::createFromDate($year, $month, 1)->startOfDay();
    }
}
