<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Models\School;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class StudentController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $gate = $this->denyStudentAccess($request);
            if ($gate) {
                return $gate;
            }

            $students = Student::query()
                ->where('parent_id', $request->user()->id)
                ->with(['city', 'pickupArea'])
                ->orderByDesc('id')
                ->paginate(\App\Support\AppPagination::PER_PAGE);

            return $this->successResponse($students, 'Students for this parent');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch students');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $gate = $this->denyStudentAccess($request);
            if ($gate) {
                return $gate;
            }

            $validated = $request->validate([
                'name'   => ['required', 'string', 'max:255'],
                'grade'  => ['nullable', 'string', 'max:100'],
                'school_id' => ['nullable', 'integer', 'exists:schools,id'],
                'school_name' => ['nullable', 'string', 'max:255'],
                'school_location' => ['nullable', 'string', 'max:255'],
                'emergency_name' => ['nullable', 'string', 'max:255'],
                'emergency_phone' => ['nullable', 'string', 'max:30'],
                'emergency_relation' => ['nullable', 'string', 'max:50'],
            ]);

            $validated = $this->applySelectedInstitution($validated);

            $student = Student::create(array_merge($validated, [
                'parent_id' => $request->user()->id,
                'status' => 'active',
            ]));
            $student->load(['city', 'pickupArea', 'school']);
            $user = $request->user()->fresh();

            return $this->successResponse([
                'student' => $student,
                'next_step' => $user->parentSelfNextStep(),
                'onboarding_complete' => $user->isParentSelfOnboardingComplete(),
                'children_count' => $user->students()->count(),
                'note' => 'Transportation (city, area, pickup/drop) is set when creating a pick-drop request — not on child profile.',
            ], 'Student created', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to create student');
        }
    }

    public function show(Request $request, Student $student): JsonResponse
    {
        try {
            $gate = $this->denyStudentAccess($request);
            if ($gate) {
                return $gate;
            }

            $student->load(['city', 'pickupArea']);

            return $this->successResponse($student, 'Student detail');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch student');
        }
    }

    public function update(Request $request, Student $student): JsonResponse
    {
        try {
            $gate = $this->denyStudentAccess($request);
            if ($gate) {
                return $gate;
            }

            $validated = $request->validate([
                'name'   => ['sometimes', 'string', 'max:255'],
                'grade'  => ['sometimes', 'nullable', 'string', 'max:100'],
                'school_id' => ['sometimes', 'nullable', 'integer', 'exists:schools,id'],
                'school_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'school_location' => ['sometimes', 'nullable', 'string', 'max:255'],
                'status' => ['sometimes', 'in:active,inactive'],
                'emergency_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'emergency_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
                'emergency_relation' => ['sometimes', 'nullable', 'string', 'max:50'],
            ]);

            $validated = $this->applySelectedInstitution($validated);

            $student->fill($validated);
            $student->save();
            $student->load(['city', 'pickupArea', 'school']);

            return $this->successResponse($student, 'Student updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update student');
        }
    }

    public function destroy(Request $request, Student $student): JsonResponse
    {
        try {
            $gate = $this->denyStudentAccess($request);
            if ($gate) {
                return $gate;
            }

            $student->delete();

            return $this->successResponse(null, 'Student deleted');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to delete student');
        }
    }

    private function denyStudentAccess(Request $request): ?JsonResponse
    {
        $user = $request->user();
        $accountDenied = $this->denyUnlessAccountType($user, $request);
        if ($accountDenied) {
            return $accountDenied;
        }

        if (!$user->isParentAccount()) {
            return $this->errorResponse('Only parent accounts can manage children.', 403);
        }

        return $this->denyUnlessKycApproved($user);
    }

    /**
     * Parents pick an admin-created institution. They cannot create a new one.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function applySelectedInstitution(array $validated): array
    {
        if (empty($validated['school_id'])) {
            return $validated;
        }

        $institution = School::query()->find($validated['school_id']);
        if (! $institution) {
            return $validated;
        }

        $validated['school_name'] = $institution->name;
        if (empty($validated['school_location'])) {
            $validated['school_location'] = $institution->address;
        }

        return $validated;
    }
}

