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

    /**
     * Shared validation for add and edit.
     *
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function validationRules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:'.implode(',', array_keys(School::CATEGORIES))],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'address' => ['nullable', 'string', 'max:255'],
            // digits, spaces, + - ( ) only, 7-15 digits in total (e.g. +92 300 1234567, 051-1234567)
            'phone' => ['nullable', 'string', 'max:50', 'regex:/^(?=(?:\D*\d){7,15}\D*$)\+?[0-9\s\-()]+$/'],
            // the standard `email` rule accepts "a@b"; also require a dot and extension in the domain
            'email' => ['nullable', 'email', 'max:255', 'regex:/^[^@\s]+@[^@\s]+\.[^@\s.]{2,}$/'],
            'status' => ['required', 'in:Active,Inactive'],
        ];

        $messages = [
            'name.required' => 'Name is required.',
            'name.max' => 'Name is too long (max 255).',
            'category.required' => 'Choose a category.',
            'category.in' => 'Choose a valid category.',
            'city_id.exists' => 'Choose a valid city.',
            'address.max' => 'Address is too long (max 255).',
            'phone.regex' => 'Enter a valid phone, e.g. +92 300 1234567.',
            'phone.max' => 'Phone is too long (max 50).',
            'email.email' => 'Enter a valid email, e.g. name@example.com.',
            'email.regex' => 'Enter a valid email, e.g. name@example.com.',
            'email.max' => 'Email is too long (max 255).',
            'status.in' => 'Choose Active or Inactive.',
        ];

        return [$rules, $messages];
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();
        [$rules, $messages] = $this->validationRules();
        // Named bag: the page has an Add and an Edit form, each shows only its own errors.
        $validated = $request->validateWithBag('addSchool', $rules, $messages);

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
        [$rules, $messages] = $this->validationRules();
        $validated = $request->validateWithBag('editSchool', $rules, $messages);

        $school->update($validated);

        return redirect()->route('schools.index')->with('success', 'Institution updated successfully.');
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
