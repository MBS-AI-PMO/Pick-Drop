<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Holiday;
use App\Services\ShiftOpsService;
use App\Support\AppPagination;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $holidays = Holiday::with('city')
            ->when($request->filled('city_id'), fn ($q) => $q->where('city_id', $request->integer('city_id')))
            ->orderByDesc('date')
            ->paginate(AppPagination::PER_PAGE)
            ->withQueryString();

        $cities = City::query()->orderBy('name')->get(['id', 'name']);

        return view('pickdrop.holidays.index', compact('holidays', 'cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:public,school,custom'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
        ]);

        $holiday = Holiday::create($validated);
        app(ShiftOpsService::class)->applyHolidayToShifts($holiday);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday added. Matching shifts were marked off for that date.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday removed.');
    }
}
