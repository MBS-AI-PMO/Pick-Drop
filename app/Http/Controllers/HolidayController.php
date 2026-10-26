<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Holiday;
use App\Models\ShiftAttendance;
use App\Models\ShiftDayRun;
use App\Models\ShiftReplacement;
use App\Services\ShiftOpsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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

        $skipCounts = ShiftAttendance::query()
            ->whereIn('status', [ShiftAttendance::SKIPPED, ShiftAttendance::HOLIDAY])
            ->whereDate('date', '>=', $gridStart->toDateString())
            ->whereDate('date', '<=', $gridEnd->toDateString())
            ->selectRaw('DATE(date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $coverCounts = ShiftReplacement::query()
            ->whereDate('date', '>=', $gridStart->toDateString())
            ->whereDate('date', '<=', $gridEnd->toDateString())
            ->selectRaw('DATE(date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $tripCounts = ShiftDayRun::query()
            ->whereDate('date', '>=', $gridStart->toDateString())
            ->whereDate('date', '<=', $gridEnd->toDateString())
            ->whereNotIn('status', [ShiftDayRun::SKIPPED])
            ->selectRaw('DATE(date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $days = [];
        for ($day = $gridStart->copy(); $day->lte($gridEnd); $day->addDay()) {
            $key = $day->toDateString();
            $days[] = [
                'date' => $key,
                'label' => $day->day,
                'in_month' => $day->month === $cursor->month,
                'is_today' => $day->isToday(),
                'holidays' => $monthHolidays->get($key, collect()),
                'trips' => (int) ($tripCounts[$key] ?? 0),
                'skips' => (int) ($skipCounts[$key] ?? 0),
                'covers' => (int) ($coverCounts[$key] ?? 0),
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
        $validator = Validator::make($request->all(), [
            'date' => ['bail', 'required', 'date', 'after_or_equal:today'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:'.implode(',', array_keys(Holiday::TYPES))],
            'all_cities' => ['nullable', 'boolean'],
            'city_ids' => ['nullable', 'array'],
            'city_ids.*' => ['integer', 'exists:cities,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'], // single city (older form / API clients)
        ], [
            'date.required' => 'Choose a date.',
            'date.date' => 'Choose a valid date.',
            'date.after_or_equal' => 'Past dates cannot be selected. Choose today or a later date.',
            'name.required' => 'Enter a title.',
            'type.in' => 'Choose a valid type.',
            'city_ids.*.exists' => 'Choose valid cities.',
        ]);

        // One or more specific cities, or "All cities" (stored as a holiday with no city).
        $allCities = $request->boolean('all_cities');
        $cityIds = collect((array) $request->input('city_ids', []))
            ->when($request->filled('city_id'), fn ($ids) => $ids->push($request->input('city_id')))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $validator->after(function ($validator) use ($request, $allCities, $cityIds) {
            if (! $allCities && $cityIds->isEmpty()) {
                $validator->errors()->add('city_ids', 'Select at least one city, or choose All cities.');
                return;
            }

            // Reject an announcement that already exists for the same date, city and title.
            $title = trim((string) $request->input('name'));
            $date = rescue(fn () => Carbon::parse($request->input('date'))->toDateString(), null, false);
            if ($title === '' || ! $date || $validator->errors()->isNotEmpty()) {
                return;
            }

            $existing = Holiday::query()
                ->whereDate('date', $date)
                ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($title)])
                ->where(function ($q) use ($allCities, $cityIds) {
                    $allCities ? $q->whereNull('city_id') : $q->whereIn('city_id', $cityIds->all());
                })
                ->with('city')
                ->get();

            if ($existing->isNotEmpty()) {
                $where = $existing->map(fn (Holiday $h) => $h->city?->name ?? 'All cities')->unique()->sort()->implode(', ');
                $validator->errors()->add(
                    'name',
                    'Announcement already exists for this date, city, and title ('.$where.').'
                );
            }
        });

        $validated = $validator->validate();

        $targets = $allCities ? collect([null]) : $cityIds;
        $created = collect();
        $existing = 0;

        foreach ($targets as $cityId) {
            // Duplicates are rejected during validation above; firstOrCreate is only a safety net
            // (e.g. two admins submitting the same announcement at the same moment).
            $holiday = Holiday::firstOrCreate(
                ['date' => $validated['date'], 'city_id' => $cityId, 'name' => trim($validated['name'])],
                ['type' => $validated['type']]
            );

            if ($holiday->wasRecentlyCreated) {
                app(ShiftOpsService::class)->applyHolidayToShifts($holiday);
                $created->push($cityId);
            } else {
                $existing++;
            }
        }

        $date = Carbon::parse($validated['date']);
        $where = $allCities
            ? 'all cities'
            : City::whereIn('id', $created->all())->orderBy('name')->pluck('name')->implode(', ');

        if ($created->isEmpty()) {
            $message = 'This off day was already announced for the selected '.($allCities ? 'scope' : 'cities').'.';
        } else {
            $message = 'Off day announced for '.$where.'. Matching pickups will be skipped.'
                .($existing > 0 ? ' ('.$existing.' already announced.)' : '');
        }

        return redirect()
            ->route('holidays.index', ['year' => $date->year, 'month' => $date->month])
            ->with('success', $message);
    }

    public function destroy(Request $request, Holiday $holiday)
    {
        $date = $holiday->date?->copy() ?? now();
        $label = $holiday->name.' ('.($holiday->city?->name ?? 'All cities').', '.$date->format('d M Y').')';

        $back = redirect()->route('holidays.index', [
            'year' => $request->integer('year') ?: $date->year,
            'month' => $request->integer('month') ?: $date->month,
        ]);

        try {
            $holiday->delete();
        } catch (\Throwable $e) {
            Log::error('Failed to remove announcement', ['holiday_id' => $holiday->id, 'error' => $e->getMessage()]);

            return $back->with('error', 'Could not remove the announcement '.$label.'. Please try again.');
        }

        return $back->with('success', 'Announcement removed: '.$label.'.');
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
