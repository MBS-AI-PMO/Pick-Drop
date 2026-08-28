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
        $schools = School::with('city')
            ->withCount('students')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . trim((string) $request->search) . '%');
            })
            ->latest()
            ->paginate(AppPagination::PER_PAGE)
            ->withQueryString();

        $cities = City::query()->orderBy('name')->get(['id', 'name']);

        return view('pickdrop.schools.index', compact('schools', 'cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        School::create($validated);

        return back()->with('success', 'School added.');
    }

    public function show(School $school)
    {
        $school->load(['city', 'students.parent']);

        return view('pickdrop.schools.show', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $school->update($validated);

        return back()->with('success', 'School updated.');
    }

    public function destroy(School $school)
    {
        $school->delete();

        return redirect()->route('schools.index')->with('success', 'School removed.');
    }
}
