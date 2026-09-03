<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\School;
use App\Support\AppPagination;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdmin();
        $categories = School::CATEGORIES;

        $schools = School::with('city')
            ->withCount('students')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . trim((string) $request->search) . '%');
            })
            ->when($request->filled('category') && array_key_exists($request->category, $categories), function ($q) use ($request) {
                $q->where('category', $request->category);
            })
            ->latest()
            ->paginate(AppPagination::PER_PAGE)
            ->withQueryString();

        $cities = City::query()->orderBy('name')->get(['id', 'name']);

        return view('pickdrop.schools.index', compact('schools', 'cities', 'categories'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:'.implode(',', array_keys(School::CATEGORIES))],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        School::create($validated);

        return back()->with('success', 'Institution added.');
    }

    public function show(School $school)
    {
        $this->ensureAdmin();
        $school->load(['city', 'students.parent']);
        $categories = School::CATEGORIES;

        return view('pickdrop.schools.show', compact('school', 'categories'));
    }

    public function update(Request $request, School $school)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:'.implode(',', array_keys(School::CATEGORIES))],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $school->update($validated);

        return back()->with('success', 'Institution updated.');
    }

    public function destroy(School $school)
    {
        $this->ensureAdmin();
        $school->delete();

        return redirect()->route('schools.index')->with('success', 'Institution removed.');
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->isPanelAdmin(), 403, 'Only Admin can manage institutions.');
    }
}
