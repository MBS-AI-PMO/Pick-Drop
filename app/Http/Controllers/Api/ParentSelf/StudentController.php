<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Http\Controllers\Api\ParentSelf\BaseApiController;
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
            $students = Student::where('parent_id', $request->user()->id)
                ->orderByDesc('id')
                ->paginate(20);

            return $this->successResponse($students, 'Students');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch students');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'   => ['required', 'string', 'max:255'],
                'grade'  => ['nullable', 'string', 'max:100'],
                'school_name' => ['nullable', 'string', 'max:255'],
                'school_location' => ['nullable', 'string', 'max:255'],
                'city_id' => ['nullable', 'integer', 'exists:cities,id'],
                'pickup_area_id' => ['nullable', 'integer', 'exists:areas,id'],
                'pickup_location' => ['nullable', 'string', 'max:255'],
                'pickup_lat' => ['nullable', 'numeric', 'between:-90,90'],
                'pickup_lng' => ['nullable', 'numeric', 'between:-180,180'],
                'pickup_time' => ['nullable', 'date_format:H:i'],
                'dropoff_time' => ['nullable', 'date_format:H:i'],
            ]);

            $student = Student::create(array_merge($validated, [
                'parent_id' => $request->user()->id,
                'status' => 'active',
            ]));

            return $this->successResponse($student, 'Student created', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to create student');
        }
    }

    public function show(Request $request, Student $student): JsonResponse
    {
        try {
            if ($student->parent_id !== $request->user()->id) {
                return $this->errorResponse('Not found', 404);
            }

            return $this->successResponse($student, 'Student detail');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch student');
        }
    }

    public function update(Request $request, Student $student): JsonResponse
    {
        try {
            if ($student->parent_id !== $request->user()->id) {
                return $this->errorResponse('Not found', 404);
            }

            $validated = $request->validate([
                'name'   => ['sometimes', 'string', 'max:255'],
                'grade'  => ['sometimes', 'nullable', 'string', 'max:100'],
                'school_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'school_location' => ['sometimes', 'nullable', 'string', 'max:255'],
                'city_id' => ['sometimes', 'nullable', 'integer', 'exists:cities,id'],
                'pickup_area_id' => ['sometimes', 'nullable', 'integer', 'exists:areas,id'],
                'pickup_location' => ['sometimes', 'nullable', 'string', 'max:255'],
                'pickup_lat' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
                'pickup_lng' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
                'pickup_time' => ['sometimes', 'nullable', 'date_format:H:i'],
                'dropoff_time' => ['sometimes', 'nullable', 'date_format:H:i'],
                'status' => ['sometimes', 'in:active,inactive'],
            ]);

            $student->fill($validated);
            $student->save();

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
            if ($student->parent_id !== $request->user()->id) {
                return $this->errorResponse('Not found', 404);
            }

            $student->delete();

            return $this->successResponse(null, 'Student deleted');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to delete student');
        }
    }
}

